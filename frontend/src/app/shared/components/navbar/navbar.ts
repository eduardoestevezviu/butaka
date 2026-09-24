import { Component, computed } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { CurrencyPipe } from '@angular/common';
import { AuthService } from '../../../core/services/auth.service';
import { CarritoService } from '../../../core/services/carrito.service';
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
  // Líneas del carrito para la previsualización. Llega vacío mientras no haya
  // carrito, así la plantilla no tiene que comprobar nulos
  lineas = computed<DetalleCarrito[]>(() => {
    const carrito = this.carritoService.carritoActual();

    if (carrito === null || !carrito.detalle_carritos) {
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
    private router: Router
  ) { }

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
