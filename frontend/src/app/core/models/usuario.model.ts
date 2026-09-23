import { Asistente } from './asistente.model';
import { Organizador } from './organizador.model';

export interface Usuario {
  id_usuario: string;
  nombre: string;
  apellidos: string;
  email: string;
  numero_telefono: string | null;
  calle: string | null;
  vivienda: string | null;
  ciudad: string | null;
  codigo_postal: string | null;
  created_at: string;
  updated_at: string;

  // Llegan solo cuando el endpoint las carga
  asistente?: Asistente;
  organizador?: Organizador | null;
}

// Respuesta de register, login y del alta como organizador
export interface Sesion {
  usuario: Usuario;
  token: string;
  token_type: string;
}
