export interface EstadoPago {
  id_estado_pago: number;
  estado_pago: string;
}

export const ESTADO_PAGO_PENDIENTE = 1;
export const ESTADO_PAGO_COMPLETADO = 2;
export const ESTADO_PAGO_REEMBOLSADO = 3;
