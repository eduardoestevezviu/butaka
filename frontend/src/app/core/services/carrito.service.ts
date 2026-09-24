import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';
import { Carrito } from '../models/carrito.model';

@Injectable({
  providedIn: 'root',
})
export class CarritoService {
  private apiUrl = '/api/carrito';

  // Carrito vigente del usuario. Es lo que lee el contador del navbar, por eso
  // vive aquí y no en la pantalla del carrito. Vale null cuando no hay ninguno
  carritoActual = signal<Carrito | null>(null);

  constructor(private http: HttpClient) {
    // Al arrancar se recupera el carrito si el usuario ya tenía sesión iniciada
    if (localStorage.getItem('token')) {
      this.cargarCarrito();
    }
  }

  /**
   * Carrito activo del usuario con sus líneas, o null si no tiene ninguno
   * @returns
   */
  getCarrito(): Observable<ApiResponse<Carrito | null>> {
    return this.http.get<ApiResponse<Carrito | null>>(this.apiUrl);
  }

  /**
   * Fija la cantidad de un tipo de entrada en el carrito. No suma a lo que
   * hubiera, reemplaza, así que el mismo método sirve para añadir y para
   * cambiar la cantidad desde la pantalla del carrito
   * @param datos id_tipo_entrada y cantidad
   * @returns
   */
  agregarEntrada(datos: any): Observable<ApiResponse<Carrito>> {
    return this.http.post<ApiResponse<Carrito>>(this.apiUrl, datos).pipe(
      // La respuesta ya trae el carrito recargado, así que no hace falta
      // volver a pedirlo
      tap(respuesta => this.carritoActual.set(respuesta.data))
    );
  }

  /**
   * Quita del carrito todas las entradas de ese tipo
   * @param idTipoEntrada
   * @returns
   */
  eliminarEntrada(idTipoEntrada: string): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.apiUrl}/${idTipoEntrada}`).pipe(
      // El borrado no devuelve el carrito, y además puede haberlo eliminado
      // entero si era la última línea, así que se vuelve a consultar
      tap(() => this.cargarCarrito())
    );
  }

  /**
   * Refresca la señal. Se llama al arrancar, al iniciar sesión y después
   * de las operaciones que no devuelven el carrito
   */
  cargarCarrito(): void {
    this.getCarrito().subscribe({
      next: (respuesta) => this.carritoActual.set(respuesta.data),
      error: () => this.carritoActual.set(null)
    });
  }

  /**
   * Limpia el carrito de memoria al cerrar sesión, para que el contador no
   * siga mostrando las entradas del usuario anterior
   */
  limpiarCarrito(): void {
    this.carritoActual.set(null);
  }
}
