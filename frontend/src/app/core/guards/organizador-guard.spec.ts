import { TestBed } from '@angular/core/testing';
import { CanActivateFn } from '@angular/router';

import { organizadorGuard } from './organizador-guard';

describe('organizadorGuard', () => {
  const executeGuard: CanActivateFn = (...guardParameters) => 
      TestBed.runInInjectionContext(() => organizadorGuard(...guardParameters));

  beforeEach(() => {
    TestBed.configureTestingModule({});
  });

  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });
});
