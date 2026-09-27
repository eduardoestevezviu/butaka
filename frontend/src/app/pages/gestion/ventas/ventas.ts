import { Component, signal } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { CurrencyPipe, DatePipe } from '@angular/common';
import { EventoService } from '../../../core/services/evento.service';
import { VentasEvento } from '../../../core/models/ventas.model';
import { MatCardModule } from '@angular/material/card';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

@Component({
  selector: 'app-ventas',
  standalone: true,
  imports: [
    CurrencyPipe,
    DatePipe,
    MatCardModule,
    MatTableModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './ventas.html',
  styleUrl: './ventas.scss',
})
export class Ventas {
  ventas = signal<VentasEvento | null>(null);
  cargando = signal(true);
  error = signal('');

  columnas = ['nombre', 'aforo', 'vendidas', 'pendientes', 'disponibles', 'recaudacion'];

  constructor(
    private eventoService: EventoService,
    private route: ActivatedRoute,
    private router: Router
  ) { }

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('id');

    if (id) {
      this.cargarVentas(id);
    }
  }

  cargarVentas(id: string) {
    this.cargando.set(true);
    this.error.set('');

    this.eventoService.getVentas(id).subscribe({
      next: (respuesta) => {
        this.ventas.set(respuesta.data);
        this.cargando.set(false);
      },
      error: (err) => {
        // Un 403 aquí significa que el evento no es suyo
        this.error.set(err.error?.message ?? 'No se pudieron cargar las ventas');
        this.cargando.set(false);
      }
    });
  }

  volver() {
    this.router.navigate(['/gestion/eventos']);
  }
}
