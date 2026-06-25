import { Injectable, signal } from '@angular/core';
import { Observable, Subject, take } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SessionRecoveryService {
  isOpen = signal<boolean>(false);
  private isRecovering = false;
  private recoverySubject = new Subject<boolean>();

  constructor() {}

  /**
   * Triggers the session recovery flow. If already recovering, returns the existing stream.
   */
  recoverSession(): Observable<boolean> {
    if (this.isRecovering) {
      return this.recoverySubject.asObservable().pipe(take(1));
    }

    this.isRecovering = true;
    this.isOpen.set(true);

    return this.recoverySubject.asObservable().pipe(take(1));
  }

  /**
   * Called when session is successfully restored. Resumes all pending requests.
   */
  onRecoverySuccess() {
    this.isOpen.set(false);
    this.isRecovering = false;
    this.recoverySubject.next(true);
  }

  /**
   * Called if the user cancels or closes the recovery modal. Rejects all pending requests.
   */
  onRecoveryCancel() {
    this.isOpen.set(false);
    this.isRecovering = false;
    this.recoverySubject.next(false);
  }
}
