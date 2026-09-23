import { Evento } from './evento.model';

// Respuesta de GET /organizador/facturacion
export interface Liquidacion {
  id_factura: string;
  numero_factura: string;
  fecha_emision: string;
  evento: Evento | null;
  importe_bruto: string;
  porcentaje_comision: number;
  base_imponible: string;
  iva: string;
  total_comision: string;
  neto: number;
}

export interface FacturacionTotales {
  importe_bruto: number;
  comision: number;
  neto: number;
}

export interface Facturacion {
  liquidaciones: Liquidacion[];
  totales: FacturacionTotales;
}
