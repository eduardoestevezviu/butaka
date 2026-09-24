import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';
import { Usuario, Sesion } from '../models/usuario.model';
import { Organizador } from '../models/organizador.model';

@Injectable({
  providedIn: 'root',
})
export class UsuarioService {
  private apiUrl = '/api/perfil';

  constructor(private http: HttpClient) { }

  /**
   * Perfil del usuario que ha iniciado sesión. No lleva id, sale del token
   * @returns
   */
  getPerfil(): Observable<ApiResponse<Usuario>> {
    return this.http.get<ApiResponse<Usuario>>(this.apiUrl);
  }

  actualizarPerfil(datos: any): Observable<ApiResponse<Usuario>> {
    return this.http.put<ApiResponse<Usuario>>(this.apiUrl, datos);
  }

  /**
   * Baja de la cuenta. Es un borrado lógico, el usuario la recupera
   * iniciando sesión de nuevo
   * @returns
   */
  eliminarCuenta(): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(this.apiUrl);
  }

  /**
   * Alta como organizador. Devuelve un token nuevo con el scope añadido,
   * porque los anteriores quedan revocados
   * @param datos
   * @returns
   */
  createOrganizador(datos: any): Observable<ApiResponse<Sesion>> {
    return this.http.post<ApiResponse<Sesion>>(`${this.apiUrl}/organizador`, datos);
  }

  actualizarOrganizador(datos: any): Observable<ApiResponse<Organizador>> {
    return this.http.put<ApiResponse<Organizador>>(`${this.apiUrl}/organizador`, datos);
  }
}
