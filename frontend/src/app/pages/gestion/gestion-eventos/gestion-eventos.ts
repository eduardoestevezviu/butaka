import { Component, signal } from '@angular/core';
import { Router } from '@angular/router';
import { DatePipe } from '@angular/common';
import { EventoService } from '../../../core/services/evento.service';
import { Evento } from '../../../core/models/evento.model';
import { ESTADO_EVENTO_CANCELADO } from '../../../core/models/estado-evento.model';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { DialogoEvento } from '../../../shared/components/dialogo-evento/dialogo-evento';
import { DialogoTiposEntrada } from '../../../shared/components/dialogo-tipos-entrada/dialogo-tipos-entrada';
import { DialogoConfirmacion } from '../../../shared/components/dialogo-confirmacion/dialogo-confirmacion';

@Component({
  selector: 'app-gestion-eventos',
  standalone: true,
  imports: [
    DatePipe,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatChipsModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './gestion-eventos.html',
  styleUrl: './gestion-eventos.scss',
})
export class GestionEventos {
  eventos = signal<Evento[]>([]);
  cargando = signal(true);
  error = signal('');
  paginaActual = signal(1);
  totalPaginas = signal(1);

  estadoCancelado = ESTADO_EVENTO_CANCELADO;

  constructor(
    private eventoService: EventoService,
    private router: Router,
    private dialog: MatDialog,
    private snackBar: MatSnackBar
  ) { }

  ngOnInit() {
    this.cargarEventos();
  }

  cargarEventos() {
    this.cargando.set(true);
    this.error.set('');

    this.eventoService.getMisEventos(this.paginaActual()).subscribe({
      next: (respuesta) => {
        this.eventos.set(respuesta.data.data);
        this.totalPaginas.set(respuesta.data.last_page);
        this.cargando.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message ?? 'No se pudieron cargar tus eventos');
        this.cargando.set(false);
      }
    });
  }

  /**
   * Entradas vendidas y aforo total del evento, sumando sus modalidades. Sirve
   * para ver de un vistazo cómo va cada evento sin entrar en sus ventas
   * @param evento
   * @returns
   */
  ocupacion(evento: Evento): string {
    if (!evento.tipo_entradas || evento.tipo_entradas.length === 0) {
      return 'Sin entradas a la venta';
    }

    let aforo = 0;
    let vendidas = 0;

    for (const tipo of evento.tipo_entradas) {
      aforo = aforo + tipo.aforo;
      vendidas = vendidas + tipo.vendidas;
    }

    return `${vendidas} / ${aforo} entradas vendidas`;
  }

  /**
   * Abre la ventana de alta. Al cerrarse devuelve true si se creó el evento,
   * y entonces el listado se recarga
   */
  nuevoEvento() {
    const referencia = this.dialog.open(DialogoEvento, {
      width: '760px',
      data: {}
    });

    referencia.afterClosed().subscribe(creado => {
      if (creado) {
        this.cargarEventos();
      }
    });
  }

  editarEvento(evento: Evento) {
    const referencia = this.dialog.open(DialogoEvento, {
      width: '760px',
      data: { evento: evento }
    });

    referencia.afterClosed().subscribe(actualizado => {
      if (actualizado) {
        this.cargarEventos();
      }
    });
  }

  /**
   * Abre la ventana de entradas a la venta. Solo recarga el listado si en ella
   * se ha añadido, cambiado o quitado alguna entrada
   * @param evento
   */
  gestionarEntradas(evento: Evento) {
    const referencia = this.dialog.open(DialogoTiposEntrada, {
      width: '860px',
      data: { evento: evento }
    });

    referencia.afterClosed().subscribe(huboCambios => {
      if (huboCambios) {
        this.cargarEventos();
      }
    });
  }

  /**
   * Elimina el evento previa confirmación. Es un borrado lógico, y el backend
   * lo rechaza si el evento ya tiene entradas vendidas
   * @param evento
   */
  eliminarEvento(evento: Evento) {
    const referencia = this.dialog.open(DialogoConfirmacion, {
      data: {
        titulo: 'Eliminar evento',
        mensaje: `Se eliminará "${evento.nombre}" y dejará de estar a la venta. Esta acción no se puede deshacer.`,
        textoBoton: 'Eliminar'
      }
    });

    referencia.afterClosed().subscribe(confirmado => {
      if (!confirmado) {
        return;
      }

      this.eventoService.eliminarEvento(evento.id_evento).subscribe({
        next: () => {
          this.snackBar.open('Evento eliminado', 'Cerrar', { duration: 3000 });
          this.cargarEventos();
        },
        error: (err) => {
          this.snackBar.open(err.error?.message ?? 'No se pudo eliminar el evento', 'Cerrar', { duration: 4000 });
        }
      });
    });
  }

  verVentas(evento: Evento) {
    this.router.navigate(['/gestion/eventos', evento.id_evento, 'ventas']);
  }

  cambiarPagina(pagina: number) {
    this.paginaActual.set(pagina);
    this.cargarEventos();
  }
}
