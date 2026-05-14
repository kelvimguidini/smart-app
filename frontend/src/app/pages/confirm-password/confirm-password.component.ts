import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Router, RouterLink } from '@angular/router';
import { ToastService } from '../../services/toast.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-confirm-password',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './confirm-password.component.html',
  styleUrls: ['./confirm-password.component.scss']
})
export class ConfirmPasswordComponent {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly router: Router = inject(Router);
  private readonly toastService: ToastService = inject(ToastService);
  private readonly apiUrl = environment.apiUrl;

  password: string = '';
  errors: any = {};
  processing = false;
  showPassword = false;

  submit() {
    this.processing = true;
    this.errors = {};

    this.http.get(`${this.apiUrl}/sanctum/csrf-cookie`).subscribe({
      next: () => {
        this.http.post(`${this.apiUrl}/confirm-password`, { password: this.password }).subscribe({
          next: () => {
            this.processing = false;
            this.toastService.success('Senha confirmada com sucesso!');
            // Redirect back to dashboard or where intended
            this.router.navigate(['/dashboard']);
          },
          error: (err: HttpErrorResponse) => {
            this.processing = false;
            if (err.status === 422) {
              this.errors = err.error.errors;
            } else if (err.status === 401) {
              this.errors = { password: ['A senha informada está incorreta.'] };
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
