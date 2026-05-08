import { Component, inject, ElementRef, HostListener, output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent {
  authService = inject(AuthService);
  el = inject(ElementRef);
  user = this.authService.user;

  toggleSidebar = output<void>();

  public isUserMenuOpen = false;
  public imgProfile = 'images/undraw_profile.svg';

  @HostListener('document:click', ['$event'])
  onDocumentClick(event: MouseEvent) {
    // Se o clique for fora do componente, fecha o menu
    if (!this.el.nativeElement.contains(event.target)) {
      this.isUserMenuOpen = false;
    }
  }

  public toggleUserMenu() {
    this.isUserMenuOpen = !this.isUserMenuOpen;
  }

  public logout() {
    this.authService.logout();
  }
}
