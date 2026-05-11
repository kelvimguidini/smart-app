import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { ToastService } from '../../services/toast.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-forgot-password',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss']
})
export class ForgotPasswordComponent {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly toastService: ToastService = inject(ToastService);
  private readonly apiUrl = environment.apiUrl;

  email: string = '';
  status: string | null = null;
  errors: any = {};
  processing = false;

  submit() {
    this.processing = true;
    this.errors = {};
    this.status = null;

    this.http.get(`${this.apiUrl}/sanctum/csrf-cookie`).subscribe({
      next: () => {
        this.http.post(`${this.apiUrl}/forgot-password`, { email: this.email }).subscribe({
          next: (response: any) => {
            this.processing = false;
            this.status = response.status;
            this.toastService.success(this.status || 'Link de redefinição enviado!');
            this.email = '';
          },
          error: (err: HttpErrorResponse) => {
            this.processing = false;
            if (err.status === 422) {
              this.errors = err.error.errors;
            } else if (err.error && err.error.message) {
              this.toastService.error(err.error.message);
            } else {
              this.toastService.error('Ocorreu um erro ao processar sua solicitação.');
            }
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
