import { Component, signal } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, ValidationErrors, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { CarritoService } from '../../../core/services/carrito.service';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-registro',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    RouterLink,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatCardModule,
    MatIconModule
  ],
  templateUrl: './registro.html',
  styleUrl: './registro.scss',
})
export class Registro {
  registroForm: FormGroup;
  error = signal('');
  cargando = signal(false);
  mostrarPassword = false;

  constructor(
    private authService: AuthService,
    private carritoService: CarritoService,
    private router: Router
  ) {
    // Los nombres de los campos son los mismos que espera la API, para no tener
    // que traducirlos antes de enviarlos
    this.registroForm = new FormGroup({
      nombre: new FormControl('', [Validators.required, Validators.maxLength(100), Validators.pattern(/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]*$/)]),
      apellidos: new FormControl('', [Validators.required, Validators.maxLength(100), Validators.pattern(/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]*$/)]),
      email: new FormControl('', [Validators.required, Validators.email, Validators.maxLength(100)]),
      password: new FormControl('', [Validators.required, Validators.minLength(8)]),
      passwordConfirmation: new FormControl('', [Validators.required]),
      numero_telefono: new FormControl('', [Validators.pattern(/^[0-9]{9}$/)]),
      calle: new FormControl('', [Validators.maxLength(255)]),
      vivienda: new FormControl('', [Validators.maxLength(50)]),
      ciudad: new FormControl('', [Validators.maxLength(100)]),
      codigo_postal: new FormControl('', [Validators.pattern(/^[0-9]{5}$/)]),
    }, { validators: this.comprobarConfirmacionPassword });
  }

  /**
   * Validador del formulario completo, no de un campo suelto, porque necesita
   * comparar dos campos entre sí. Al marcar el error se borran los demás del
   * campo de confirmación, así que su aviso de obligatorio nunca llega a verse:
   * la confirmación vacía ya se avisa como que no coinciden
   * @param control
   * @returns
   */
  comprobarConfirmacionPassword(control: AbstractControl): ValidationErrors | null {
    const password = control.get('password');
    const confirmacion = control.get('passwordConfirmation');

    if (password?.value !== confirmacion?.value) {
      confirmacion?.setErrors({ noCoinciden: true });
      return { noCoinciden: true };
    }

    confirmacion?.setErrors(null);
    return null;
  }

  onSubmit() {
    if (this.registroForm.invalid) return;

    this.cargando.set(true);
    this.error.set('');

    const valores = this.registroForm.value;

    // La confirmación de la contraseña no se envía, solo sirve para comprobar
    // aquí que el usuario ha escrito lo mismo dos veces
    const datos = {
      nombre: valores.nombre,
      apellidos: valores.apellidos,
      email: valores.email,
      password: valores.password,
      numero_telefono: valores.numero_telefono,
      calle: valores.calle,
      vivienda: valores.vivienda,
      ciudad: valores.ciudad,
      codigo_postal: valores.codigo_postal,
    };

    this.authService.register(datos).subscribe({
      next: () => {
        // El alta ya devuelve el token, así que se entra directamente sin pasar
        // por el formulario de acceso
        this.carritoService.cargarCarrito();
        this.router.navigate(['/']);
      },
      error: (err) => {
        this.cargando.set(false);
        this.error.set(err.error?.message ?? 'Error al registrar la cuenta');
      }
    });
  }
}
