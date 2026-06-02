import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { EventService } from '../../../services/event.service';
import { AuthService } from '../../../services/auth.service';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';

@Component({
  selector: 'app-event-list',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, AuthenticatedLayoutComponent, DatatableComponent, ConfirmModalComponent],
  templateUrl: './event-list.component.html',
  styleUrls: ['./event-list.component.scss'],
})
export class EventListComponent implements OnInit {
  private readonly eventService = inject(EventService);
  private readonly authService = inject(AuthService);

  events: any[] = [];
  pagination: any = {};
  isLoader = false;

  // Filter form
  filters = {
    startDate: '',
    endDate: '',
    city: '',
    consultant: '',
    client: '',
    status: '',
    eventCode: '',
    valorFaturamento: '',
  };

  // Dropdown list arrays
  customers: any[] = [];
  users: any[] = [];
  allStatus: any = {};

  // Expanded rows state
  expandedEventId: number | null = null;
  expandedProviders: any[] = [];

  ngOnInit() {
    this.loadInitialLookups();
    this.loadEvents();
  }

  loadInitialLookups() {
    // Load all necessary lookups by calling edit-data with ID 0
    this.eventService.getEditData(0).subscribe({
      next: (res) => {
        this.customers = res.customers || [];
        this.users = res.users || [];
        this.allStatus = res.allStatus || {};
      },
      error: (err) => console.error('Erro ao buscar dados auxiliares', err),
    });
  }

  loadEvents(page: number = 1) {
    this.isLoader = true;
    const params = {
      page,
      per_page: 10,
      startDate: this.filters.startDate,
      endDate: this.filters.endDate,
      city: this.filters.city,
      consultant: this.filters.consultant,
      client: this.filters.client,
      status: this.filters.status,
      eventCode: this.filters.eventCode,
      valorFaturamento: this.filters.valorFaturamento,
    };

    this.eventService.getEvents(params).subscribe({
      next: (res) => {
        this.events = res.data || [];
        this.pagination = {
          current_page: res.current_page,
          last_page: res.last_page,
          per_page: res.per_page,
          total: res.total,
          from: res.from,
          to: res.to,
        };
        this.isLoader = false;
      },
      error: (err) => {
        console.error('Erro ao buscar eventos', err);
        this.isLoader = false;
      },
    });
  }

  onSearch() {
    this.loadEvents(1);
  }

  onPageChange(page: number) {
    this.loadEvents(page);
  }

  onPerPageChange(perPage: number) {
    // Standard page reload
    this.loadEvents(1);
  }

  // Row collapse mechanism
  toggleEventDetails(event: any) {
    if (this.expandedEventId === event.id) {
      this.expandedEventId = null;
      this.expandedProviders = [];
    } else {
      this.expandedEventId = event.id;
      this.expandedProviders = this.getProvidersByEvent(event);
    }
  }

  getProvidersByEvent(event: any): any[] {
    const groups: any[] = [];

    event.event_hotels?.forEach((current: any) => {
      if (!groups.some((g) => g.type === 'Hotel' && g.id === current.hotel?.id)) {
        groups.push({
          id: current.hotel?.id,
          name: current.hotel?.name,
          city: current.hotel?.city,
          email: current.hotel?.email,
          sended_mail: current.sended_mail,
          token_budget: current.token_budget,
          providerBudget: current.provider_budget,
          type: 'Hotel',
          table: 'event_hotels',
          table_id: current.id,
          status: current.status_his?.[0]?.status,
          order: current.order || 0,
        });
      }
    });

    event.event_abs?.forEach((current: any) => {
      if (!groups.some((g) => g.id === current.ab?.id)) {
        groups.push({
          id: current.ab?.id,
          name: current.ab?.name,
          city: current.ab?.city,
          email: current.ab?.email,
          sended_mail: current.sended_mail,
          token_budget: current.token_budget,
          providerBudget: current.provider_budget,
          type: 'A&B',
          table: 'event_abs',
          table_id: current.id,
          status: current.status_his?.[0]?.status,
          order: current.order || 0,
        });
      }
    });

    event.event_halls?.forEach((current: any) => {
      if (!groups.some((g) => g.id === current.hall?.id)) {
        groups.push({
          id: current.hall?.id,
          name: current.hall?.name,
          city: current.hall?.city,
          email: current.hall?.email,
          sended_mail: current.sended_mail,
          token_budget: current.token_budget,
          providerBudget: current.provider_budget,
          type: 'Salão',
          table: 'event_halls',
          table_id: current.id,
          status: current.status_his?.[0]?.status,
          order: current.order || 0,
        });
      }
    });

    event.event_adds?.forEach((current: any) => {
      if (!groups.some((g) => g.id === current.add?.id)) {
        groups.push({
          id: current.add?.id,
          name: current.add?.name,
          city: current.add?.city,
          email: current.add?.email,
          sended_mail: current.sended_mail,
          token_budget: current.token_budget,
          providerBudget: current.provider_budget,
          type: 'Serviço Adicional',
          table: 'event_adds',
          table_id: current.id,
          status: current.status_his?.[0]?.status,
          order: current.order || 0,
        });
      }
    });

    event.event_transports?.forEach((current: any) => {
      if (!groups.some((g) => g.isTransport && g.id === current.transport?.id)) {
        groups.push({
          id: current.transport?.id,
          name: current.transport?.name,
          city: current.transport?.city,
          email: current.transport?.email,
          type: 'Transporte Terrestre',
          sended_mail: current.sended_mail,
          token_budget: current.token_budget,
          providerBudget: current.provider_budget,
          isTransport: true,
          table: 'event_transports',
          table_id: current.id,
          status: current.status_his?.[0]?.status,
          order: current.order || 0,
        });
      }
    });

    return groups.sort((a, b) => a.order - b.order);
  }

  get allStatusKeys(): string[] {
    return Object.keys(this.allStatus);
  }

  getStatusLabel(status: string): string {
    return this.allStatus[status]?.label || 'Solicitado';
  }

  deleteEvent(id: number) {
    this.isLoader = true;
    this.eventService.deleteEvent(id).subscribe({
      next: (res) => {
        this.loadEvents(this.pagination.current_page);
      },
      error: (err) => {
        console.error('Erro ao excluir evento', err);
        this.isLoader = false;
      },
    });
  }

  hasPermission(role: string): boolean {
    return this.authService.user()?.permissions?.some((p: any) => p.name === role) || false;
  }
}
