import { TestBed } from '@angular/core/testing';

import { TipoEntrada } from './tipo-entrada';

describe('TipoEntrada', () => {
  let service: TipoEntrada;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TipoEntrada);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
