import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Router, RouterLink } from '@angular/router';
import { ToastService } from '../../services/toast.service';
import { AuthService } from '../../services/auth.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly router: Router = inject(Router);
  private readonly toastService: ToastService = inject(ToastService);
  private readonly authService: AuthService = inject(AuthService);

  private readonly apiUrl = environment.apiUrl;

  form = {
    email: '',
    password: '',
    remember: false
  };

  errors: any = {};
  processing = false;

  submit() {
    this.processing = true;
    this.errors = {};

    this.http.get(`${this.apiUrl}/sanctum/csrf-cookie`).subscribe({
      next: () => {
        this.http.post(`${this.apiUrl}/login`, this.form).subscribe({
          next: (response: any) => {
            // Após o login, buscamos os dados do usuário para atualizar o estado global
            this.authService.checkAuth().subscribe(() => {
              this.processing = false;
              this.router.navigate(['/dashboard']);
            });
          },
          error: (err: HttpErrorResponse) => {
            this.processing = false;
            if (err.status === 422) {
              this.errors = err.error.errors;
            } else if (err.error && err.error.message) {
              this.toastService.error(err.error.message);
            }
            this.form.password = '';
          }
        });
      },
      error: (err: HttpErrorResponse) => {
        this.processing = false;
        console.error('CSRF setup failed', err);
      }
    });
  }
}
