import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const authReq = req.clone({
    withCredentials: true,
    setHeaders: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  });
  
  return next(authReq);
};
