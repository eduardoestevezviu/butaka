import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse, Paginado } from '../models/api-response.model';
import { Compra } from '../models/compra.model';

@Injectable({
  providedIn: 'root',
})
export class CompraService {
  private apiUrl = '/api/compras';

  constructor(private http: HttpClient) { }

  /**
   * Listado de compras del usuario. Es ligero, sin el detalle de cada una
   * @param page
   * @returns
   */
  getCompras(page?: number): Observable<ApiResponse<Paginado<Compra>>> {
    let params = new HttpParams();

    if (page) params = params.set('page', page);

    return this.http.get<ApiResponse<Paginado<Compra>>>(this.apiUrl, { params });
  }

  /**
   * Detalle de una compra, que se pide al desplegar su panel
   * @param id
   * @returns
   */
  getCompra(id: string): Observable<ApiResponse<Compra>> {
    return this.http.get<ApiResponse<Compra>>(`${this.apiUrl}/${id}`);
  }

  /**
   * Convierte el carrito activo en una compra. El importe lo recalcula el
   * backend con los precios vigentes, aquí solo viajan los puntos a canjear
   * @param datos
   * @returns
   */
  crearCompra(datos: any): Observable<ApiResponse<Compra>> {
    return this.http.post<ApiResponse<Compra>>(this.apiUrl, datos);
  }
}
