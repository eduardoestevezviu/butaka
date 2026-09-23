import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

/**
 * Guard que comprueba que un usuario sea organizador para acceder a las
 * rutas de gestión de eventos si no, envía al catálogo
 * @param route
 * @param state
 * @returns
 */
export const organizadorGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.esOrganizador()) {
    return true;
  }

  router.navigate(['/']);
  return false;
};
