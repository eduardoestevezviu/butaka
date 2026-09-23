import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

/**
 * Middleware para automatizar la adición del token en la cabecera
 * de las peticiones http
 * @param req
 * @param next
 * @returns
 */
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const authService = inject(AuthService);
  const token = authService.getToken();

  if (token) {
    const reqClone = req.clone({
      headers: req.headers.set('Authorization', `Bearer ${token}`)
    });
    // Pasa la petición al siguiente interceptor si lo hubiera,
    // si no lo envía al back
    return next(reqClone);
  }

  return next(req);
};
