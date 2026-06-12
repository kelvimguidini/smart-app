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
import { MeasureComponent } from './pages/adds/measure/measure.component';
import { FrequencyComponent } from './pages/adds/frequency/frequency.component';
import { ServiceAddComponent } from './pages/adds/service-add/service-add.component';
import { ProviderServiceComponent } from './pages/adds/provider-service/provider-service.component';
import { CurrenciesComponent } from './pages/admin/currencies/currencies.component';
import { CustomersComponent } from './pages/admin/customers/customers.component';
import { CrdsComponent } from './pages/admin/crds/crds.component';
import { CitiesComponent } from './pages/admin/cities/cities.component';
import { CustomerMetadataComponent } from './pages/admin/customer-metadata/customer-metadata.component';
import { BrandComponent } from './pages/transport/brand/brand.component';
import { CarModelComponent } from './pages/transport/car-model/car-model.component';
import { VehicleComponent } from './pages/transport/vehicle/vehicle.component';
import { TransportServiceComponent } from './pages/transport/transport-service/transport-service.component';
import { BrokerTransComponent } from './pages/transport/broker-trans/broker-trans.component';
import { ProviderTransportComponent } from './pages/transport/provider-transport/provider-transport.component';
import { EventListComponent } from './pages/event/event-list/event-list.component';
import { EventCreateComponent } from './pages/event/event-create/event-create.component';
import { BudgetComponent } from './pages/event/budget/budget.component';

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
  { path: 'measure', component: MeasureComponent, canActivate: [authGuard], title: 'Medidas - SmartApp' },
  { path: 'frequency', component: FrequencyComponent, canActivate: [authGuard], title: 'Frequências - SmartApp' },
  { path: 'service-add', component: ServiceAddComponent, canActivate: [authGuard], title: 'Serviços Adicionais - SmartApp' },
  { path: 'provider-service', component: ProviderServiceComponent, canActivate: [authGuard], title: 'Fornecedores de Serviço - SmartApp' },
  { path: 'currency', component: CurrenciesComponent, canActivate: [authGuard], title: 'Moedas - SmartApp' },
  { path: 'brand', component: BrandComponent, canActivate: [authGuard], title: 'Marcas de Veículo - SmartApp' },
  { path: 'car-model', component: CarModelComponent, canActivate: [authGuard], title: 'Modelos de Veículo - SmartApp' },
  { path: 'vehicle', component: VehicleComponent, canActivate: [authGuard], title: 'Tipos de Veículo - SmartApp' },
  { path: 'transport-service', component: TransportServiceComponent, canActivate: [authGuard], title: 'Serviços de Transporte - SmartApp' },
  { path: 'broker-trans', component: BrokerTransComponent, canActivate: [authGuard], title: 'Brokers de Transporte - SmartApp' },
  { path: 'provider-transport', component: ProviderTransportComponent, canActivate: [authGuard], title: 'Fornecedores de Transporte - SmartApp' },
  { path: 'event-list', component: EventListComponent, canActivate: [authGuard], title: 'Eventos - SmartApp' },
  { path: 'event', component: EventCreateComponent, canActivate: [authGuard], title: 'Criar Evento - SmartApp' },
  { path: 'event/:id', component: EventCreateComponent, canActivate: [authGuard], title: 'Editar Evento - SmartApp' },
  { path: 'event/:id/:tab', component: EventCreateComponent, canActivate: [authGuard], title: 'Editar Evento - SmartApp' },
  { path: 'customer', component: CustomersComponent, canActivate: [authGuard], title: 'Clientes - SmartApp' },
  { path: 'customer-metadata', component: CustomerMetadataComponent, canActivate: [authGuard], title: 'Metadados Cliente - SmartApp' },
  { path: 'crd', component: CrdsComponent, canActivate: [authGuard], title: 'CRDs - SmartApp' },
  { path: 'city', component: CitiesComponent, canActivate: [authGuard], title: 'Cidades - SmartApp' },
  { path: 'confirm-password', component: ConfirmPasswordComponent, canActivate: [authGuard], title: 'Confirmar Senha - SmartApp' },
  { path: 'budget', component: BudgetComponent, title: 'Orçamento - SmartApp' },
  { path: 'budget/:token', component: BudgetComponent, title: 'Orçamento - SmartApp' },
  { path: 'budget/:token/:prove/:user', component: BudgetComponent, title: 'Orçamento - SmartApp' },
  { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
  { path: '**', redirectTo: 'dashboard' }
];
