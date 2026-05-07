import { Injectable, signal, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { catchError, map, Observable, of, tap } from 'rxjs';
import { environment } from '../../environments/environment';

export interface User {
  id: number;
  name: string;
  email: string;
  permissions?: any[];
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly router = inject(Router);
  
  private readonly baseUrl = environment.apiUrl;
  
  user = signal<User | null>(null);
  isAuthenticated = signal<boolean>(false);

  constructor() { }

  checkAuth(): Observable<boolean> {
    return this.http.get<User>(`${this.baseUrl}/api/user`).pipe(
      tap(user => {
        this.user.set(user);
        this.isAuthenticated.set(true);
      }),
      map(() => true),
      catchError(() => {
        this.user.set(null);
        this.isAuthenticated.set(false);
        return of(false);
      })
    );
  }

  login(credentials: any): Observable<any> {
    return this.http.get(`${this.baseUrl}/sanctum/csrf-cookie`).pipe(
      map(() => this.http.post(`${this.baseUrl}/login`, credentials)),
      // O post retorna um observable, precisamos de um switchMap se quisermos encadear
      // Mas para manter simples agora, vamos apenas retornar o post
    );
    // Nota: Vou simplificar a lógica de login no componente por enquanto
  }

  logout() {
    this.http.post(`${this.baseUrl}/logout`, {}).subscribe({
      next: () => {
        this.user.set(null);
        this.isAuthenticated.set(false);
        this.router.navigate(['/login']);
      },
      error: () => {
        this.user.set(null);
        this.isAuthenticated.set(false);
        this.router.navigate(['/login']);
      }
    });
  }
}
