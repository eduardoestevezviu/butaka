import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';
import { TipoEntrada } from '../models/tipo-entrada.model';

@Injectable({
  providedIn: 'root',
})
export class TipoEntradaService {
  private apiUrl = '/api/tipo-entradas';
  private apiUrlEventos = '/api/eventos';

  constructor(private http: HttpClient) { }

  /**
   * Crea un tipo de entrada dentro de un evento. El evento va en la ruta
   * porque un tipo de entrada no existe por sí solo
   * @param idEvento
   * @param datos
   * @returns
   */
  crearTipoEntrada(idEvento: string, datos: any): Observable<ApiResponse<TipoEntrada>> {
    return this.http.post<ApiResponse<TipoEntrada>>(`${this.apiUrlEventos}/${idEvento}/tipo-entradas`, datos);
  }

  actualizarTipoEntrada(id: string, datos: any): Observable<ApiResponse<TipoEntrada>> {
    return this.http.put<ApiResponse<TipoEntrada>>(`${this.apiUrl}/${id}`, datos);
  }

  eliminarTipoEntrada(id: string): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.apiUrl}/${id}`);
  }
}
