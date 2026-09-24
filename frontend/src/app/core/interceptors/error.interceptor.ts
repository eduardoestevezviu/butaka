import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { CarritoService } from '../services/carrito.service';

/**
 * Middleware que centraliza el tratamiento del token caducado o revocado. Sin
 * él, cada componente tendría que comprobar el 401 por su cuenta
 * @param req
 * @param next
 * @returns
 */
export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const authService = inject(AuthService);
  const carritoService = inject(CarritoService);
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      // El 401 del inicio de sesión son credenciales incorrectas, no una sesión
      // caducada, y lo tiene que mostrar el propio formulario
      const esLogin = req.url.endsWith('/login');

      if (error.status === 401 && !esLogin) {
        authService.limpiarSesion();
        carritoService.limpiarCarrito();
        router.navigate(['/login']);
      }

      // El error se vuelve a lanzar para que el componente pueda mostrarlo
      return throwError(() => error);
    })
  );
};
