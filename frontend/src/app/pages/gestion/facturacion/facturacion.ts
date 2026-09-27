import { Component, signal } from '@angular/core';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { FacturaService } from '../../../core/services/factura.service';
import { Facturacion as FacturacionModel, Liquidacion } from '../../../core/models/facturacion.model';
import { MatCardModule } from '@angular/material/card';
import { MatTableModule } from '@angular/material/table';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-facturacion',
  standalone: true,
  imports: [
    CurrencyPipe,
    DatePipe,
    MatCardModule,
    MatTableModule,
    MatFormFieldModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './facturacion.html',
  styleUrl: './facturacion.scss',
})
export class Facturacion {
  facturacion = signal<FacturacionModel | null>(null);
  cargando = signal(true);
  error = signal('');

  // Eventos que se ofrecen en el filtro. Salen de la primera consulta sin
  // filtrar, así solo aparecen los que de verdad tienen liquidación
  eventosLiquidados = signal<{ id: string; nombre: string }[]>([]);
  eventoSeleccionado = signal<string | null>(null);

  columnas = ['numero_factura', 'fecha_emision', 'evento', 'importe_bruto', 'comision', 'neto', 'acciones'];

  constructor(
    private facturaService: FacturaService,
    private snackBar: MatSnackBar
  ) { }

  ngOnInit() {
    this.cargarFacturacion();
  }

  cargarFacturacion() {
    this.cargando.set(true);
    this.error.set('');

    this.facturaService.getFacturacion(this.eventoSeleccionado() ?? undefined).subscribe({
      next: (respuesta) => {
        this.facturacion.set(respuesta.data);

        // La lista del filtro solo se construye en la consulta sin filtrar,
        // porque una filtrada devuelve un único evento
        if (this.eventoSeleccionado() === null) {
          this.construirFiltro(respuesta.data.liquidaciones);
        }

        this.cargando.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message ?? 'No se pudo cargar la facturación');
        this.cargando.set(false);
      }
    });
  }

  construirFiltro(liquidaciones: Liquidacion[]) {
    const eventos = [];

    for (const liquidacion of liquidaciones) {
      if (liquidacion.evento) {
        eventos.push({ id: liquidacion.evento.id_evento, nombre: liquidacion.evento.nombre });
      }
    }

    this.eventosLiquidados.set(eventos);
  }

  /**
   * Descarga el PDF de la liquidación. Se trae el fichero y se provoca la
   * descarga con un enlace temporal, porque la ruta exige el token
   * @param idFactura
   * @param numero
   */
  descargar(idFactura: string, numero: string) {
    this.facturaService.descargarPdf(idFactura).subscribe({
      next: (fichero) => {
        const direccion = URL.createObjectURL(fichero);
        const enlace = document.createElement('a');

        enlace.href = direccion;
        enlace.download = `${numero}.pdf`;
        enlace.click();

        URL.revokeObjectURL(direccion);
      },
      error: () => {
        this.snackBar.open('No se pudo descargar la liquidación', 'Cerrar', { duration: 4000 });
      }
    });
  }

  filtrar(idEvento: string | null) {
    this.eventoSeleccionado.set(idEvento);
    this.cargarFacturacion();
  }
}
