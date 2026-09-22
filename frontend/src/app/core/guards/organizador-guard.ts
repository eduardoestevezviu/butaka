import { CanActivateFn } from '@angular/router';

export const organizadorGuard: CanActivateFn = (route, state) => {
  return true;
};
