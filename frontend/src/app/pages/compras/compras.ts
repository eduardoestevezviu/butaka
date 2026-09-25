import { Component, signal } from '@angular/core';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { CompraService } from '../../core/services/compra.service';
import { Compra } from '../../core/models/compra.model';
import { ESTADO_PAGO_COMPLETADO, ESTADO_PAGO_PENDIENTE } from '../../core/models/estado-pago.model';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

@Component({
  selector: 'app-compras',
  standalone: true,
  imports: [
    CurrencyPipe,
    DatePipe,
    MatExpansionModule,
    MatButtonModule,
    MatIconModule,
    MatChipsModule,
    MatDividerModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './compras.html',
  styleUrl: './compras.scss',
})
export class Compras {
  compras = signal<Compra[]>([]);
  cargando = signal(true);
  error = signal('');
  paginaActual = signal(1);
  totalPaginas = signal(1);

  // Detalle de cada compra ya desplegada, indexado por su identificador. Así al
  // volver a abrir un panel no se repite la petición
  detalles = signal<Record<string, Compra>>({});

  estadoPendiente = ESTADO_PAGO_PENDIENTE;
  estadoCompletado = ESTADO_PAGO_COMPLETADO;

  constructor(private compraService: CompraService) { }

  ngOnInit() {
    this.cargarCompras();
  }

  cargarCompras() {
    this.cargando.set(true);
    this.error.set('');

    this.compraService.getCompras(this.paginaActual()).subscribe({
      next: (respuesta) => {
        this.compras.set(respuesta.data.data);
        this.totalPaginas.set(respuesta.data.last_page);
        this.cargando.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message ?? 'No se pudieron cargar las compras');
        this.cargando.set(false);
      }
    });
  }

  /**
   * Trae el detalle de una compra al desplegar su panel. El listado es ligero y
   * no trae los puntos ni la factura, por eso hace falta esta segunda petición
   * @param compra
   */
  abrirDetalle(compra: Compra) {
    if (this.detalles()[compra.id_compra]) {
      return;
    }

    this.compraService.getCompra(compra.id_compra).subscribe({
      next: (respuesta) => {
        this.detalles.set({ ...this.detalles(), [compra.id_compra]: respuesta.data });
      },
      error: () => { }
    });
  }

  detalleDe(compra: Compra): Compra | null {
    return this.detalles()[compra.id_compra] ?? null;
  }

  /**
   * Resumen de una compra para la cabecera del panel, sin desplegarlo
   * @param compra
   * @returns
   */
  resumen(compra: Compra): string {
    if (!compra.detalle_compras || compra.detalle_compras.length === 0) {
      return '';
    }

    const primero = compra.detalle_compras[0].tipo_entrada?.evento?.nombre ?? '';
    const otros = compra.detalle_compras.length - 1;

    if (otros === 0) {
      return primero;
    }

    return `${primero} y ${otros} más`;
  }

  cambiarPagina(pagina: number) {
    this.paginaActual.set(pagina);
    this.cargarCompras();
  }
}
