/**
 * Valores que el front necesita conocer y que el backend define en
 * config/butaka.php. Se declaran aquí, en un único sitio, para que al cambiarlos
 * solo haya que tocar dos ficheros y no todas las pantallas.
 * El backend sigue siendo el que manda: estos valores solo evitan que el usuario
 * llegue a intentar algo que la API va a rechazar
 */

// Entradas que un mismo asistente puede comprar de un evento
export const MAX_ENTRADAS_POR_EVENTO = 6;

// Fidelización. El asistente gana un punto por euro y necesita veinte para
// descontar un euro, con un canje mínimo y un tope sobre el importe
export const PUNTOS_POR_EURO_CANJE = 20;
export const MIN_PUNTOS_CANJE = 100;
export const MAX_PORCENTAJE_CANJE = 50;
