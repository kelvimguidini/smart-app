import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoadingService } from '../../../services/loading.service';

@Component({
  selector: 'app-loading-bar',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div *ngIf="loadingService.isLoading()" class="loading-bar-container">
      <div class="loading-bar" [style.width.%]="loadingService.progress()"></div>
    </div>
  `,
  styles: [`
    .loading-bar-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 3px;
      z-index: 9999;
      background-color: transparent;
    }
    .loading-bar {
      height: 100%;
      background-color: #4f46e5; /* Indigo-600 */
      transition: width 0.3s ease-out;
      box-shadow: 0 0 10px rgba(79, 70, 229, 0.5);
    }
  `]
})
export class LoadingBarComponent {
  loadingService = inject(LoadingService);
}
