// Los decimales llegan como cadena, porque MySQL devuelve decimal como texto
export interface TipoEntrada {
  id_tipo_entrada: string;
  nombre: string;
  precio: string;
  oferta: number | null;
  aforo: number;
  vendidas: number;
  id_evento: string;
}
