import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';
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
   * Factura concreta con todos los datos que necesita el documento
   * @param id
   * @returns
   */
  getFactura(id: string): Observable<ApiResponse<Factura>> {
    return this.http.get<ApiResponse<Factura>>(`${this.apiUrl}/${id}`);
  }

  /**
   * Descarga el PDF de la factura. Se pide como binario porque la respuesta no
   * es JSON, y con HttpClient en lugar de un enlace normal para que el
   * interceptor pueda añadirle el token: un <a href> no lleva cabeceras
   * @param id
   * @returns
   */
  descargarPdf(id: string): Observable<Blob> {
    return this.http.get(`${this.apiUrl}/${id}/pdf`, { responseType: 'blob' });
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
