import { Component, Inject, signal } from '@angular/core';
import { FormControl, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { TipoEntradaService } from '../../../core/services/tipo-entrada.service';
import { Evento } from '../../../core/models/evento.model';
import { TipoEntrada } from '../../../core/models/tipo-entrada.model';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDividerModule } from '@angular/material/divider';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-dialogo-tipos-entrada',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatDividerModule
  ],
  templateUrl: './dialogo-tipos-entrada.html',
  styleUrl: './dialogo-tipos-entrada.scss',
})
export class DialogoTiposEntrada {
  // Cada entrada a la venta tiene su propio formulario y sus propios botones.
  // No hay un guardado global, para que quede claro que cada fila se aplica sola
  formularios = signal<{ tipo: TipoEntrada; form: FormGroup }[]>([]);
  nuevoForm: FormGroup;
  ocupado = signal(false);

  // Se enciende con la primera alta, cambio o baja. Al cerrar se lo comunica al
  // listado, para que solo se recargue si de verdad hay algo distinto
  huboCambios = signal(false);

  constructor(
    private tipoEntradaService: TipoEntradaService,
    private snackBar: MatSnackBar,
    @Inject(MAT_DIALOG_DATA) public datos: { evento: Evento }
  ) {
    this.nuevoForm = this.crearFormulario();
    this.cargar(datos.evento.tipo_entradas ?? []);
  }

  crearFormulario(tipo?: TipoEntrada): FormGroup {
    return new FormGroup({
      nombre: new FormControl(tipo?.nombre ?? '', [Validators.required, Validators.maxLength(100)]),
      precio: new FormControl<number | null>(tipo ? Number(tipo.precio) : null, [Validators.required, Validators.min(0)]),
      aforo: new FormControl<number | null>(tipo?.aforo ?? null, [Validators.required, Validators.min(1)]),
      oferta: new FormControl<number | null>(tipo?.oferta ?? null, [Validators.min(1), Validators.max(100)]),
    });
  }

  cargar(tipos: TipoEntrada[]) {
    this.formularios.set(tipos.map(tipo => ({ tipo, form: this.crearFormulario(tipo) })));
  }

  agregar() {
    if (this.nuevoForm.invalid) return;

    this.ocupado.set(true);

    this.tipoEntradaService.crearTipoEntrada(this.datos.evento.id_evento, this.nuevoForm.value).subscribe({
      next: (respuesta) => {
        this.formularios.set([
          ...this.formularios(),
          { tipo: respuesta.data, form: this.crearFormulario(respuesta.data) }
        ]);
        this.nuevoForm.reset();
        this.huboCambios.set(true);
        this.ocupado.set(false);
        this.aviso('Entrada añadida');
      },
      error: (err) => this.fallo(err)
    });
  }

  actualizar(fila: { tipo: TipoEntrada; form: FormGroup }) {
    if (fila.form.invalid) return;

    this.ocupado.set(true);

    this.tipoEntradaService.actualizarTipoEntrada(fila.tipo.id_tipo_entrada, fila.form.value).subscribe({
      next: (respuesta) => {
        fila.tipo = respuesta.data;
        this.huboCambios.set(true);
        this.ocupado.set(false);
        this.aviso('Entrada actualizada');
      },
      error: (err) => this.fallo(err)
    });
  }

  /**
   * El backend rechaza el borrado si ya se han vendido entradas de ese tipo
   * @param fila
   */
  eliminar(fila: { tipo: TipoEntrada; form: FormGroup }) {
    this.ocupado.set(true);

    this.tipoEntradaService.eliminarTipoEntrada(fila.tipo.id_tipo_entrada).subscribe({
      next: () => {
        this.formularios.set(
          this.formularios().filter(f => f.tipo.id_tipo_entrada !== fila.tipo.id_tipo_entrada)
        );
        this.huboCambios.set(true);
        this.ocupado.set(false);
        this.aviso('Entrada eliminada');
      },
      error: (err) => this.fallo(err)
    });
  }

  private aviso(mensaje: string) {
    this.snackBar.open(mensaje, 'Cerrar', { duration: 3000 });
  }

  private fallo(err: any) {
    this.ocupado.set(false);
    this.snackBar.open(err.error?.message ?? 'No se pudo completar la operación', 'Cerrar', { duration: 4000 });
  }
}
