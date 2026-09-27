import { Component, signal } from '@angular/core';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { CompraService } from '../../core/services/compra.service';
import { CarritoService } from '../../core/services/carrito.service';
import { Compra } from '../../core/models/compra.model';
import { ESTADO_PAGO_COMPLETADO, ESTADO_PAGO_PENDIENTE } from '../../core/models/estado-pago.model';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';

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
  // Compra cuyo pago se está abriendo, para bloquear solo su botón
  pagando = signal<string | null>(null);
  estadoCompletado = ESTADO_PAGO_COMPLETADO;

  constructor(
    private compraService: CompraService,
    private carritoService: CarritoService,
    private route: ActivatedRoute,
    private router: Router,
    private snackBar: MatSnackBar
  ) { }

  ngOnInit() {
    this.cargarCompras();
    this.comprobarVueltaDeStripe();
  }

  /**
   * Stripe devuelve al usuario a esta pantalla con parámetros en la dirección.
   * Si trae el identificador de la sesión, se le pide al backend que confirme
   * el pago; si el usuario canceló, solo se le avisa
   */
  comprobarVueltaDeStripe() {
    const parametros = this.route.snapshot.queryParamMap;

    if (parametros.get('pago') === 'cancelado') {
      this.snackBar.open('Has cancelado el pago. La compra sigue pendiente', 'Cerrar', { duration: 5000 });
      this.limpiarParametros();
      return;
    }

    const idCompra = parametros.get('id_compra');

    if (parametros.get('session_id') && idCompra) {
      this.verificarPago(idCompra);
    }
  }

  verificarPago(idCompra: string) {
    this.compraService.verificarPago(idCompra).subscribe({
      next: (respuesta) => {
        this.snackBar.open(respuesta.message, 'Cerrar', { duration: 5000 });
        // El detalle guardado es el de antes de pagar, así que se descarta
        this.detalles.set({});
        this.cargarCompras();
        // El carrito se leyó al arrancar la aplicación, antes de confirmar el
        // pago, así que puede haber quedado desfasado
        this.carritoService.cargarCarrito();
        this.limpiarParametros();
      },
      error: (err) => {
        this.snackBar.open(err.error?.message ?? 'No se pudo confirmar el pago', 'Cerrar', { duration: 5000 });
        this.limpiarParametros();
      }
    });
  }

  /**
   * Quita los parámetros de la dirección para que al recargar la página no se
   * vuelva a intentar confirmar el mismo pago
   */
  limpiarParametros() {
    this.router.navigate([], { queryParams: {} });
  }

  /**
   * Lleva al usuario a la pasarela de Stripe
   * @param compra
   */
  pagar(compra: Compra) {
    this.pagando.set(compra.id_compra);

    this.compraService.iniciarPago(compra.id_compra).subscribe({
      next: (respuesta) => {
        // Se sale de la aplicación, así que no hace falta desbloquear el botón
        window.location.href = respuesta.data.url;
      },
      error: (err) => {
        this.pagando.set(null);
        this.snackBar.open(err.error?.message ?? 'No se pudo abrir la pasarela de pago', 'Cerrar', { duration: 4000 });
      }
    });
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
   * Nombre del evento de la compra, para la cabecera del panel sin desplegar.
   * Una compra corresponde a un único evento, aunque incluya varios tipos de
   * entrada, así que basta con mirar la primera línea
   * @param compra
   * @returns
   */
  resumen(compra: Compra): string {
    if (!compra.detalle_compras || compra.detalle_compras.length === 0) {
      return '';
    }

    return compra.detalle_compras[0].tipo_entrada?.evento?.nombre ?? '';
  }

  cambiarPagina(pagina: number) {
    this.paginaActual.set(pagina);
    this.cargarCompras();
  }
}
