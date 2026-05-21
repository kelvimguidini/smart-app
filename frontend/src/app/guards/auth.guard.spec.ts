import { describe, it, expect, beforeEach, vi } from 'vitest';
import { TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { authGuard } from './auth.guard';
import { AuthService } from '../services/auth.service';
import { of, Observable, map } from 'rxjs';
import { HttpClientTestingModule } from '@angular/common/http/testing';

describe('authGuard', () => {
  let authService: any;
  let router: any;

  beforeEach(() => {
    const authSpy = { 
      isAuthenticated: vi.fn(),
      checkAuth: vi.fn()
    };
    const routerSpy = { navigate: vi.fn() };

    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [
        { provide: AuthService, useValue: authSpy },
        { provide: Router, useValue: routerSpy }
      ]
    });

    authService = TestBed.inject(AuthService);
    router = TestBed.inject(Router);
  });

  it('should allow access if user is authenticated (CT-006)', () => {
    authService.isAuthenticated.mockReturnValue(true);

    const result = TestBed.runInInjectionContext(() => authGuard({} as any, {} as any));
    
    expect(result).toBe(true);
  });

  it('should redirect to login if user is not authenticated (CT-039)', () => {
    authService.isAuthenticated.mockReturnValue(false);
    authService.checkAuth.mockReturnValue(of(false));

    const result = TestBed.runInInjectionContext(() => authGuard({} as any, {} as any));
    
    if (result instanceof Observable) {
      return result.pipe(
        map(allowed => {
          expect(allowed).toBe(false);
          expect(router.navigate).toHaveBeenCalledWith(['/login']);
        })
      );
    } else {
      expect(result).toBe(false);
      expect(router.navigate).toHaveBeenCalledWith(['/login']);
      return of(true); // Retorno dummy para consistência
    }
  });
});
