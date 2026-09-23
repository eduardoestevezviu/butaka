import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';
import { Usuario, Sesion } from '../models/usuario.model';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = '/api';

  // Señales reactivas. Se inicializan leyendo el navegador para que al recargar
  // la página la barra de navegación siga sabiendo quién había iniciado sesión
  isLoggedIn = signal(!!localStorage.getItem('token'));
  esOrganizador = signal(this.leerEsOrganizador());

  constructor(private http: HttpClient) { }

  /**
   * Método para realizar el registro de un usuario
   * @param datos
   * @returns datos del usuario y el token
   */
  register(datos: any): Observable<ApiResponse<Sesion>> {
    return this.http.post<ApiResponse<Sesion>>(`${this.apiUrl}/register`, datos).pipe(
      tap((respuesta: ApiResponse<Sesion>) => {
        this.guardarSesion(respuesta.data);
      })
    );
  }

  /**
   * Método para iniciar sesión
   * @param email
   * @param password
   * @returns datos del usuario y el token
   */
  login(email: string, password: string): Observable<ApiResponse<Sesion>> {
    return this.http.post<ApiResponse<Sesion>>(`${this.apiUrl}/login`, { email, password }).pipe(
      // Procesa la respuesta de la llamada
      tap((respuesta: ApiResponse<Sesion>) => {
        this.guardarSesion(respuesta.data);
      })
    );
  }

  /**
   * Método para cerrar sesión. La sesión se limpia siempre, incluso si la
   * petición falla, porque el usuario ha pedido salir
   * @returns
   */
  logout(): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => this.limpiarSesion())
    );
  }

  /**
   * Guarda en el navegador los datos de la sesión y actualiza las señales.
   * Lo usan el registro, el inicio de sesión y el alta como organizador
   * @param sesion
   */
  guardarSesion(sesion: Sesion): void {
    localStorage.setItem('token', sesion.token);
    localStorage.setItem('usuario', JSON.stringify(sesion.usuario));

    this.isLoggedIn.set(true);
    this.esOrganizador.set(!!sesion.usuario.organizador);
  }

  /**
   * Limpia las variables almacenadas en el navegador
   */
  limpiarSesion(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('usuario');

    this.isLoggedIn.set(false);
    this.esOrganizador.set(false);
  }

  /**
   * Actualiza el usuario si se modifica su perfil
   * @param usuario
   */
  actualizarUsuario(usuario: Usuario): void {
    localStorage.setItem('usuario', JSON.stringify(usuario));
    this.esOrganizador.set(!!usuario.organizador);
  }

  /**
   * Comprueba si el usuario guardado tiene ficha de organizador. Se consulta
   * al arrancar, cuando las señales aún no existen
   * @returns
   */
  private leerEsOrganizador(): boolean {
    const usuario = this.getUsuario();

    if (usuario === null) {
      return false;
    }

    return !!usuario.organizador;
  }

  /**
   * Getters y setters
   */
  getToken(): string | null {
    return localStorage.getItem('token');
  }

  getUsuario(): Usuario | null {
    const usuario = localStorage.getItem('usuario');
    return usuario ? JSON.parse(usuario) : null;
  }
}
