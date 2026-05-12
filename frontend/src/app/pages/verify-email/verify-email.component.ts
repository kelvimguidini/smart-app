import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastService } from '../../services/toast.service';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-verify-email',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './verify-email.component.html',
  styleUrls: ['./verify-email.component.scss']
})
export class VerifyEmailComponent implements OnInit {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly route: ActivatedRoute = inject(ActivatedRoute);
  private readonly router: Router = inject(Router);
  private readonly toastService: ToastService = inject(ToastService);
  private readonly apiUrl = environment.apiUrl;

  email: string = '';
  processing = false;
  verificationLinkSent = false;

  ngOnInit() {
    this.email = this.route.snapshot.queryParams['email'] || '';
    if (!this.email) {
      this.toastService.error('E-mail não fornecido.');
      this.router.navigate(['/login']);
    }
  }

  submit() {
    if (!this.email) return;

    this.processing = true;
    this.verificationLinkSent = false;

    this.http.get(`${this.apiUrl}/sanctum/csrf-cookie`).subscribe({
      next: () => {
        this.http.post(`${this.apiUrl}/email/verification-notification`, { email: this.email }).subscribe({
          next: (response: any) => {
            this.processing = false;
            if (response.status === 'verification-link-sent') {
              this.verificationLinkSent = true;
              this.toastService.success('Link reenviado com sucesso!');
            } else if (response.message === 'Email already verified.') {
              this.toastService.info('Conta já ativada! Você pode fazer login.');
              this.router.navigate(['/login']);
            }
          },
          error: (err: HttpErrorResponse) => {
            this.processing = false;
            this.toastService.error('Erro ao enviar o e-mail de verificação.');
            console.error('Error sending verification email:', err);
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
