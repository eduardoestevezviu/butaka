import { Component, signal } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { MAX_ENTRADAS_POR_EVENTO } from '../../core/butaka.config';
import { Evento as EventoModel } from '../../core/models/evento.model';
import { TipoEntrada } from '../../core/models/tipo-entrada.model';
import { EventoService } from '../../core/services/evento.service';
import { CarritoService } from '../../core/services/carrito.service';
import { AuthService } from '../../core/services/auth.service';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-evento',
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
    MatProgressSpinnerModule
  ],
  templateUrl: './evento.html',
  styleUrl: './evento.scss',
})
export class Evento {
  evento = signal<EventoModel | null>(null);
  cargando = signal(true);
  error = signal('');
  // Cantidad elegida para cada tipo de entrada, indexada por su identificador
  cantidades = signal<Record<string, number>>({});

  constructor(
    private eventoService: EventoService,
    private carritoService: CarritoService,
    public authService: AuthService,
    private route: ActivatedRoute,
    private router: Router,
    private snackBar: MatSnackBar
  ) { }

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('id');

    if (id) {
      this.cargarEvento(id);
    }
  }

  cargarEvento(id: string) {
    this.cargando.set(true);
    this.error.set('');

    this.eventoService.getEvento(id).subscribe({
      next: (respuesta) => {
        this.evento.set(respuesta.data);
        this.cargando.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message ?? 'No se pudo cargar el evento');
        this.cargando.set(false);
      }
    });
  }

  /**
   * Entradas que quedan por vender de una modalidad
   * @param tipo
   * @returns
   */
  disponibles(tipo: TipoEntrada): number {
    return tipo.aforo - tipo.vendidas;
  }

  /**
   * Precio final de una modalidad, con el descuento aplicado si lo tiene
   * @param tipo
   * @returns
   */
  precioFinal(tipo: TipoEntrada): number {
    const precio = Number(tipo.precio);

    if (tipo.oferta === null || tipo.oferta === 0) {
      return precio;
    }

    return precio * (1 - tipo.oferta / 100);
  }

  /**
   * Opciones del desplegable de cantidad. Nunca más de las que quedan a la venta
   * ni más del máximo por evento que impone el backend
   * @param tipo
   * @returns
   */
  opcionesCantidad(tipo: TipoEntrada): number[] {
    const maximo = Math.min(this.disponibles(tipo), MAX_ENTRADAS_POR_EVENTO);
    const opciones = [];

    for (let i = 1; i <= maximo; i++) {
      opciones.push(i);
    }

    return opciones;
  }

  cantidadDe(tipo: TipoEntrada): number {
    return this.cantidades()[tipo.id_tipo_entrada] ?? 1;
  }

  cambiarCantidad(tipo: TipoEntrada, cantidad: number) {
    this.cantidades.set({ ...this.cantidades(), [tipo.id_tipo_entrada]: cantidad });
  }

  /**
   * Añade la modalidad al carrito. Sin sesión iniciada no se puede comprar, así
   * que se envía al usuario al formulario de acceso
   * @param tipo
   */
  agregarAlCarrito(tipo: TipoEntrada) {
    if (!this.authService.isLoggedIn()) {
      this.router.navigate(['/login']);
      return;
    }

    this.carritoService.agregarEntrada({
      id_tipo_entrada: tipo.id_tipo_entrada,
      cantidad: this.cantidadDe(tipo)
    }).subscribe({
      next: () => {
        this.snackBar.open('Entradas añadidas al carrito', 'Cerrar', { duration: 3000 });
      },
      error: (err) => {
        this.snackBar.open(err.error?.message ?? 'No se pudo añadir al carrito', 'Cerrar', { duration: 4000 });
      }
    });
  }
}
