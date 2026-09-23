import { TipoFactura } from './tipo-factura.model';
import { Evento } from './evento.model';

export interface Factura {
  id_factura: string;
  numero_factura: string;
  id_tipo_factura: number;
  id_usuario: string;
  fecha_emision: string;
  base_imponible: string;
  iva: string;
  total: string;

  // Solo en las facturas de compra
  id_compra: string | null;

  // Solo en las de liquidación
  id_evento: string | null;
  importe_bruto: string | null;
  comision: number | null;

  tipo_factura?: TipoFactura;
  evento?: Evento | null;
}
