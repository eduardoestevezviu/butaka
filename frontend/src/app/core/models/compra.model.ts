import { DetalleCompra } from './detalle-compra.model';
import { EstadoPago } from './estado-pago.model';
import { Factura } from './factura.model';

export interface Compra {
  id_compra: string;
  id_usuario: string;
  id_estado_pago: number;
  importe_total: string;
  puntos_ganados: number;
  puntos_canjeados: number;
  fecha_pago: string | null;
  session_id: string | null;
  created_at: string;
  updated_at: string;

  estado_pago?: EstadoPago;
  detalle_compras?: DetalleCompra[];
  factura?: Factura | null;
}
