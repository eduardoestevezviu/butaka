import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { tap } from 'rxjs/operators';
import { ApiResponse } from '../models/api-response.model';
import { Categoria } from '../models/categoria.model';

@Injectable({
  providedIn: 'root',
})
export class CategoriaService {
  private apiUrl = '/api/categorias';
  private cache: ApiResponse<Categoria[]> | null = null;

  constructor(private http: HttpClient) { }

  /**
   * Devuelve las categorías para el filtro del catálogo
   * @returns
   */
  getCategorias(): Observable<ApiResponse<Categoria[]>> {
    // Se almacena la lista de categorías en caché para evitar peticiones redundantes
    // Se destruye cuando se recarga la página o se cierra la pestaña
    if (this.cache !== null) {
      return of(this.cache);
    }

    return this.http.get<ApiResponse<Categoria[]>>(this.apiUrl).pipe(
      tap(respuesta => this.cache = respuesta)
    );
  }
}
