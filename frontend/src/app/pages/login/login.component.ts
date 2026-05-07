import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Router } from '@angular/router';
import { ToastService } from '../../services/toast.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly router: Router = inject(Router);
  private readonly toastService: ToastService = inject(ToastService);

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

    this.http.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true }).subscribe({
      next: () => {
        this.http.post('http://localhost:8000/login', this.form, {
          withCredentials: true,
          headers: {
            'Accept': 'application/json'
          }
        }).subscribe({
          next: (response: any) => {
            this.processing = false;
            this.router.navigate(['/']);
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
