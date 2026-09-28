import { Component, computed, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CurrencyPipe } from '@angular/common';
import { AuthService } from '../../../core/services/auth.service';
import { CarritoService } from '../../../core/services/carrito.service';
import { CategoriaService } from '../../../core/services/categoria.service';
import { Categoria } from '../../../core/models/categoria.model';
import { DetalleCarrito } from '../../../core/models/detalle-carrito.model';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatBadgeModule } from '@angular/material/badge';
import { MatDividerModule } from '@angular/material/divider';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [
    RouterLink,
    CurrencyPipe,
    MatToolbarModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatBadgeModule,
    MatDividerModule
  ],
  templateUrl: './navbar.html',
  styleUrl: './navbar.scss',
})
export class Navbar {
  // Categorías que se muestran en la barra. El servicio las guarda en caché, así
  // que solo se piden una vez por sesión aunque la barra viva en todas las pantallas
  categorias = signal<Categoria[]>([]);

  // Categoría que el catálogo tiene filtrada, para marcarla en la barra. Se lee de
  // la dirección, no de un estado propio, así sigue siendo correcta al recargar
  categoriaActiva = signal<number | null>(null);

  // Líneas del carrito para la previsualización. Llega vacío mientras no haya
  // carrito, así la plantilla no tiene que comprobar nulos
  lineas = computed<DetalleCarrito[]>(() => {
    const carrito = this.carritoService.carritoActual();

    if (!carrito || !carrito.detalle_carritos) {
      return [];
    }

    return carrito.detalle_carritos;
  });

  // Número de entradas para el globo del icono. Se recalcula solo cada vez que
  // el servicio actualiza la señal del carrito
  totalEntradas = computed(() => {
    let total = 0;

    for (const linea of this.lineas()) {
      total = total + linea.cantidad;
    }

    return total;
  });

  // Importe del carrito. El precio llega como cadena porque MySQL devuelve los
  // decimales como texto, por eso hay que convertirlo antes de multiplicar
  totalImporte = computed(() => {
    let total = 0;

    for (const linea of this.lineas()) {
      total = total + Number(linea.precio_unitario) * linea.cantidad;
    }

    return total;
  });

  constructor(
    public authService: AuthService,
    private carritoService: CarritoService,
    private categoriaService: CategoriaService,
    private router: Router,
    private route: ActivatedRoute
  ) { }

  ngOnInit() {
    this.categoriaService.getCategorias().subscribe({
      next: (respuesta) => this.categorias.set(respuesta.data),
      // Si fallan, la barra se queda sin los enlaces pero el resto sigue funcionando
      error: () => this.categorias.set([])
    });

    // La barra no se destruye al navegar, de modo que hay que estar pendiente de los
    // cambios de la dirección para saber qué categoría hay que resaltar
    this.route.queryParams.subscribe((parametros) => {
      if (parametros['categoria']) {
        this.categoriaActiva.set(Number(parametros['categoria']));
      } else {
        this.categoriaActiva.set(null);
      }
    });
  }

  /**
   * Importe de una línea, para mostrarlo en la previsualización
   * @param linea
   * @returns
   */
  subtotal(linea: DetalleCarrito): number {
    return Number(linea.precio_unitario) * linea.cantidad;
  }

  /**
   * Método que cierra y limpia la sesión. El carrito se limpia aquí porque
   * vive en otro servicio, y así no se enredan entre ellos
   */
  logout() {
    this.authService.logout().subscribe({
      next: () => this.salir(),
      error: () => {
        // Aunque la petición falle la sesión se cierra en el navegador, porque
        // el usuario ha pedido salir
        this.authService.limpiarSesion();
        this.salir();
      }
    });
  }

  /**
   * Limpia el carrito de memoria y devuelve al inicio de sesión
   */
  private salir(): void {
    this.carritoService.limpiarCarrito();
    this.router.navigate(['/login']);
  }
}
