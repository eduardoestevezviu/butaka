/**
 * Valores que el front necesita conocer y que el backend define en
 * config/butaka.php. Se declaran aquí, en un único sitio, para que al cambiarlos
 * solo haya que tocar dos ficheros y no todas las pantallas.
 * El backend sigue siendo el que manda: estos valores solo evitan que el usuario
 * llegue a intentar algo que la API va a rechazar
 */

// Entradas que un mismo asistente puede comprar de un evento
export const MAX_ENTRADAS_POR_EVENTO = 6;
