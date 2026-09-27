import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth-guard';
import { organizadorGuard } from './core/guards/organizador-guard';

export const routes: Routes = [
  /**
   * RUTAS PÚBLICAS
   */
  {
    path: '',
    loadComponent: () => import('./pages/catalogo/catalogo').then(m => m.Catalogo)
  },
  {
    path: 'eventos/:id',
    loadComponent: () => import('./pages/evento/evento').then(m => m.Evento)
  },
  {
    path: 'login',
    loadComponent: () => import('./pages/auth/login/login').then(m => m.Login)
  },
  {
    path: 'registro',
    loadComponent: () => import('./pages/auth/registro/registro').then(m => m.Registro)
  },

  /**
   * RUTAS DE ASISTENTE, exigen sesión iniciada
   */
  {
    path: 'carrito',
    canActivate: [authGuard],
    loadComponent: () => import('./pages/carrito/carrito').then(m => m.Carrito)
  },
  {
    path: 'compras',
    canActivate: [authGuard],
    loadComponent: () => import('./pages/compras/compras').then(m => m.Compras)
  },
  {
    path: 'perfil',
    canActivate: [authGuard],
    loadComponent: () => import('./pages/perfil/perfil').then(m => m.Perfil)
  },

  /**
   * RUTAS DE ORGANIZADOR. El primer guard comprueba la sesión y el segundo
   * que además tenga ficha de organizador
   */
  {
    path: 'gestion',
    canActivate: [authGuard, organizadorGuard],
    children: [
      {
        path: 'eventos',
        loadComponent: () => import('./pages/gestion/gestion-eventos/gestion-eventos').then(m => m.GestionEventos)
      },
      {
        path: 'eventos/:id/ventas',
        loadComponent: () => import('./pages/gestion/ventas/ventas').then(m => m.Ventas)
      },
      {
        path: 'facturacion',
        loadComponent: () => import('./pages/gestion/facturacion/facturacion').then(m => m.Facturacion)
      },
    ]
  },

  /**
   * RUTAS DE ERROR. El comodín debe ir siempre el último
   */
  {
    path: 'error',
    loadComponent: () => import('./pages/error/error').then(m => m.Error)
  },
  {
    path: '**',
    redirectTo: '/error'
  }
];
