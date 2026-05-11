import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastService } from '../../services/toast.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss']
})
export class ResetPasswordComponent implements OnInit {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly route: ActivatedRoute = inject(ActivatedRoute);
  private readonly router: Router = inject(Router);
  private readonly toastService: ToastService = inject(ToastService);
  private readonly apiUrl = environment.apiUrl;

  form = {
    token: '',
    email: '',
    password: '',
    password_confirmation: ''
  };

  errors: any = {};
  processing = false;

  ngOnInit() {
    // Capture token from path and email from query params
    this.form.token = this.route.snapshot.params['token'] || '';
    this.form.email = this.route.snapshot.queryParams['email'] || '';
  }

  submit() {
    this.processing = true;
    this.errors = {};

    this.http.get(`${this.apiUrl}/sanctum/csrf-cookie`).subscribe({
      next: () => {
        this.http.post(`${this.apiUrl}/reset-password`, this.form).subscribe({
          next: (response: any) => {
            this.processing = false;
            this.toastService.success(response.status || 'Senha redefinida com sucesso!');
            this.router.navigate(['/login']);
          },
          error: (err: HttpErrorResponse) => {
            this.processing = false;
            if (err.status === 422) {
              this.errors = err.error.errors;
            } else if (err.error && err.error.message) {
              this.toastService.error(err.error.message);
            } else {
              this.toastService.error('Erro ao redefinir senha.');
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
