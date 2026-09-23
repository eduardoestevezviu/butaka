// Respuesta de GET /organizador/eventos/{id}/ventas
export interface VentaModalidad {
  id_tipo_entrada: string;
  nombre: string;
  aforo: number;
  vendidas: number;
  pendientes: number;
  disponibles: number;
  recaudacion: number;
}

export interface VentasTotales {
  aforo: number;
  vendidas: number;
  pendientes: number;
  disponibles: number;
  recaudacion: number;
}

export interface VentasEvento {
  id_evento: string;
  nombre: string;
  fecha: string;
  modalidades: VentaModalidad[];
  totales: VentasTotales;
}
