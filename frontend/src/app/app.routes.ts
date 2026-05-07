import { Routes } from '@angular/router';
import { LoginComponent } from './pages/login/login.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ForgotPasswordComponent } from './pages/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './pages/reset-password/reset-password.component';

import { authGuard } from './guards/auth.guard';
import { guestGuard } from './guards/guest.guard';

export const routes: Routes = [
    { path: 'login', component: LoginComponent, canActivate: [guestGuard], title: 'Logar - SmartApp' },
    { path: 'forgot-password', component: ForgotPasswordComponent, canActivate: [guestGuard], title: 'Recuperar Senha - SmartApp' },
    { path: 'reset-password/:token', component: ResetPasswordComponent, canActivate: [guestGuard], title: 'Redefinir Senha - SmartApp' },
    { path: 'dashboard', component: DashboardComponent, canActivate: [authGuard], title: 'Dashboard - SmartApp' },
    { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
];
