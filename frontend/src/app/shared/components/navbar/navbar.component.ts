import { Component, inject, ElementRef, HostListener, output } from '@angular/core';
import { CommonModule, DOCUMENT } from '@angular/common';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent {
  authService = inject(AuthService);
  el = inject(ElementRef);
  user = this.authService.user;
  private readonly document = inject(DOCUMENT);

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

  resolveExternalLink(link: string | undefined): string {
    if (!link) return '';
    if (link.startsWith('http://') || link.startsWith('https://')) {
      return link;
    }
    const base = this.document.querySelector('base')?.getAttribute('href') || '/';
    const cleanBase = base.endsWith('/') ? base.slice(0, -1) : base;
    const cleanLink = link.startsWith('/') ? link : '/' + link;
    return cleanBase + cleanLink;
  }
}
