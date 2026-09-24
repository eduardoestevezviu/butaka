import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiResponse } from '../models/api-response.model';
import { Posicionamiento } from '../models/posicionamiento.model';

@Injectable({
  providedIn: 'root',
})
export class RankingService {
  private apiUrl = '/api/organizador/posicionamiento';

  constructor(private http: HttpClient) { }

  /**
   * Posición del organizador en el ranking. Solo devuelve su propia fila,
   * nunca la clasificación completa
   * @returns
   */
  getPosicionamiento(): Observable<ApiResponse<Posicionamiento>> {
    return this.http.get<ApiResponse<Posicionamiento>>(this.apiUrl);
  }
}
