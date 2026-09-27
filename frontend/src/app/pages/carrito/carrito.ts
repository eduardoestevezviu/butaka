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

// Las entradas del carrito que pertenecen a un mismo evento, que es lo que se
// paga de una vez: cada compra corresponde a un único evento
interface GrupoEvento {
  idEvento: string;
  nombre: string;
  fecha: string;
  lineas: DetalleCarrito[];
  subtotal: number;
}

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

  // Puntos que se quieren canjear en cada evento, indexados por su identificador
  puntosPorEvento = signal<Record<string, number>>({});

  // Evento cuya compra se está tramitando, para bloquear solo su botón
  procesando = signal<string | null>(null);

  minimoCanje = MIN_PUNTOS_CANJE;

  grupos = computed<GrupoEvento[]>(() => {
    const carrito = this.carritoService.carritoActual();

    if (!carrito || !carrito.detalle_carritos) {
      return [];
    }

    const grupos: GrupoEvento[] = [];

    for (const linea of carrito.detalle_carritos) {
      const evento = linea.tipo_entrada?.evento;

      if (!evento) {
        continue;
      }

      let grupo = grupos.find(g => g.idEvento === evento.id_evento);

      if (!grupo) {
        grupo = {
          idEvento: evento.id_evento,
          nombre: evento.nombre,
          fecha: evento.fecha,
          lineas: [],
          subtotal: 0
        };
        grupos.push(grupo);
      }

      grupo.lineas.push(linea);
      grupo.subtotal = grupo.subtotal + Number(linea.precio_unitario) * linea.cantidad;
    }

    return grupos;
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

  puntosDe(grupo: GrupoEvento): number {
    return this.puntosPorEvento()[grupo.idEvento] ?? 0;
  }

  descuento(grupo: GrupoEvento): number {
    return this.puntosDe(grupo) / PUNTOS_POR_EURO_CANJE;
  }

  total(grupo: GrupoEvento): number {
    return grupo.subtotal - this.descuento(grupo);
  }

  /**
   * Puntos que como mucho se pueden canjear en este evento: ni más de los que
   * se tienen, ni más de los que cubren la mitad de su importe
   * @param grupo
   * @returns
   */
  maximoCanjeable(grupo: GrupoEvento): number {
    const topePorImporte = Math.floor(
      grupo.subtotal * MAX_PORCENTAJE_CANJE / 100 * PUNTOS_POR_EURO_CANJE
    );

    return Math.min(this.puntosDisponibles(), topePorImporte);
  }

  cambiarPuntos(grupo: GrupoEvento, valor: string) {
    const puntos = Number(valor);
    let elegidos = 0;

    if (!isNaN(puntos) && puntos > 0) {
      elegidos = Math.min(puntos, this.maximoCanjeable(grupo));
    }

    this.puntosPorEvento.set({ ...this.puntosPorEvento(), [grupo.idEvento]: elegidos });
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

  /**
   * Compra las entradas de un evento y lleva a la pasarela de pago. Las de los
   * demás eventos siguen en el carrito para pagarse por separado
   * @param grupo
   */
  comprar(grupo: GrupoEvento) {
    this.procesando.set(grupo.idEvento);

    this.compraService.crearCompra({
      id_evento: grupo.idEvento,
      puntos_canjeados: this.puntosDe(grupo)
    }).subscribe({
      next: (respuesta) => {
        this.carritoService.cargarCarrito();
        this.irAPagar(respuesta.data.id_compra);
      },
      error: (err) => {
        this.procesando.set(null);
        this.snackBar.open(err.error?.message ?? 'No se pudo completar la compra', 'Cerrar', { duration: 5000 });
      }
    });
  }

  /**
   * Abre la pasarela para la compra recién creada. Si no se pudiera abrir, la
   * compra ya existe y queda pendiente, así que se envía a "Mis compras" para
   * que el asistente pueda pagarla desde allí
   * @param idCompra
   */
  private irAPagar(idCompra: string) {
    this.compraService.iniciarPago(idCompra).subscribe({
      next: (respuesta) => {
        window.location.href = respuesta.data.url;
      },
      error: () => {
        this.procesando.set(null);
        this.snackBar.open('Tu compra se ha registrado, pero no se pudo abrir el pago. Puedes pagarla desde Mis compras', 'Cerrar', { duration: 6000 });
        this.router.navigate(['/compras']);
      }
    });
  }
}
