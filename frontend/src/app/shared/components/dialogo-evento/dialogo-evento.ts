import { Component, Inject, signal } from '@angular/core';
import { FormArray, FormControl, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialogModule } from '@angular/material/dialog';
import { EventoService } from '../../../core/services/evento.service';
import { CategoriaService } from '../../../core/services/categoria.service';
import { Evento } from '../../../core/models/evento.model';
import { Categoria } from '../../../core/models/categoria.model';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDividerModule } from '@angular/material/divider';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-dialogo-evento',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatDividerModule
  ],
  templateUrl: './dialogo-evento.html',
  styleUrl: './dialogo-evento.scss',
})
export class DialogoEvento {
  eventoForm: FormGroup;
  categorias = signal<Categoria[]>([]);
  guardando = signal(false);

  // En el alta los tipos de entrada viajan dentro del evento, porque el backend no
  // admite un evento sin entradas a la venta. Una vez creado, se gestionan en
  // su propia ventana, para que no convivan dos formas de guardar
  edicion = false;

  constructor(
    private eventoService: EventoService,
    private categoriaService: CategoriaService,
    private dialogRef: MatDialogRef<DialogoEvento>,
    private snackBar: MatSnackBar,
    @Inject(MAT_DIALOG_DATA) public datos: { evento?: Evento }
  ) {
    this.edicion = !!datos?.evento;

    this.eventoForm = new FormGroup({
      nombre: new FormControl('', [Validators.required, Validators.maxLength(150)]),
      descripcion: new FormControl(''),
      ubicacion: new FormControl('', [Validators.required, Validators.maxLength(255)]),
      fecha: new FormControl('', [Validators.required]),
      imagen: new FormControl('', [Validators.maxLength(255)]),
      id_categoria: new FormControl<number | null>(null, [Validators.required]),
    });

    if (datos?.evento) {
      this.rellenarEvento(datos.evento);
    } else {
      // Solo en el alta se añade la lista de tipos de entrada al formulario, ya
      // que es la única petición que los lleva dentro. Nace con uno para rellenar
      this.eventoForm.addControl('tipo_entradas', new FormArray([]));
      this.tipoEntradas.push(this.crearGrupoTipoEntrada());
    }
  }

  ngOnInit() {
    this.categoriaService.getCategorias().subscribe({
      next: (respuesta) => this.categorias.set(respuesta.data),
      error: () => this.categorias.set([])
    });
  }

  /**
   * Acceso cómodo a la lista de tipos de entrada del formulario de alta
   */
  get tipoEntradas(): FormArray {
    return this.eventoForm.get('tipo_entradas') as FormArray;
  }

  crearGrupoTipoEntrada(): FormGroup {
    return new FormGroup({
      nombre: new FormControl('', [Validators.required, Validators.maxLength(100)]),
      precio: new FormControl<number | null>(null, [Validators.required, Validators.min(0)]),
      aforo: new FormControl<number | null>(null, [Validators.required, Validators.min(1)]),
      oferta: new FormControl<number | null>(null, [Validators.min(1), Validators.max(100)]),
    });
  }

  rellenarEvento(evento: Evento) {
    this.eventoForm.patchValue({
      nombre: evento.nombre,
      descripcion: evento.descripcion ?? '',
      ubicacion: evento.ubicacion,
      fecha: this.formatearFecha(evento.fecha),
      imagen: evento.imagen,
      id_categoria: evento.id_categoria,
    });
  }

  /**
   * El campo de fecha y hora del navegador necesita el formato aaaa-mm-ddThh:mm,
   * mientras que la API devuelve la fecha con un espacio y con segundos
   * @param fecha
   * @returns
   */
  formatearFecha(fecha: string): string {
    return fecha.replace(' ', 'T').substring(0, 16);
  }

  agregarTipoEntrada() {
    this.tipoEntradas.push(this.crearGrupoTipoEntrada());
  }

  quitarTipoEntrada(indice: number) {
    this.tipoEntradas.removeAt(indice);
  }

  guardar() {
    if (this.eventoForm.invalid) return;

    this.guardando.set(true);
    const valores = this.eventoForm.value;

    if (this.edicion) {
      this.eventoService.actualizarEvento(this.datos.evento!.id_evento, valores).subscribe({
        next: () => this.cerrar('Evento actualizado'),
        error: (err) => this.fallo(err)
      });
    } else {
      this.eventoService.crearEvento(valores).subscribe({
        next: () => this.cerrar('Evento creado'),
        error: (err) => this.fallo(err)
      });
    }
  }

  private cerrar(mensaje: string) {
    this.guardando.set(false);
    this.snackBar.open(mensaje, 'Cerrar', { duration: 3000 });
    // El true le indica al listado que debe recargarse
    this.dialogRef.close(true);
  }

  private fallo(err: any) {
    this.guardando.set(false);
    this.snackBar.open(err.error?.message ?? 'No se pudo guardar', 'Cerrar', { duration: 4000 });
  }
}
