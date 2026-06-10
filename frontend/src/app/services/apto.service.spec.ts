import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { AptoService } from './apto.service';
import { environment } from '../../environments/environment';

describe('AptoService', () => {
  let service: AptoService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [AptoService]
    });
    service = TestBed.inject(AptoService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('should fetch aptos with pagination (CT-033)', () => {
    const mockResponse = {
      data: [{ id: 1, name: 'Single', active: true }],
      total: 1
    };

    service.getAptos({ page: 1 }).subscribe(response => {
      expect(response.data.length).toBe(1);
      expect(response.total).toBe(1);
    });

    const req = httpMock.expectOne(req => req.url.includes('/api/aptos') && req.params.has('page'));
    expect(req.request.method).toBe('GET');
    req.flush(mockResponse);
  });

  it('should send activation request (CT-004/005)', () => {
    service.activateApto(1).subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/api/aptos/1/activate`);
    expect(req.request.method).toBe('PUT');
    req.flush({});
  });

  it('should send deactivation request (CT-004/005)', () => {
    service.deactivateApto(1).subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/api/aptos/1/deactivate`);
    expect(req.request.method).toBe('PUT');
    req.flush({});
  });
});
