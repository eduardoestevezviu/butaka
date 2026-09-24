import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse, Paginado } from '../models/api-response.model';
import { Factura } from '../models/factura.model';
import { Facturacion } from '../models/facturacion.model';

@Injectable({
  providedIn: 'root',
})
export class FacturaService {
  private apiUrl = '/api/facturas';
  private apiUrlOrganizador = '/api/organizador/facturacion';

  constructor(private http: HttpClient) { }

  /**
   * Facturas del usuario, tanto de compra como de liquidación
   * @param page
   * @returns
   */
  getFacturas(page?: number): Observable<ApiResponse<Paginado<Factura>>> {
    let params = new HttpParams();

    if (page) params = params.set('page', page);

    return this.http.get<ApiResponse<Paginado<Factura>>>(this.apiUrl, { params });
  }

  /**
   * Factura concreta con todos los datos que necesita el documento
   * @param id
   * @returns
   */
  getFactura(id: string): Observable<ApiResponse<Factura>> {
    return this.http.get<ApiResponse<Factura>>(`${this.apiUrl}/${id}`);
  }

  /**
   * Liquidaciones del organizador con sus totales. No se pagina, para que los
   * totales abarquen todas las liquidaciones y no solo las de la página
   * @param idEvento filtro opcional por evento
   * @returns
   */
  getFacturacion(idEvento?: string): Observable<ApiResponse<Facturacion>> {
    let params = new HttpParams();

    if (idEvento) params = params.set('evento', idEvento);

    return this.http.get<ApiResponse<Facturacion>>(this.apiUrlOrganizador, { params });
  }
}
