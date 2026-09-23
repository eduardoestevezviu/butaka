// Respuesta de GET /organizador/posicionamiento. Solo la posición propia
export interface Posicionamiento {
  posicion: number | null;
  total_organizadores: number;
  ocupacion_media?: number;
  eventos_computados?: number;
  entradas_vendidas?: number;
}
