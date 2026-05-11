import { Injectable, signal } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class LoadingService {
  private activeRequests = 0;
  isLoading = signal<boolean>(false);
  progress = signal<number>(0);
  private interval: any;

  show() {
    this.activeRequests++;
    if (this.activeRequests === 1) {
      this.startLoading();
    }
  }

  hide() {
    this.activeRequests--;
    if (this.activeRequests <= 0) {
      this.activeRequests = 0;
      this.finishLoading();
    }
  }

  private startLoading() {
    this.isLoading.set(true);
    this.progress.set(0);
    
    // Simulate initial jump
    setTimeout(() => this.progress.set(20), 50);

    // Slowly crawl
    this.interval = setInterval(() => {
      const current = this.progress();
      if (current < 90) {
        // Smaller steps as it gets closer to 90
        const increment = (90 - current) * 0.1;
        this.progress.set(current + increment);
      }
    }, 200);
  }

  private finishLoading() {
    clearInterval(this.interval);
    this.progress.set(100);
    
    // Hide after animation finish
    setTimeout(() => {
      if (this.activeRequests === 0) {
        this.isLoading.set(false);
        this.progress.set(0);
      }
    }, 300);
  }
}
