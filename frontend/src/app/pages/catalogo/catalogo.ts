import { Component, computed, signal } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DatePipe } from '@angular/common';
import { EventoService } from '../../core/services/evento.service';
import { CategoriaService } from '../../core/services/categoria.service';
import { Evento } from '../../core/models/evento.model';
import { Categoria } from '../../core/models/categoria.model';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

@Component({
  selector: 'app-catalogo',
  standalone: true,
  imports: [
    DatePipe,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './catalogo.html',
  styleUrl: './catalogo.scss',
})
export class Catalogo {
  eventos = signal<Evento[]>([]);
  destacados = signal<Evento[]>([]);
  categorias = signal<Categoria[]>([]);
  cargando = signal(true);
  error = signal('');
  buscar = signal('');
  // Vale null cuando no hay ninguna categoría elegida, que es "todas"
  categoria = signal<number | null>(null);
  paginaActual = signal(1);
  totalPaginas = signal(1);

  // Nombre de la categoría filtrada, para encabezar el listado
  titulo = computed(() => {
    const filtrada = this.categoria();

    if (filtrada === null) {
      return 'Todos los eventos';
    }

    for (const categoria of this.categorias()) {
      if (categoria.id_categoria === filtrada) {
        return categoria.categoria;
      }
    }

    return 'Todos los eventos';
  });

  constructor(
    private eventoService: EventoService,
    private categoriaService: CategoriaService,
    private router: Router,
    private route: ActivatedRoute
  ) { }

  ngOnInit() {
    this.cargarCategorias();
    this.cargarDestacados();

    // La categoría viaja en la dirección, puesta por los enlaces de la barra. Al
    // escucharla aquí, el catálogo se recarga sin que la barra tenga que hablar con
    // él, y el enlace filtrado se puede compartir o recargar
    this.route.queryParams.subscribe((parametros) => {
      if (parametros['categoria']) {
        this.categoria.set(Number(parametros['categoria']));
      } else {
        this.categoria.set(null);
      }

      this.paginaActual.set(1);
      this.cargarEventos();
    });
  }

  /**
   * Carga la banda de destacados. Llega vacía mientras ningún organizador tenga
   * ocupación medible, y entonces la banda no se pinta
   */
  cargarDestacados() {
    this.eventoService.getDestacados().subscribe({
      next: (respuesta) => this.destacados.set(respuesta.data),
      // Si falla, el catálogo funciona igual sin la banda
      error: () => this.destacados.set([])
    });
  }

  /**
   * Carga las categorías. Solo se usan para poner el nombre de la que esté
   * filtrada encima del listado, porque los enlaces viven en la barra
   */
  cargarCategorias() {
    this.categoriaService.getCategorias().subscribe({
      next: (respuesta) => this.categorias.set(respuesta.data),
      // Si fallan las categorías el catálogo sigue siendo usable sin el filtro
      error: () => this.categorias.set([])
    });
  }

  cargarEventos() {
    this.cargando.set(true);
    this.error.set('');

    this.eventoService.getEventos({
      categoria: this.categoria(),
      buscar: this.buscar(),
      page: this.paginaActual()
    }).subscribe({
      next: (respuesta) => {
        // data.data porque la primera es la del ResultResponse y la segunda
        // la del paginador de Laravel
        this.eventos.set(respuesta.data.data);
        this.totalPaginas.set(respuesta.data.last_page);
        this.cargando.set(false);
      },
      error: (err) => {
        this.error.set(err.error?.message ?? 'Error al cargar los eventos');
        this.cargando.set(false);
      }
    });
  }

  /**
   * Cualquier cambio de filtro devuelve a la primera página, porque la página
   * en la que estabas puede no existir con el nuevo filtro
   */
  onFiltrar() {
    this.paginaActual.set(1);
    this.cargarEventos();
  }

  cambiarPagina(pagina: number) {
    this.paginaActual.set(pagina);
    this.cargarEventos();
  }

  verDetalle(evento: Evento) {
    this.router.navigate(['/eventos', evento.id_evento]);
  }
}
