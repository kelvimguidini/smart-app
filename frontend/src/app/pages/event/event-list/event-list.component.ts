import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { EventService } from '../../../services/event.service';
import { AuthService } from '../../../services/auth.service';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ProviderActionsComponent } from './provider-actions/provider-actions.component';
import flatpickr from 'flatpickr';

@Component({
  selector: 'app-event-list',
  standalone: true,
  imports: [
    CommonModule, 
    FormsModule, 
    RouterLink, 
    AuthenticatedLayoutComponent, 
    DatatableComponent, 
    ConfirmModalComponent, 
    ModalComponent,
    ProviderActionsComponent
  ],
  templateUrl: './event-list.component.html',
  styleUrls: ['./event-list.component.scss'],
})
export class EventListComponent implements OnInit, AfterViewInit {
  private readonly eventService = inject(EventService);
  private readonly authService = inject(AuthService);

  @ViewChild('dateRangePicker') dateRangePickerElement!: ElementRef;
  private flatpickrInstance: any;

  events: any[] = [];
  pagination: any = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  };
  isLoader = false;

  // Sorting state
  sortKey = 'created_at';
  sortDir: 'asc' | 'desc' = 'desc';

  // Modal states
  showViewModal = false;
  showSaleModal = false;
  showHistoryModal = false;

  selectedEvent: any = null;
  faturamentoValue = 0;

  historyList: any[] = [];
  loadingHistory = false;
  restoringHistory = false;

  historyPagination = {
    page: 1,
    perPage: 50,
  };

  tableLabels: { [key: string]: string } = {
    event: 'Base do Evento',
    event_hotel: 'Hotel',
    event_hotel_opt: 'Hotel - Detalhes',
    event_ab: 'A&B',
    event_ab_opt: 'A&B - Detalhes',
    event_add: 'Adicional',
    event_add_opt: 'Adicional - Detalhes',
    event_hall: 'Salão',
    event_hall_opt: 'Salão - Detalhes',
    event_transport: 'Transporte',
    event_transport_opt: 'Transporte - Detalhes',
  };

  fieldLabels: { [key: string]: string } = {
    name: 'Nome do Evento',
    code: 'Código do Zendesk',
    requester: 'Solicitante',
    customer_id: 'Empresa',
    sector: 'Setor',
    pax_base: 'Base de Pax',
    cost_center: 'Centro de Custo',
    date: 'Data do Evento',
    date_final: 'Data do Evento Fim',
    crd_id: 'CRD',
    hotel_operator: 'Operador - Hotel',
    air_operator: 'Operador - Aéreo',
    land_operator: 'Operador - Terrestre',
    provider_id: 'Fornecedor',
    city: 'Cidade',
    currency: 'Moeda',
    invoice: 'Nota Fiscal',
    iss_percent: 'ISS (%)',
    service_percent: 'Serviço (%)',
    iva_percent: 'IVA (%)',
    service_charge: 'Taxa de Turismo',
    iof: 'IOF (%)',
    deadline: 'Prazo',
    deadline_date: 'Prazo',
    internal_observation: 'Observação Interna',
    customer_observation: 'Observação Cliente',
    taxa_4bts: 'Taxa 4BTS (%)',
    type: 'Tipo',
    currency_id: 'Moeda',
    broker_id: 'Broker',
    in: 'IN',
    out: 'OUT',
    count: 'QTD',
    kickback: 'Comissão (%)',
    received_proposal: 'Proposta Recebida',
    received_proposal_percent: 'Markup (%)',
    order: 'Ordem',
    service_id: 'Serviço',
    hotel_id: 'Hotel',
    hotel: 'Hotel',
    apto_hotel_id: 'Apartamento',
    category_hotel_id: 'Categoria',
    regime_id: 'Regime',
    purpose_id: 'Proposito',
    compare_trivago: 'Comparação Trivago',
    compare_website_htl: 'Comparação Website Hotel',
    compare_omnibess: 'Comparação Omnibees',
    ab_id: 'A&B',
    ab: 'A&B',
    service_type_id: 'Tipo de Serviço',
    local_id: 'Local',
    add_id: 'Adicional',
    add: 'Adicional',
    measure_id: 'Unidade de Medida',
    frequency_id: 'Frequência',
    unit: 'Unidade',
    hall_id: 'Salão',
    hall: 'Salão',
    transport_id: 'Transporte',
    transport: 'Transporte',
    vehicle_id: 'Veículo',
    model_id: 'Modelo',
    brand_id: 'Marca',
    observation: 'Observação',
    value: 'Valor',
    qtd: 'Quantidade',
    qtd_dayles: 'Qtd Diárias',
    start_date: 'Data Início',
    end_date: 'Data Fim',
    checkin: 'Check-in',
    checkout: 'Check-out',
  };

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

  ngAfterViewInit() {
    this.flatpickrInstance = flatpickr(this.dateRangePickerElement.nativeElement, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      locale: {
        firstDayOfWeek: 1,
        weekdays: {
          shorthand: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
          longhand: [
            'Domingo',
            'Segunda-feira',
            'Terça-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado',
          ],
        },
        months: {
          shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
          longhand: [
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro',
          ],
        },
        rangeSeparator: ' até ',
      },
      onChange: (selectedDates) => {
        if (selectedDates.length === 2) {
          const formatDate = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
          };
          this.filters.startDate = formatDate(selectedDates[0]);
          this.filters.endDate = formatDate(selectedDates[1]);
        } else if (selectedDates.length === 0) {
          this.filters.startDate = '';
          this.filters.endDate = '';
        }
      },
    });
  }

  clearDateRange() {
    this.filters.startDate = '';
    this.filters.endDate = '';
    if (this.flatpickrInstance) {
      this.flatpickrInstance.clear();
    }
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
      per_page: this.pagination.per_page || 10,
      order_by: this.sortKey,
      order_dir: this.sortDir,
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
    this.pagination.per_page = perPage;
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
          sended_mail_link: current.sended_mail_link,
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
          sended_mail_link: current.sended_mail_link,
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
          sended_mail_link: current.sended_mail_link,
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
          sended_mail_link: current.sended_mail_link,
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
          sended_mail_link: current.sended_mail_link,
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

  // --- SORTING ---
  toggleSort(key: string) {
    if (this.sortKey === key) {
      this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortKey = key;
      this.sortDir = 'asc';
    }
    this.loadEvents(1);
  }

  getSortIcon(key: string): string {
    if (this.sortKey !== key) {
      return 'fa-sort text-muted opacity-50';
    }
    return this.sortDir === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary';
  }

  // --- MODALS AND ACTIONS ---
  openViewModal(event: any) {
    this.selectedEvent = event;
    this.showViewModal = true;
  }

  closeViewModal() {
    this.showViewModal = false;
    this.selectedEvent = null;
  }

  openSaleModal(event: any) {
    this.selectedEvent = event;
    this.faturamentoValue = event.valor_faturamento || 0;
    this.showSaleModal = true;
  }

  closeSaleModal() {
    this.showSaleModal = false;
    this.selectedEvent = null;
  }

  saveFaturamento() {
    if (!this.selectedEvent) return;
    this.isLoader = true;
    this.eventService.saveValorFaturamento(this.selectedEvent.id, this.faturamentoValue).subscribe({
      next: (res) => {
        this.closeSaleModal();
        this.loadEvents(this.pagination.current_page);
      },
      error: (err) => {
        console.error('Erro ao salvar faturamento:', err);
        this.isLoader = false;
      }
    });
  }

  openHistoryModal(event: any) {
    this.selectedEvent = event;
    this.showHistoryModal = true;
    this.loadHistory();
  }

  closeHistoryModal() {
    this.showHistoryModal = false;
    this.selectedEvent = null;
    this.historyList = [];
    this.historyPagination.page = 1;
  }

  loadHistory() {
    if (!this.selectedEvent) return;
    this.loadingHistory = true;
    this.historyPagination.page = 1;
    this.eventService.getHistory(this.selectedEvent.id).subscribe({
      next: (res) => {
        this.historyList = (res || []).map((h: any) => ({ ...h, showDetails: false, diffsCalculated: false, diffs: [] }));
        this.loadingHistory = false;
      },
      error: (err) => {
        console.error('Erro ao carregar histórico:', err);
        this.loadingHistory = false;
      }
    });
  }

  get displayedHistoryList() {
    const start = (this.historyPagination.page - 1) * this.historyPagination.perPage;
    return this.historyList.slice(start, start + this.historyPagination.perPage);
  }

  get totalHistoryPages() {
    return Math.ceil(this.historyList.length / this.historyPagination.perPage);
  }

  nextHistoryPage() {
    if (this.historyPagination.page < this.totalHistoryPages) {
      this.historyPagination.page++;
    }
  }

  prevHistoryPage() {
    if (this.historyPagination.page > 1) {
      this.historyPagination.page--;
    }
  }

  mathMin(a: number, b: number): number {
    return Math.min(a, b);
  }

  toggleHistoryDetails(item: any) {
    item.showDetails = !item.showDetails;
    if (item.showDetails && !item.diffsCalculated) {
      item.diffs = this.diffFields(item);
      item.diffsCalculated = true;
    }
  }

  restoreHistoryItem(item: any) {
    this.restoringHistory = true;
    this.eventService.restoreHistory(item.table_name, item.record_id, item.id).subscribe({
      next: (res) => {
        this.loadHistory();
        this.restoringHistory = false;
      },
      error: (err) => {
        console.error('Erro ao restaurar histórico:', err);
        this.restoringHistory = false;
      }
    });
  }

  formatDate(dateStr: string, showTime: boolean = false): string {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const datePart = d.toLocaleDateString('pt-BR');
    if (!showTime) return datePart;
    const timePart = d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    return `${datePart} ${timePart}`;
  }

  diffFields(item: any): any[] {
    let oldObj: any = {};
    let newObj: any = {};
    try {
      oldObj = item.old_data ? JSON.parse(item.old_data) : {};
    } catch (e) {
      oldObj = {};
    }
    try {
      newObj = item.new_data ? JSON.parse(item.new_data) : {};
    } catch (e) {
      newObj = {};
    }
    const diffs: any[] = [];
    const ignoreFields = ['updated_at', 'created_at', 'deleted_at'];
    const dateFields = ['deadline_date', 'date', 'date_final', 'checkin', 'checkout', 'created_at', 'updated_at', 'in', 'out'];
    const booleanFields = ['invoice'];

    for (const key in newObj) {
      if (ignoreFields.includes(key)) continue;
      if (oldObj[key] !== newObj[key]) {
        let oldVal = oldObj[key] ?? '';
        let newVal = newObj[key] ?? '';

        if (dateFields.includes(key)) {
          oldVal = this.formatDate(oldVal);
          newVal = this.formatDate(newVal);
        }
        if (booleanFields.includes(key)) {
          oldVal = (oldVal === true || oldVal === 'true' || oldVal === 1 || oldVal === '1') ? 'Sim' : 'Não';
          newVal = (newVal === true || newVal === 'true' || newVal === 1 || newVal === '1') ? 'Sim' : 'Não';
        }
        let fieldName = key;
        if (item.table_name === 'event_hall_opt' && key === 'name') {
          fieldName = 'Descrição';
        }
        diffs.push({
          field: this.fieldLabels[fieldName] || fieldName,
          old: oldVal,
          new: newVal
        });
      }
    }
    return diffs;
  }

  badgeClass(action: string): string {
    switch (action) {
      case 'created':
      case 'create':
      case 'inserted':
        return 'badge bg-success';
      case 'updated':
      case 'update':
        return 'badge bg-warning text-dark';
      case 'deleted':
      case 'delete':
        return 'badge bg-danger';
      default:
        return 'badge bg-secondary';
    }
  }

  actionLabel(action: string): string {
    switch (action) {
      case 'created':
      case 'create':
      case 'inserted':
        return 'Criado';
      case 'updated':
      case 'update':
        return 'Editado';
      case 'deleted':
      case 'delete':
        return 'Excluído';
      default:
        return action;
    }
  }
}
