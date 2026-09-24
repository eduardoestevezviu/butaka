import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse, Paginado } from '../models/api-response.model';
import { Evento } from '../models/evento.model';
import { VentasEvento } from '../models/ventas.model';

@Injectable({
  providedIn: 'root',
})
export class EventoService {
  private apiUrl = '/api/eventos';
  private apiUrlOrganizador = '/api/organizador/eventos';

  constructor(private http: HttpClient) { }

  /**
   * Catálogo público. Solo devuelve eventos publicados y no celebrados
   * @param filtros categoria, buscar y page
   * @returns
   */
  getEventos(filtros?: any): Observable<ApiResponse<Paginado<Evento>>> {
    let params = new HttpParams();

    if (filtros?.categoria) params = params.set('categoria', filtros.categoria);
    if (filtros?.buscar) params = params.set('buscar', filtros.buscar);
    if (filtros?.page) params = params.set('page', filtros.page);

    return this.http.get<ApiResponse<Paginado<Evento>>>(this.apiUrl, { params });
  }

  /**
   * Eventos de los organizadores mejor posicionados, para la banda de
   * destacados del catálogo. No se pagina, son cinco como mucho
   * @returns
   */
  getDestacados(): Observable<ApiResponse<Evento[]>> {
    return this.http.get<ApiResponse<Evento[]>>(`${this.apiUrl}/destacados`);
  }

  /**
   * Ficha pública de un evento con sus tipos de entrada
   * @param id
   * @returns
   */
  getEvento(id: string): Observable<ApiResponse<Evento>> {
    return this.http.get<ApiResponse<Evento>>(`${this.apiUrl}/${id}`);
  }

  /**
   * Eventos del organizador que ha iniciado sesión, en cualquier estado
   * @param page
   * @returns
   */
  getMisEventos(page?: number): Observable<ApiResponse<Paginado<Evento>>> {
    let params = new HttpParams();

    if (page) params = params.set('page', page);

    return this.http.get<ApiResponse<Paginado<Evento>>>(this.apiUrlOrganizador, { params });
  }

  /**
   * Ventas del evento desglosadas por tipo de entrada
   * @param id
   * @returns
   */
  getVentas(id: string): Observable<ApiResponse<VentasEvento>> {
    return this.http.get<ApiResponse<VentasEvento>>(`${this.apiUrlOrganizador}/${id}/ventas`);
  }

  crearEvento(datos: any): Observable<ApiResponse<Evento>> {
    return this.http.post<ApiResponse<Evento>>(this.apiUrl, datos);
  }

  actualizarEvento(id: string, datos: any): Observable<ApiResponse<Evento>> {
    return this.http.put<ApiResponse<Evento>>(`${this.apiUrl}/${id}`, datos);
  }

  eliminarEvento(id: string): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.apiUrl}/${id}`);
  }
}
