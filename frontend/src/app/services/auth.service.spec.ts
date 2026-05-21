import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { Router } from '@angular/router';
import { AuthService } from './auth.service';
import { environment } from '../../environments/environment';

describe('AuthService', () => {
  let service: AuthService;
  let httpMock: HttpTestingController;
  let router: any;

  beforeEach(() => {
    const routerSpy = { navigate: vi.fn() };

    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [
        AuthService,
        { provide: Router, useValue: routerSpy }
      ]
    });

    service = TestBed.inject(AuthService);
    httpMock = TestBed.inject(HttpTestingController);
    router = TestBed.inject(Router);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('should update state on successful auth check (CT-036)', () => {
    const mockUser = { id: 1, name: 'Admin', email: 'admin@test.com' };

    service.checkAuth().subscribe(isAuth => {
      expect(isAuth).toBe(true);
      expect(service.isAuthenticated()).toBe(true);
      expect(service.user()).toEqual(mockUser);
    });

    const req = httpMock.expectOne(`${environment.apiUrl}/api/user`);
    expect(req.request.method).toBe('GET');
    req.flush(mockUser);
  });

  it('should clear state on failed auth check (CT-039)', () => {
    service.checkAuth().subscribe(isAuth => {
      expect(isAuth).toBe(false);
      expect(service.isAuthenticated()).toBe(false);
      expect(service.user()).toBeNull();
    });

    const req = httpMock.expectOne(`${environment.apiUrl}/api/user`);
    req.error(new ErrorEvent('Unauthorized'), { status: 401 });
  });

  it('should navigate to login on logout (CT-039)', () => {
    service.logout();

    const req = httpMock.expectOne(`${environment.apiUrl}/logout`);
    req.flush({});

    expect(service.isAuthenticated()).toBe(false);
    expect(router.navigate).toHaveBeenCalledWith(['/login'], { replaceUrl: true });
  });
});
