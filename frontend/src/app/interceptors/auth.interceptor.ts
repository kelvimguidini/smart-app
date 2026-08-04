import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, switchMap, throwError } from 'rxjs';
import { SessionRecoveryService } from '../services/session-recovery.service';
import { AuthService } from '../services/auth.service';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const recoveryService = inject(SessionRecoveryService);
  const authService = inject(AuthService);
  const router = inject(Router);

  const authReq = req.clone({
    withCredentials: true,
    setHeaders: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  });
  
  return next(authReq).pipe(
    catchError((error: HttpErrorResponse) => {
      // 419 (CSRF mismatch) or 401 (Unauthorized)
      // Only recover if the user was previously authenticated and we aren't already on login/csrf requests
      const isAuth = authService.isAuthenticated();
      const isAuthEndpoint = req.url.includes('/login') || req.url.includes('/sanctum/csrf-cookie');

      if ((error.status === 419 || error.status === 401) && isAuth && !isAuthEndpoint) {
        return recoveryService.recoverSession().pipe(
          switchMap((recovered) => {
            if (recovered) {
              // Clone the original request again to ensure it picks up the refreshed cookies/headers
              const retriedReq = req.clone({
                withCredentials: true,
                setHeaders: {
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
                }
              });
              return next(retriedReq);
            } else {
              // If recovery was cancelled, logout and reject the request
              authService.logout();
              return throwError(() => error);
            }
          })
        );
      }
      return throwError(() => error);
    })
  );
};

