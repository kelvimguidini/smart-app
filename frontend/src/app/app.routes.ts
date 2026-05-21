import { Routes } from '@angular/router';
import { LoginComponent } from './pages/login/login.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ForgotPasswordComponent } from './pages/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './pages/reset-password/reset-password.component';
import { RolesComponent } from './pages/roles/roles.component';
import { AptoComponent } from './pages/apto/apto.component';
import { RegimeComponent } from './pages/regime/regime.component';
import { CategoryComponent } from './pages/category/category.component';
import { PurposeComponent } from './pages/purpose/purpose.component';
import { ServiceTypeComponent } from './pages/service-type/service-type.component';
import { ConfirmPasswordComponent } from './pages/confirm-password/confirm-password.component';
import { VerifyEmailComponent } from './pages/verify-email/verify-email.component';
import { BrokerComponent } from './pages/broker/broker.component';
import { HotelComponent } from './pages/hotel/hotel.component';
import { ServiceComponent } from './pages/service/service.component';
import { LocalComponent } from './pages/local/local.component';

import { authGuard } from './guards/auth.guard';
import { guestGuard } from './guards/guest.guard';

export const routes: Routes = [
    { path: 'login', component: LoginComponent, canActivate: [guestGuard], title: 'Logar - SmartApp' },
    { path: 'forgot-password', component: ForgotPasswordComponent, canActivate: [guestGuard], title: 'Recuperar Senha - SmartApp' },
    { path: 'reset-password/:token', component: ResetPasswordComponent, canActivate: [guestGuard], title: 'Redefinir Senha - SmartApp' },
    { path: 'verify-email', component: VerifyEmailComponent, canActivate: [guestGuard], title: 'Ativar Conta - SmartApp' },
    { path: 'dashboard', component: DashboardComponent, canActivate: [authGuard], title: 'Dashboard - SmartApp' },
    { path: 'roles', component: RolesComponent, canActivate: [authGuard], title: 'Grupo de Acesso - SmartApp' },
    { path: 'apto', component: AptoComponent, canActivate: [authGuard], title: 'Apartamento - SmartApp' },
    { path: 'regime', component: RegimeComponent, canActivate: [authGuard], title: 'Regime - SmartApp' },
    { path: 'category', component: CategoryComponent, canActivate: [authGuard], title: 'Categorias - SmartApp' },
    { path: 'purpose', component: PurposeComponent, canActivate: [authGuard], title: 'Propósitos - SmartApp' },
    { path: 'service-type', component: ServiceTypeComponent, canActivate: [authGuard], title: 'Tipos de Serviço - SmartApp' },
    { path: 'service', component: ServiceComponent, canActivate: [authGuard], title: 'Serviços - SmartApp' },
    { path: 'local', component: LocalComponent, canActivate: [authGuard], title: 'Locais - SmartApp' },
    { path: 'broker', component: BrokerComponent, canActivate: [authGuard], title: 'Broker - SmartApp' },
    { path: 'hotel', component: HotelComponent, canActivate: [authGuard], title: 'Hotéis - SmartApp' },
    { path: 'confirm-password', component: ConfirmPasswordComponent, canActivate: [authGuard], title: 'Confirmar Senha - SmartApp' },
    { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
];
