import { TestBed } from '@angular/core/testing';

import { TipoEntradaService } from './tipo-entrada.service';

describe('TipoEntradaService', () => {
  let service: TipoEntradaService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TipoEntradaService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
