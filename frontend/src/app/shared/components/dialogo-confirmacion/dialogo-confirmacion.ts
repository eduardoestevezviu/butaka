import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';

/**
 * Ventana genérica de confirmación para las acciones que no se pueden deshacer.
 * Devuelve true si el usuario confirma y nada si cancela
 */
@Component({
  selector: 'app-dialogo-confirmacion',
  standalone: true,
  imports: [MatDialogModule, MatButtonModule],
  templateUrl: './dialogo-confirmacion.html',
  styleUrl: './dialogo-confirmacion.scss',
})
export class DialogoConfirmacion {
  constructor(
    @Inject(MAT_DIALOG_DATA) public datos: { titulo: string; mensaje: string; textoBoton: string }
  ) { }
}
