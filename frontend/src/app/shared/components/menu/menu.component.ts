import { Component, computed, inject, signal, input, output, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, NavigationEnd, RouterLink, RouterLinkActive } from '@angular/router';
import { filter } from 'rxjs/operators';
import { AuthService } from '../../../services/auth.service';

interface SubMenuItem {
  link: string;
  name: string;
  active?: boolean;
  role: string | string[];
}

interface MenuItem {
  name: string;
  icon: string;
  isItem: boolean;
  link?: string;
  active?: boolean;
  collapseHeader?: string;
  subMenu?: SubMenuItem[];
  divider?: boolean;
  collapsed?: boolean;
}

@Component({
  selector: 'app-menu',
  standalone: true,
  imports: [CommonModule, RouterLink, RouterLinkActive],
  templateUrl: './menu.component.html',
  styleUrls: ['./menu.component.scss']
})
export class MenuComponent implements OnInit {
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  
  isToggled = input<boolean>(false);
  toggleSidebar = output<void>();

  menuTitle = 'SmartApp';
  userPermissions = computed(() => this.authService.user()?.permissions || []);

  constructor() {
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe((event: any) => {
      this.checkActiveMenu(event.urlAfterRedirects);
    });
  }

  ngOnInit() {
    this.checkActiveMenu(this.router.url);
  }

  checkActiveMenu(url: string) {
    const urlWithoutParams = url.split('?')[0];
    
    this.menuItems.forEach(item => {
      if (!item.isItem && item.subMenu) {
        const isActive = item.subMenu.some(sub => {
          return urlWithoutParams === sub.link || urlWithoutParams.startsWith(sub.link + '/');
        });
        if (isActive) {
          item.collapsed = false;
        } else {
          // Garante que menus inativos comecem fechados para evitar dois abertos
          item.collapsed = true;
        }
      }
    });
  }

  menuItems: MenuItem[] = [
    {
      link: '/dashboard',
      name: 'Inicio',
      icon: 'fas fa-fw fa-home',
      isItem: true,
    },
    {
      name: 'Usuários',
      icon: 'fas fw fa-users',
      isItem: false,
      collapseHeader: 'Administrar acessos',
      collapsed: true,
      subMenu: [
        { link: '/role', name: 'Grupo de Acesso', role: 'role_admin' },
        { link: '/register', name: 'Usuário', role: 'user_admin' }
      ],
    },
    {
      name: 'Hotel',
      icon: 'fa fa-bed',
      isItem: false,
      collapseHeader: 'Tabelas auxiliares',
      collapsed: true,
      subMenu: [
        { link: '/broker', name: 'Broker', role: 'broker_admin' },
        { link: '/regime', name: 'Regime', role: 'regime_admin' },
        { link: '/purpose', name: 'Propósito', role: 'purpose_admin' },
        { link: '/category', name: 'Categoria', role: 'category_admin' },
        { link: '/apto', name: 'Apartamento', role: 'apto_admin' },
        { link: '/hotel', name: 'Hotel', role: 'admin_provider' },
      ]
    },
    {
      name: 'A&B',
      icon: 'fa fa-utensils',
      isItem: false,
      collapseHeader: 'Tabelas auxiliares',
      collapsed: true,
      subMenu: [
        { link: '/service', name: 'Serviço', role: 'service_admin' },
        { link: '/service-type', name: 'Tipo de Serviço', role: 'service_type_admin' },
        { link: '/local', name: 'Local', role: 'local_admin' },
      ]
    },
    {
      name: 'Salões',
      icon: 'fa fa-warehouse',
      isItem: false,
      collapseHeader: 'Tabelas auxiliares',
      collapsed: true,
      subMenu: [
        { link: '/service-hall', name: 'Serviço', role: 'service_hall_admin' },
        { link: '/purpose-hall', name: 'Propósito', role: 'purpose_hall_admin' },
      ]
    },
    {
      name: 'Adicionais',
      icon: 'fa fa-stream',
      isItem: false,
      collapseHeader: 'Tabelas auxiliares',
      collapsed: true,
      subMenu: [
        { link: '/service-add', name: 'Serviços', role: 'service_add_admin' },
        { link: '/measure', name: 'Medida', role: 'measure_admin' },
        { link: '/frequency', name: 'Frequência', role: 'frequency_admin' },
        { link: '/provider-service', name: 'Fornecedor', role: 'admin_provider_service' },
      ]
    },
    {
      name: 'Transporte',
      icon: 'fa fa-car-alt',
      isItem: false,
      collapseHeader: 'Tabelas auxiliares',
      collapsed: true,
      subMenu: [
        { link: '/broker-trans', name: 'Broker', role: 'broker_trans_admin' },
        { link: '/brand', name: 'Marca', role: 'brand_admin' },
        { link: '/car-model', name: 'Modelo de Carro', role: 'car_model_admin' },
        { link: '/vehicle', name: 'Tipo de Veículo', role: 'vehicle_admin' },
        { link: '/transport-service', name: 'Serviços', role: 'transport_service_admin' },
        { link: '/provider-transport', name: 'Fornecedor', role: 'admin_provider_transport' },
      ]
    },
    {
      name: 'Administrativo',
      icon: 'fa fa-user-cog',
      isItem: false,
      collapseHeader: 'Tabelas auxiliares',
      collapsed: true,
      subMenu: [
        { link: '/currency', name: 'Moeda', role: 'currency_admin' },
        { link: '/customer', name: 'Clientes', role: 'customer_admin' },
        { link: '/crd', name: 'CRD\'s', role: 'crd_admin' },
        { link: '/city', name: 'Cidade', role: 'city_admin' },
      ]
    },
    {
      name: 'Eventos',
      icon: 'fa fa-calendar-check',
      isItem: false,
      collapseHeader: 'Cotações',
      collapsed: true,
      subMenu: [
        { link: '/event', name: 'Cadastro Inicial', role: 'event_admin' },
        { link: '/event-list', name: 'Listar', role: ['event_admin', 'hotel_operator', 'land_operator', 'air_operator'] }
      ],
    },
  ];

  hasPermission(role: string | string[]): boolean {
    const permissions = this.userPermissions();
    if (!permissions || permissions.length === 0) return false;
    const roles = Array.isArray(role) ? role : [role];
    return roles.some(r => permissions.some(p => p.name === r));
  }

  canShowMenu(menuItem: MenuItem): boolean {
    if (menuItem.isItem) return true;
    if (!menuItem.subMenu) return false;
    return menuItem.subMenu.some(s => this.hasPermission(s.role));
  }

  toggleCollapse(menuItem: MenuItem) {
    const isExpanding = menuItem.collapsed;
    
    if (isExpanding) {
      // Fecha todos os outros antes de abrir o atual
      this.menuItems.forEach(item => {
        if (!item.isItem) {
          item.collapsed = true;
        }
      });
    }
    
    menuItem.collapsed = !menuItem.collapsed;
  }

  isAngularRoute(link: string | undefined): boolean {
    if (!link) return false;
    const angularRoutes = [
      '/dashboard', '/apto', '/regime', '/category', '/purpose', 
      '/service-type', '/broker', '/role', '/hotel', '/service', 
      '/local', '/service-hall', '/purpose-hall', '/measure',
      '/frequency', '/service-add', '/provider-service', '/brand',
      '/car-model', '/vehicle', '/transport-service', '/broker-trans', '/provider-transport',
      '/event', '/event-list'
    ];
    return angularRoutes.includes(link);
  }
}
