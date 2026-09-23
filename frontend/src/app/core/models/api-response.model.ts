// Todas las respuestas de la API van envueltas por ResultResponse
export interface ApiResponse<T> {
  statusCode: number;
  message: string;
  data: T;
}

// Forma estándar de la paginación de Laravel
export interface Paginado<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number | null;
  last_page: number;
  last_page_url: string;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
}
