import { DetalleCarrito } from './detalle-carrito.model';

export interface Carrito {
  id_carrito: string;
  id_usuario: string;
  id_estado_carrito: number;
  created_at: string;
  updated_at: string;

  detalle_carritos?: DetalleCarrito[];
}
