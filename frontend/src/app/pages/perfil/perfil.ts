import { Component, signal } from '@angular/core';
import { Router } from '@angular/router';
import { AbstractControl, FormControl, FormGroup, ValidationErrors, Validators, ReactiveFormsModule } from '@angular/forms';
import { UsuarioService } from '../../core/services/usuario.service';
import { AuthService } from '../../core/services/auth.service';
import { CarritoService } from '../../core/services/carrito.service';
import { Usuario } from '../../core/models/usuario.model';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDividerModule } from '@angular/material/divider';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-perfil',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatDividerModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './perfil.html',
  styleUrl: './perfil.scss',
})
export class Perfil {
  usuario = signal<Usuario | null>(null);
  cargando = signal(true);
  guardando = signal(false);
  guardandoFiscales = signal(false);

  datosForm: FormGroup;
  organizadorForm: FormGroup;

  constructor(
    private usuarioService: UsuarioService,
    private authService: AuthService,
    private carritoService: CarritoService,
    private router: Router,
    private snackBar: MatSnackBar
  ) {
    this.datosForm = new FormGroup({
      nombre: new FormControl('', [Validators.required, Validators.maxLength(100)]),
      apellidos: new FormControl('', [Validators.required, Validators.maxLength(100)]),
      numero_telefono: new FormControl('', [Validators.pattern(/^[0-9]{9}$/)]),
      calle: new FormControl('', [Validators.maxLength(255)]),
      vivienda: new FormControl('', [Validators.maxLength(50)]),
      ciudad: new FormControl('', [Validators.maxLength(100)]),
      codigo_postal: new FormControl('', [Validators.pattern(/^[0-9]{5}$/)]),
      // Solo se envía si el usuario la rellena
      password: new FormControl('', [Validators.minLength(8)]),
      passwordConfirmation: new FormControl(''),
    }, { validators: this.comprobarConfirmacionPassword });

    this.organizadorForm = new FormGroup({
      nombre_comercial: new FormControl('', [Validators.required, Validators.maxLength(150)]),
      nif: new FormControl('', [Validators.required, Validators.minLength(9), Validators.maxLength(9)]),
      iban: new FormControl('', [Validators.required, Validators.maxLength(34)]),
    });
  }

  /**
   * Comprueba que la contraseña nueva se haya escrito igual dos veces. Solo
   * actúa si el usuario está cambiándola, porque el campo puede quedarse vacío.
   * Es importante porque Butaka no tiene recuperación de contraseña: un error
   * al teclearla dejaría la cuenta inaccesible
   * @param control
   * @returns
   */
  comprobarConfirmacionPassword(control: AbstractControl): ValidationErrors | null {
    const password = control.get('password');
    const confirmacion = control.get('passwordConfirmation');

    if (!password?.value) {
      confirmacion?.setErrors(null);
      return null;
    }

    if (password.value !== confirmacion?.value) {
      confirmacion?.setErrors({ noCoinciden: true });
      return { noCoinciden: true };
    }

    confirmacion?.setErrors(null);
    return null;
  }

  ngOnInit() {
    this.cargarPerfil();
  }

  cargarPerfil() {
    this.cargando.set(true);

    this.usuarioService.getPerfil().subscribe({
      next: (respuesta) => {
        this.usuario.set(respuesta.data);
        this.rellenarFormularios(respuesta.data);
        this.cargando.set(false);
      },
      error: () => {
        this.snackBar.open('No se pudo cargar el perfil', 'Cerrar', { duration: 4000 });
        this.cargando.set(false);
      }
    });
  }

  /**
   * Vuelca los datos recibidos en los dos formularios. La contraseña se deja
   * siempre vacía, porque nunca viaja de vuelta desde el servidor
   * @param usuario
   */
  rellenarFormularios(usuario: Usuario) {
    this.datosForm.patchValue({
      nombre: usuario.nombre,
      apellidos: usuario.apellidos,
      numero_telefono: usuario.numero_telefono ?? '',
      calle: usuario.calle ?? '',
      vivienda: usuario.vivienda ?? '',
      ciudad: usuario.ciudad ?? '',
      codigo_postal: usuario.codigo_postal ?? '',
      password: '',
      passwordConfirmation: '',
    });

    if (usuario.organizador) {
      this.organizadorForm.patchValue({
        nombre_comercial: usuario.organizador.nombre_comercial,
        nif: usuario.organizador.nif,
        iban: usuario.organizador.iban,
      });
    }
  }

  esOrganizador(): boolean {
    return !!this.usuario()?.organizador;
  }

  puntos(): number {
    return this.usuario()?.asistente?.puntos ?? 0;
  }

  guardarDatos() {
    if (this.datosForm.invalid) return;

    this.guardando.set(true);
    const valores = { ...this.datosForm.value };

    // La confirmación solo sirve para comprobar aquí que coinciden
    delete valores.passwordConfirmation;

    // Si no se ha escrito contraseña nueva, no se manda el campo
    if (!valores.password) {
      delete valores.password;
    }

    this.usuarioService.actualizarPerfil(valores).subscribe({
      next: (respuesta) => {
        this.usuario.set(respuesta.data);
        this.authService.actualizarUsuario(respuesta.data);
        this.datosForm.patchValue({ password: '', passwordConfirmation: '' });
        this.guardando.set(false);
        this.snackBar.open('Datos actualizados', 'Cerrar', { duration: 3000 });
      },
      error: (err) => {
        this.guardando.set(false);
        this.snackBar.open(err.error?.message ?? 'No se pudieron guardar los datos', 'Cerrar', { duration: 4000 });
      }
    });
  }

  /**
   * Alta o actualización de los datos fiscales. El alta devuelve un token nuevo,
   * porque los anteriores quedan revocados al cambiar los roles del usuario
   */
  guardarFiscales() {
    if (this.organizadorForm.invalid) return;

    this.guardandoFiscales.set(true);

    if (this.esOrganizador()) {
      this.usuarioService.actualizarOrganizador(this.organizadorForm.value).subscribe({
        next: () => {
          this.guardandoFiscales.set(false);
          this.snackBar.open('Datos fiscales actualizados', 'Cerrar', { duration: 3000 });
          this.cargarPerfil();
        },
        error: (err) => this.errorFiscales(err)
      });
    } else {
      this.usuarioService.createOrganizador(this.organizadorForm.value).subscribe({
        next: (respuesta) => {
          // Sin esto el usuario se quedaría con un token revocado y la siguiente
          // petición le echaría de la sesión
          this.authService.guardarSesion(respuesta.data);
          this.guardandoFiscales.set(false);
          this.snackBar.open('Ya puedes publicar eventos', 'Cerrar', { duration: 4000 });
          this.cargarPerfil();
        },
        error: (err) => this.errorFiscales(err)
      });
    }
  }

  private errorFiscales(err: any) {
    this.guardandoFiscales.set(false);
    this.snackBar.open(err.error?.message ?? 'No se pudieron guardar los datos fiscales', 'Cerrar', { duration: 4000 });
  }

  /**
   * Baja de la cuenta. Es reversible: el usuario la recupera iniciando sesión
   */
  darDeBaja() {
    this.usuarioService.eliminarCuenta().subscribe({
      next: () => {
        this.authService.limpiarSesion();
        this.carritoService.limpiarCarrito();
        this.router.navigate(['/']);
      },
      error: (err) => {
        this.snackBar.open(err.error?.message ?? 'No se pudo dar de baja la cuenta', 'Cerrar', { duration: 4000 });
      }
    });
  }
}
