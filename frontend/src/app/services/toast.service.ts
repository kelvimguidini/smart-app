import { Injectable, signal } from '@angular/core';

export interface ToastMessage {
  message: string;
  type: 'success' | 'danger' | 'warning' | 'info';
}

@Injectable({
  providedIn: 'root'
})
export class ToastService {
  toasts = signal<ToastMessage[]>([]);

  show(message: string, type: 'success' | 'danger' | 'warning' | 'info' = 'info') {
    const toast: ToastMessage = { message, type };
    this.toasts.update(current => [...current, toast]);

    // Auto-remove after 5 seconds
    setTimeout(() => {
      this.remove(toast);
    }, 5000);
  }

  remove(toast: ToastMessage) {
    this.toasts.update(current => current.filter(t => t !== toast));
  }

  success(message: string) { this.show(message, 'success'); }
  error(message: string) { this.show(message, 'danger'); }
  warning(message: string) { this.show(message, 'warning'); }
  info(message: string) { this.show(message, 'info'); }
}
