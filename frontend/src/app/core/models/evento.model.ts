import { Categoria } from './categoria.model';
import { EstadoEvento } from './estado-evento.model';
import { Organizador } from './organizador.model';
import { TipoEntrada } from './tipo-entrada.model';

export interface Evento {
  id_evento: string;
  nombre: string;
  descripcion: string | null;
  ubicacion: string;
  fecha: string;
  imagen: string;

  // Dirección completa de la imagen. Es la que se pinta, nunca imagen
  imagen_url: string;

  id_usuario: string;
  id_categoria: number;
  id_estado_evento: number;
  created_at: string;
  updated_at: string;

  organizador?: Organizador;
  categoria?: Categoria;
  estado_evento?: EstadoEvento;
  tipo_entradas?: TipoEntrada[];
}
