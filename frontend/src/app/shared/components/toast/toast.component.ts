import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ToastService } from '../../../services/toast.service';


@Component({
  selector: 'app-toast',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
      @for (toast of toastService.toasts(); track toast) {
        <div class="toast show align-items-center text-white border-0 mb-2" 
             [class.bg-success]="toast.type === 'success'"
             [class.bg-danger]="toast.type === 'danger'"
             [class.bg-warning]="toast.type === 'warning'"
             [class.bg-info]="toast.type === 'info'"
             role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              {{ toast.message }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                    (click)="toastService.remove(toast)" aria-label="Close"></button>
          </div>
        </div>
      }
    </div>
  `,
  styles: [`
    .toast {
      min-width: 250px;
    }
  `]
})
export class ToastComponent {
  toastService = inject(ToastService);
}
