import { Component, inject, OnInit, EffectRef, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { ModalComponent } from '../modal/modal.component';
import { AuthService } from '../../../services/auth.service';
import { SessionRecoveryService } from '../../../services/session-recovery.service';
import { ToastService } from '../../../services/toast.service';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-session-recovery-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalComponent],
  templateUrl: './session-recovery-modal.html',
  styleUrls: ['./session-recovery-modal.scss']
})
export class SessionRecoveryModalComponent implements OnInit {
  private readonly http = inject(HttpClient);
  protected readonly authService = inject(AuthService);
  protected readonly recoveryService = inject(SessionRecoveryService);
  private readonly toastService = inject(ToastService);

  private readonly apiUrl = environment.apiUrl;

  form = {
    email: '',
    password: '',
    remember: true
  };

  processing = false;
  showPassword = false;
  errorMessage = '';

  togglePasswordVisibility() {
    this.showPassword = !this.showPassword;
  }


  constructor() {
    // Keep email updated when the modal opens and a user is already cached in state
    effect(() => {
      if (this.recoveryService.isOpen()) {
        const currentUser = this.authService.user();
        if (currentUser && currentUser.email) {
          this.form.email = currentUser.email;
        }
        this.form.password = '';
        this.errorMessage = '';
      }
    });
  }

  ngOnInit() {}

  onSubmit() {
    if (!this.form.email || !this.form.password) {
      this.errorMessage = 'Por favor, preencha todos os campos.';
      return;
    }

    this.processing = true;
    this.errorMessage = '';

    // First fetch the csrf cookie
    this.http.get(`${this.apiUrl}/sanctum/csrf-cookie`).subscribe({
      next: () => {
        // Then perform login
        this.http.post(`${this.apiUrl}/login`, this.form).subscribe({
          next: () => {
            // Verify and reload authentication state
            this.authService.checkAuth().subscribe({
              next: (success) => {
                this.processing = false;
                if (success) {
                  this.toastService.success('Sessão restaurada com sucesso!');
                  this.recoveryService.onRecoverySuccess();
                } else {
                  this.errorMessage = 'Não foi possível revalidar a sessão. Faça login novamente.';
                }
              },
              error: () => {
                this.processing = false;
                this.errorMessage = 'Erro ao validar nova sessão.';
              }
            });
          },
          error: (err: HttpErrorResponse) => {
            this.processing = false;
            if (err.status === 422) {
              this.errorMessage = 'E-mail ou senha incorretos.';
            } else if (err.error && err.error.message) {
              this.errorMessage = err.error.message;
            } else {
              this.errorMessage = 'Erro ao realizar login. Tente novamente.';
            }
          }
        });
      },
      error: (err) => {
        this.processing = false;
        this.errorMessage = 'Erro ao configurar a segurança da sessão (CSRF).';
        console.error('CSRF recovery setup failed', err);
      }
    });
  }

  onCancel() {
    this.recoveryService.onRecoveryCancel();
    this.toastService.warning('Sessão não restaurada. Redirecionando para login...');
    this.authService.logout();
  }
}
