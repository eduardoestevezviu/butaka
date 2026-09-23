import { TipoEntrada } from './tipo-entrada.model';
import { Evento } from './evento.model';

export interface DetalleCompra {
  id_compra: string;
  id_tipo_entrada: string;
  cantidad: number;
  precio_unitario: string;

  tipo_entrada?: TipoEntrada & { evento?: Evento };
}
