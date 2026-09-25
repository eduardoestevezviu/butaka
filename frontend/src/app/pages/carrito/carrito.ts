import { Component, computed, signal } from '@angular/core';
import { Router } from '@angular/router';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { CarritoService } from '../../core/services/carrito.service';
import { CompraService } from '../../core/services/compra.service';
import { UsuarioService } from '../../core/services/usuario.service';
import { DetalleCarrito } from '../../core/models/detalle-carrito.model';
import {
  MAX_ENTRADAS_POR_EVENTO,
  MIN_PUNTOS_CANJE,
  MAX_PORCENTAJE_CANJE,
  PUNTOS_POR_EURO_CANJE
} from '../../core/butaka.config';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-carrito',
  standalone: true,
  imports: [
    CurrencyPipe,
    DatePipe,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatDividerModule,
    MatFormFieldModule,
    MatSelectModule,
    MatInputModule
  ],
  templateUrl: './carrito.html',
  styleUrl: './carrito.scss',
})
export class Carrito {
  // Puntos disponibles del asistente. Se piden al perfil y no se leen del
  // navegador, porque el saldo cambia con cada compra
  puntosDisponibles = signal(0);
  puntosACanjear = signal(0);
  procesando = signal(false);
  minimoCanje = MIN_PUNTOS_CANJE;

  // Las líneas salen de la señal del servicio, así que la pantalla se actualiza
  // sola cuando se cambia una cantidad o se quita una entrada
  lineas = computed<DetalleCarrito[]>(() => {
    const carrito = this.carritoService.carritoActual();

    if (!carrito || !carrito.detalle_carritos) {
      return [];
    }

    return carrito.detalle_carritos;
  });

  subtotal = computed(() => {
    let total = 0;

    for (const linea of this.lineas()) {
      total = total + Number(linea.precio_unitario) * linea.cantidad;
    }

    return total;
  });

  // Descuento que suponen los puntos elegidos
  descuento = computed(() => {
    return this.puntosACanjear() / PUNTOS_POR_EURO_CANJE;
  });

  total = computed(() => {
    return this.subtotal() - this.descuento();
  });

  // Puntos que como mucho se pueden canjear en esta compra: ni más de los que
  // se tienen, ni más de los que cubren la mitad del importe
  maximoCanjeable = computed(() => {
    const topePorImporte = Math.floor(
      this.subtotal() * MAX_PORCENTAJE_CANJE / 100 * PUNTOS_POR_EURO_CANJE
    );

    return Math.min(this.puntosDisponibles(), topePorImporte);
  });

  constructor(
    private carritoService: CarritoService,
    private compraService: CompraService,
    private usuarioService: UsuarioService,
    private router: Router,
    private snackBar: MatSnackBar
  ) { }

  ngOnInit() {
    this.carritoService.cargarCarrito();
    this.cargarPuntos();
  }

  cargarPuntos() {
    this.usuarioService.getPerfil().subscribe({
      next: (respuesta) => this.puntosDisponibles.set(respuesta.data?.asistente?.puntos ?? 0),
      error: () => this.puntosDisponibles.set(0)
    });
  }

  /**
   * Opciones del selector de cantidad: ni más de las que quedan a la venta ni
   * más del máximo por evento
   * @param linea
   * @returns
   */
  opcionesCantidad(linea: DetalleCarrito): number[] {
    const tipo = linea.tipo_entrada;
    let maximo = MAX_ENTRADAS_POR_EVENTO;

    if (tipo) {
      maximo = Math.min(tipo.aforo - tipo.vendidas, MAX_ENTRADAS_POR_EVENTO);
    }

    const opciones = [];

    for (let i = 1; i <= maximo; i++) {
      opciones.push(i);
    }

    return opciones;
  }

  subtotalLinea(linea: DetalleCarrito): number {
    return Number(linea.precio_unitario) * linea.cantidad;
  }

  cambiarCantidad(linea: DetalleCarrito, cantidad: number) {
    this.carritoService.agregarEntrada({
      id_tipo_entrada: linea.id_tipo_entrada,
      cantidad: cantidad
    }).subscribe({
      error: (err) => {
        this.snackBar.open(err.error?.message ?? 'No se pudo cambiar la cantidad', 'Cerrar', { duration: 4000 });
      }
    });
  }

  eliminar(linea: DetalleCarrito) {
    this.carritoService.eliminarEntrada(linea.id_tipo_entrada).subscribe({
      error: (err) => {
        this.snackBar.open(err.error?.message ?? 'No se pudo quitar la entrada', 'Cerrar', { duration: 4000 });
      }
    });
  }

  cambiarPuntos(valor: string) {
    const puntos = Number(valor);

    if (isNaN(puntos) || puntos < 0) {
      this.puntosACanjear.set(0);
      return;
    }

    this.puntosACanjear.set(Math.min(puntos, this.maximoCanjeable()));
  }

  /**
   * Convierte el carrito en una compra. El importe lo recalcula el backend con
   * los precios vigentes, aquí solo viajan los puntos a canjear
   */
  comprar() {
    this.procesando.set(true);

    this.compraService.crearCompra({
      puntos_canjeados: this.puntosACanjear()
    }).subscribe({
      next: () => {
        this.carritoService.cargarCarrito();
        this.router.navigate(['/compras']);
      },
      error: (err) => {
        this.procesando.set(false);
        this.snackBar.open(err.error?.message ?? 'No se pudo completar la compra', 'Cerrar', { duration: 5000 });
      }
    });
  }
}
