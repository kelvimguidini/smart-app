import { Component, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MenuComponent } from '../../components/menu/menu.component';
import { NavbarComponent } from '../../components/navbar/navbar.component';
import { ToastComponent } from '../../components/toast/toast.component';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-authenticated-layout',
  standalone: true,
  imports: [CommonModule, MenuComponent, NavbarComponent, ToastComponent],
  templateUrl: './authenticated-layout.component.html',
  styleUrls: ['./authenticated-layout.component.scss']
})
export class AuthenticatedLayoutComponent {
  authService = inject(AuthService);
  isSidebarToggled = signal(false);

  toggleSidebar() {
    this.isSidebarToggled.update(val => !val);
  }
}
