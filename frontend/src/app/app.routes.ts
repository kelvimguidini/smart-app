import { Routes } from '@angular/router';
import { LoginComponent } from './pages/auth/login/login.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ForgotPasswordComponent } from './pages/auth/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './pages/auth/reset-password/reset-password.component';
import { RolesComponent } from './pages/user/roles/roles.component';
import { UsersComponent } from './pages/user/users/users.component';
import { ProfileComponent } from './pages/user/profile/profile.component';
import { AptoComponent } from './pages/hotel/apto/apto.component';
import { RegimeComponent } from './pages/hotel/regime/regime.component';
import { CategoryComponent } from './pages/hotel/category/category.component';
import { PurposeComponent } from './pages/hotel/purpose/purpose.component';
import { ServiceTypeComponent } from './pages/a&b/service-type/service-type.component';
import { ConfirmPasswordComponent } from './pages/auth/confirm-password/confirm-password.component';
import { VerifyEmailComponent } from './pages/auth/verify-email/verify-email.component';
import { BrokerComponent } from './pages/hotel/broker/broker.component';
import { HotelComponent } from './pages/hotel/hotel/hotel.component';
import { ServiceComponent } from './pages/a&b/service/service.component';
import { LocalComponent } from './pages/a&b/local/local.component';
import { ServiceHallComponent } from './pages/hall/service-hall/service-hall.component';
import { PurposeHallComponent } from './pages/hall/purpose-hall/purpose-hall.component';

import { authGuard } from './guards/auth.guard';
import { guestGuard } from './guards/guest.guard';

export const routes: Routes = [
  { path: 'login', component: LoginComponent, canActivate: [guestGuard], title: 'Logar - SmartApp' },
  { path: 'forgot-password', component: ForgotPasswordComponent, canActivate: [guestGuard], title: 'Recuperar Senha - SmartApp' },
  { path: 'reset-password/:token', component: ResetPasswordComponent, canActivate: [guestGuard], title: 'Redefinir Senha - SmartApp' },
  { path: 'verify-email', component: VerifyEmailComponent, canActivate: [guestGuard], title: 'Ativar Conta - SmartApp' },
  { path: 'dashboard', component: DashboardComponent, canActivate: [authGuard], title: 'Dashboard - SmartApp' },
  { path: 'role', component: RolesComponent, canActivate: [authGuard], title: 'Grupo de Acesso - SmartApp' },
  { path: 'register', component: UsersComponent, canActivate: [authGuard], title: 'Usuários - SmartApp' },
  { path: 'profile', component: ProfileComponent, canActivate: [authGuard], title: 'Meus Dados - SmartApp' },
  { path: 'apto', component: AptoComponent, canActivate: [authGuard], title: 'Apartamento - SmartApp' },
  { path: 'regime', component: RegimeComponent, canActivate: [authGuard], title: 'Regime - SmartApp' },
  { path: 'category', component: CategoryComponent, canActivate: [authGuard], title: 'Categorias - SmartApp' },
  { path: 'purpose', component: PurposeComponent, canActivate: [authGuard], title: 'Propósitos - SmartApp' },
  { path: 'service-type', component: ServiceTypeComponent, canActivate: [authGuard], title: 'Tipos de Serviço - SmartApp' },
  { path: 'service', component: ServiceComponent, canActivate: [authGuard], title: 'Serviços - SmartApp' },
  { path: 'local', component: LocalComponent, canActivate: [authGuard], title: 'Locais - SmartApp' },
  { path: 'service-hall', component: ServiceHallComponent, canActivate: [authGuard], title: 'Serviços do Salão - SmartApp' },
  { path: 'purpose-hall', component: PurposeHallComponent, canActivate: [authGuard], title: 'Propósitos do Salão - SmartApp' },
  { path: 'broker', component: BrokerComponent, canActivate: [authGuard], title: 'Broker - SmartApp' },
  { path: 'hotel', component: HotelComponent, canActivate: [authGuard], title: 'Hotéis - SmartApp' },
  { path: 'confirm-password', component: ConfirmPasswordComponent, canActivate: [authGuard], title: 'Confirmar Senha - SmartApp' },
  { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
];
