import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { Observable } from 'rxjs';

import { EventService } from '../../../services/event.service';
import { AuthService } from '../../../services/auth.service';
import { CityService } from '../../../services/city.service';
import { ToastService } from '../../../services/toast.service';
import { AutocompleteComponent } from '../../../shared/components/autocomplete/autocomplete.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';

@Component({
  selector: 'app-event-create',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, AuthenticatedLayoutComponent, AutocompleteComponent, ConfirmModalComponent],
  templateUrl: './event-create.component.html',
  styleUrls: ['./event-create.component.scss'],
})
export class EventCreateComponent implements OnInit {
  private readonly eventService = inject(EventService);
  private readonly authService = inject(AuthService);
  private readonly cityService = inject(CityService);
  private readonly toastService = inject(ToastService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  isLoader = false;
  processing = false;
  errors: any = {};

  parseFloat(value: any): number {
    return parseFloat(value || 0);
  }

  // Active state
  eventId: number = 0;
  activeTab: number = 0; // 0 = Básico, 1 = Hotel, 2 = A&B, 3 = Salões, 4 = Adicionais, 5 = Transporte
  event: any = null;

  // Master dropdown lookup data
  customers: any[] = [];
  crds: any[] = [];
  crdsFiltered: any[] = [];
  users: any[] = [];
  providers: any[] = [];
  providersService: any[] = [];
  providersTransport: any[] = [];
  brokers: any[] = [];
  currencies: any[] = [];
  regimes: any[] = [];
  purposes: any[] = [];
  services: any[] = [];
  servicesType: any[] = [];
  locals: any[] = [];
  catsHotel: any[] = [];
  aptosHotel: any[] = [];
  brokersT: any[] = [];
  vehicles: any[] = [];
  models: any[] = [];
  servicesT: any[] = [];
  brands: any[] = [];
  servicesHall: any[] = [];
  purposesHall: any[] = [];
  servicesAdd: any[] = [];
  frequencies: any[] = [];
  measures: any[] = [];
  allStatus: any = {};

  // Form State - Tab 0: Básico
  basicForm = {
    id: 0,
    name: '',
    customer: '' as string | number,
    code: '',
    requester: '',
    sector: '',
    paxBase: '',
    cc: '',
    date: '',
    date_final: '',
    crd_id: '' as string | number,
    hotel_operator: '' as string | number,
    air_operator: '' as string | number,
    land_operator: '' as string | number,
    countries: [{ id: 0, pais: '', cidade: '', errors: '' }],
  };

  // Provider Lists per Category
  eventHotels: any[] = [];
  eventABs: any[] = [];
  eventHalls: any[] = [];
  eventAdds: any[] = [];
  eventTransports: any[] = [];

  // Tab View Options
  showDetailsHotel = false;
  showDetailsAB = false;
  showDetailsHall = false;
  showDetailsAdd = false;
  showDetailsTransport = false;

  // Form State - Provider Links (vincular)
  providerLinkForm: any = {
    id: 0,
    provider_id: '',
    currency_id: '',
    iss_percent: 0,
    service_percent: 0,
    iva_percent: 0,
    service_charge: 0,
    payment_method: '',
    internal_observation: '',
    customer_observation: '',
  };
  showProviderLinkForm = false;
  providerLinkType: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' = 'hotel';

  // Form State - Provider Options (detalhes/tarifas)
  optForm: any = {
    id: 0,
    parent_id: 0, // e.g. event_hotel_id, event_ab_id, etc.
    broker_id: '',
    regime_id: '',
    purpose_id: '',
    category_id: '',
    apto_id: '',
    service_id: '',
    service_type_id: '',
    local_id: '',
    frequency_id: '',
    measure_id: '',
    vehicle_id: '',
    car_model_id: '',
    brand_id: '',
    in: '',
    out: '',
    count: 1,
    kickback: 0,
    received_proposal: 0,
    received_proposal_percent: 100,
  };
  showOptForm = false;
  optFormType: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' = 'hotel';

  // Autocomplete Functions
  searchCities = (term: string) => this.cityService.searchCities(term);
  displayCity = (city: any) => (city ? `${city.name} - ${city.states ? city.states : city.country}` : '');

  ngOnInit() {
    this.route.params.subscribe((params) => {
      this.eventId = params['id'] ? parseInt(params['id']) : 0;
      this.activeTab = params['tab'] ? parseInt(params['tab']) : 0;
      this.loadInitialData();
    });
  }

  loadInitialData() {
    this.isLoader = true;
    this.eventService.getEditData(this.eventId).subscribe({
      next: (res) => {
        // Load master lists
        this.customers = res.customers || [];
        this.crds = res.crds || [];
        this.users = res.users || [];
        this.providers = res.providers || [];
        this.providersService = res.providersService || [];
        this.providersTransport = res.providersTransport || [];
        this.brokers = res.brokers || [];
        this.currencies = res.currencies || [];
        this.regimes = res.regimes || [];
        this.purposes = res.purposes || [];
        this.services = res.services || [];
        this.servicesType = res.servicesType || [];
        this.locals = res.locals || [];
        this.catsHotel = res.catsHotel || [];
        this.aptosHotel = res.aptosHotel || [];
        this.brokersT = res.brokersT || [];
        this.vehicles = res.vehicles || [];
        this.models = res.models || [];
        this.servicesT = res.servicesT || [];
        this.brands = res.brands || [];
        this.servicesHall = res.servicesHall || [];
        this.purposesHall = res.purposesHall || [];
        this.servicesAdd = res.servicesAdd || [];
        this.frequencies = res.frequencies || [];
        this.measures = res.measures || [];
        this.allStatus = res.allStatus || {};

        if (res.event) {
          this.event = res.event;
          this.basicForm.id = res.event.id;
          this.basicForm.name = res.event.name;
          this.basicForm.customer = res.event.customer_id || '';
          this.basicForm.code = res.event.code || '';
          this.basicForm.requester = res.event.requester || '';
          this.basicForm.sector = res.event.sector || '';
          this.basicForm.paxBase = res.event.pax_base || '';
          this.basicForm.cc = res.event.cost_center || '';

          if (res.event.date) {
            this.basicForm.date = res.event.date.split('T')[0];
          }
          if (res.event.date_final) {
            this.basicForm.date_final = res.event.date_final.split('T')[0];
          }

          this.basicForm.crd_id = res.event.crd_id || '';
          this.basicForm.hotel_operator = res.event.hotel_operator || '';
          this.basicForm.air_operator = res.event.air_operator || '';
          this.basicForm.land_operator = res.event.land_operator || '';

          if (res.event.event_locals && res.event.event_locals.length > 0) {
            this.basicForm.countries = res.event.event_locals.map((l: any) => ({
              id: l.id,
              pais: l.pais || '',
              cidade: l.cidade || '',
              errors: '',
            }));
          } else {
            this.basicForm.countries = [{ id: 0, pais: '', cidade: '', errors: '' }];
          }

          // Filter CRDs
          this.filterCrds(this.basicForm.customer);

          // Provider lists
          this.eventHotels = res.eventHotels || [];
          this.eventABs = res.eventABs || [];
          this.eventHalls = res.eventHalls || [];
          this.eventAdds = res.eventAdds || [];
          this.eventTransports = res.eventTransports || [];
        }

        this.isLoader = false;
      },
      error: (err) => {
        this.toastService.error('Erro ao carregar dados do evento');
        console.error(err);
        this.isLoader = false;
      },
    });
  }

  // --- TAB 0: BÁSICO ---
  onCustomerChange() {
    this.basicForm.crd_id = '';
    this.filterCrds(this.basicForm.customer);
  }

  filterCrds(customerId: any) {
    if (!customerId) {
      this.crdsFiltered = [];
      return;
    }
    this.crdsFiltered = this.crds.filter((c) => c.customer_id == customerId || !c.customer_id);
  }

  addCountry() {
    this.basicForm.countries.push({ id: 0, pais: '', cidade: '', errors: '' });
  }

  removeCountry(index: number) {
    if (this.basicForm.countries.length > 1) {
      this.basicForm.countries.splice(index, 1);
    }
  }

  saveBasic() {
    this.processing = true;
    this.errors = {};

    this.eventService.saveEvent(this.basicForm).subscribe({
      next: (res) => {
        this.processing = false;
        this.toastService.success(res.message || 'Dados básicos salvos com sucesso!');
        if (!this.eventId) {
          this.router.navigate(['/event', res.event.id]);
        } else {
          this.loadInitialData();
        }
      },
      error: (err: HttpErrorResponse) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
          this.toastService.error('Erro de validação, verifique os campos.');
        } else {
          this.toastService.error(err.error?.message || 'Erro ao salvar os dados básicos.');
        }
      },
    });
  }

  // --- PROVIDER LINK VINCULOS ---
  openAddProviderLink(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport', editItem: any = null) {
    this.providerLinkType = type;
    this.errors = {};

    if (editItem) {
      this.providerLinkForm = {
        id: editItem.id,
        provider_id: editItem.hotel_id || editItem.ab_id || editItem.hall_id || editItem.add_id || editItem.transport_id,
        currency_id: editItem.currency_id,
        iss_percent: editItem.iss_percent || 0,
        service_percent: editItem.service_percent || 0,
        iva_percent: editItem.iva_percent || 0,
        service_charge: editItem.service_charge || 0,
        payment_method: editItem.payment_method || '',
        internal_observation: editItem.internal_observation || '',
        customer_observation: editItem.customer_observation || '',
      };
    } else {
      this.providerLinkForm = {
        id: 0,
        provider_id: '',
        currency_id: this.currencies[0]?.id || '',
        iss_percent: 0,
        service_percent: 0,
        iva_percent: 0,
        service_charge: 0,
        payment_method: '',
        internal_observation: '',
        customer_observation: '',
      };
    }

    this.showProviderLinkForm = true;
  }

  closeProviderLinkForm() {
    this.showProviderLinkForm = false;
    this.errors = {};
  }

  saveProviderLink() {
    this.processing = true;
    this.errors = {};

    const payload = {
      ...this.providerLinkForm,
      event_id: this.eventId,
    };

    let obs: Observable<any>;
    switch (this.providerLinkType) {
      case 'hotel':
        obs = this.eventService.saveEventHotel(payload);
        break;
      case 'ab':
        obs = this.eventService.saveEventAB(payload);
        break;
      case 'hall':
        obs = this.eventService.saveEventHall(payload);
        break;
      case 'add':
        obs = this.eventService.saveEventAdd(payload);
        break;
      case 'transport':
        obs = this.eventService.saveEventTransport(payload);
        break;
    }

    obs.subscribe({
      next: (res) => {
        this.processing = false;
        this.toastService.success(res.message || 'Fornecedor vinculado com sucesso!');
        this.closeProviderLinkForm();
        this.loadInitialData();
      },
      error: (err: HttpErrorResponse) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastService.error(err.error?.message || 'Erro ao vincular fornecedor.');
        }
      },
    });
  }

  deleteProviderLink(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport', id: number) {
    this.isLoader = true;
    let obs: Observable<any>;
    switch (type) {
      case 'hotel':
        obs = this.eventService.deleteEventHotel(id);
        break;
      case 'ab':
        obs = this.eventService.deleteEventAB(id);
        break;
      case 'hall':
        obs = this.eventService.deleteEventHall(id);
        break;
      case 'add':
        obs = this.eventService.deleteEventAdd(id);
        break;
      case 'transport':
        obs = this.eventService.deleteEventTransport(id);
        break;
    }

    obs.subscribe({
      next: (res) => {
        this.toastService.success('Vínculo removido com sucesso!');
        this.loadInitialData();
      },
      error: (err) => {
        this.toastService.error('Erro ao remover vínculo.');
        console.error(err);
        this.isLoader = false;
      },
    });
  }

  // --- PROVIDER OPTIONS (DETALHES / TARIFAS) ---
  openAddOpt(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport', parentId: number, editItem: any = null, isDuplicate = false) {
    this.optFormType = type;
    this.errors = {};

    if (editItem) {
      this.optForm = {
        id: isDuplicate ? 0 : editItem.id,
        parent_id: parentId,
        broker_id: editItem.broker_id || '',
        regime_id: editItem.regime_id || '',
        purpose_id: editItem.purpose_id || '',
        category_id: editItem.category_id || '',
        apto_id: editItem.apto_id || '',
        service_id: editItem.service_id || '',
        service_type_id: editItem.service_type_id || '',
        local_id: editItem.local_id || '',
        frequency_id: editItem.frequency_id || '',
        measure_id: editItem.measure_id || '',
        vehicle_id: editItem.vehicle_id || '',
        car_model_id: editItem.car_model_id || '',
        brand_id: editItem.brand_id || '',
        in: editItem.in ? editItem.in.split('T')[0] : '',
        out: editItem.out ? editItem.out.split('T')[0] : '',
        count: editItem.count || 1,
        kickback: editItem.kickback || 0,
        received_proposal: editItem.received_proposal || 0,
        received_proposal_percent: editItem.received_proposal_percent || 100,
      };
    } else {
      this.optForm = {
        id: 0,
        parent_id: parentId,
        broker_id: this.brokers[0]?.id || '',
        regime_id: this.regimes[0]?.id || '',
        purpose_id: this.purposes[0]?.id || '',
        category_id: this.catsHotel[0]?.id || '',
        apto_id: this.aptosHotel[0]?.id || '',
        service_id: '',
        service_type_id: '',
        local_id: '',
        frequency_id: '',
        measure_id: '',
        vehicle_id: '',
        car_model_id: '',
        brand_id: '',
        in: this.basicForm.date || '',
        out: this.basicForm.date_final || '',
        count: 1,
        kickback: 0,
        received_proposal: 0,
        received_proposal_percent: 100,
      };

      // Set first values based on type defaults
      if (type === 'ab') {
        this.optForm.service_id = this.services[0]?.id || '';
        this.optForm.service_type_id = this.servicesType[0]?.id || '';
        this.optForm.local_id = this.locals[0]?.id || '';
      } else if (type === 'hall') {
        this.optForm.service_id = this.servicesHall[0]?.id || '';
        this.optForm.purpose_id = this.purposesHall[0]?.id || '';
      } else if (type === 'add') {
        this.optForm.service_id = this.servicesAdd[0]?.id || '';
        this.optForm.frequency_id = this.frequencies[0]?.id || '';
        this.optForm.measure_id = this.measures[0]?.id || '';
      } else if (type === 'transport') {
        this.optForm.service_id = this.servicesT[0]?.id || '';
        this.optForm.vehicle_id = this.vehicles[0]?.id || '';
        this.optForm.car_model_id = this.models[0]?.id || '';
        this.optForm.brand_id = this.brands[0]?.id || '';
        this.optForm.broker_id = this.brokersT[0]?.id || '';
      }
    }

    this.showOptForm = true;
  }

  closeOptForm() {
    this.showOptForm = false;
    this.errors = {};
  }

  saveOpt() {
    this.processing = true;
    this.errors = {};

    let payload: any = { ...this.optForm };
    let obs: Observable<any>;

    switch (this.optFormType) {
      case 'hotel':
        payload.event_hotel_id = this.optForm.parent_id;
        obs = this.eventService.saveHotelOpt(payload);
        break;
      case 'ab':
        payload.event_ab_id = this.optForm.parent_id;
        obs = this.eventService.saveABOpt(payload);
        break;
      case 'hall':
        payload.event_hall_id = this.optForm.parent_id;
        obs = this.eventService.saveHallOpt(payload);
        break;
      case 'add':
        payload.event_add_id = this.optForm.parent_id;
        obs = this.eventService.saveAddOpt(payload);
        break;
      case 'transport':
        payload.event_transport_id = this.optForm.parent_id;
        obs = this.eventService.saveTransportOpt(payload);
        break;
    }

    obs.subscribe({
      next: (res) => {
        this.processing = false;
        this.toastService.success(res.message || 'Tarifa/Opção salva com sucesso!');
        this.closeOptForm();
        this.loadInitialData();
      },
      error: (err: HttpErrorResponse) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastService.error(err.error?.message || 'Erro ao salvar tarifa.');
        }
      },
    });
  }

  deleteOpt(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport', id: number) {
    this.isLoader = true;
    let obs: Observable<any>;
    switch (type) {
      case 'hotel':
        obs = this.eventService.deleteHotelOpt(id);
        break;
      case 'ab':
        obs = this.eventService.deleteABOpt(id);
        break;
      case 'hall':
        obs = this.eventService.deleteHallOpt(id);
        break;
      case 'add':
        obs = this.eventService.deleteAddOpt(id);
        break;
      case 'transport':
        obs = this.eventService.deleteTransportOpt(id);
        break;
    }

    obs.subscribe({
      next: (res) => {
        this.toastService.success('Opção removida com sucesso!');
        this.loadInitialData();
      },
      error: (err) => {
        this.toastService.error('Erro ao remover opção.');
        console.error(err);
        this.isLoader = false;
      },
    });
  }

  // --- CALCULATION FORMULAS (PRESERVED 100%) ---
  daysBetween(date1: string, date2: string, includeLastDay: boolean = false): number {
    if (!date1 || !date2) return 0;

    const d1 = new Date(date1);
    const d2 = new Date(date2);
    d1.setHours(0, 0, 0, 0);
    d2.setHours(0, 0, 0, 0);

    const difference = Math.abs(d1.getTime() - d2.getTime());
    const days = Math.ceil(difference / (1000 * 60 * 60 * 24));

    return includeLastDay ? days + 1 : days;
  }

  unitCost(opt: any): number {
    return parseFloat(opt.received_proposal || 0);
  }

  unitSale(opt: any): number {
    const cost = this.unitCost(opt);
    const percent = parseFloat(opt.received_proposal_percent || 0);
    if (percent > 0) {
      return Math.ceil(cost / percent);
    }
    return cost;
  }

  roomNights(provider: any, isAB = false): number {
    let sum = 0;
    const opts =
      provider.event_hotels_opt ||
      provider.event_ab_opts ||
      provider.event_hall_opts ||
      provider.event_add_opts ||
      provider.event_transport_opts ||
      [];
    for (const opt of opts) {
      sum += parseFloat(opt.count || 0) * this.daysBetween(opt.in, opt.out, isAB);
    }
    return sum;
  }

  average(provider: any, isAB = false): number {
    const opts =
      provider.event_hotels_opt ||
      provider.event_ab_opts ||
      provider.event_hall_opts ||
      provider.event_add_opts ||
      provider.event_transport_opts ||
      [];
    if (opts.length === 0) return 0;

    let sum = 0;
    for (const opt of opts) {
      sum += this.unitSale(opt);
    }
    return sum / opts.length;
  }

  sumCount(provider: any): number {
    let sum = 0;
    const opts =
      provider.event_hotels_opt ||
      provider.event_ab_opts ||
      provider.event_hall_opts ||
      provider.event_add_opts ||
      provider.event_transport_opts ||
      [];
    for (const opt of opts) {
      sum += parseFloat(opt.count || 0);
    }
    return sum;
  }

  sumNts(provider: any, isAB = false): number {
    let sum = 0;
    const opts =
      provider.event_hotels_opt ||
      provider.event_ab_opts ||
      provider.event_hall_opts ||
      provider.event_add_opts ||
      provider.event_transport_opts ||
      [];
    for (const opt of opts) {
      sum += this.daysBetween(opt.in, opt.out, isAB);
    }
    return sum;
  }

  sumSale(provider: any, isAB = false): number {
    let sum = 0;
    const opts =
      provider.event_hotels_opt ||
      provider.event_ab_opts ||
      provider.event_hall_opts ||
      provider.event_add_opts ||
      provider.event_transport_opts ||
      [];
    for (const opt of opts) {
      sum += this.unitSale(opt) * this.daysBetween(opt.in, opt.out, isAB) * parseFloat(opt.count || 0);
    }
    return sum;
  }

  sumCost(provider: any, isAB = false): number {
    let sum = 0;
    const opts =
      provider.event_hotels_opt ||
      provider.event_ab_opts ||
      provider.event_hall_opts ||
      provider.event_add_opts ||
      provider.event_transport_opts ||
      [];
    for (const opt of opts) {
      sum += this.unitCost(opt) * this.daysBetween(opt.in, opt.out, isAB) * parseFloat(opt.count || 0);
    }
    return sum;
  }

  sumTaxes(provider: any, taxType: 'iss' | 'serv' | 'iva' | 'sc', isAB = false): number {
    let sum = 0;
    const opts =
      provider.event_hotels_opt ||
      provider.event_ab_opts ||
      provider.event_hall_opts ||
      provider.event_add_opts ||
      provider.event_transport_opts ||
      [];

    for (const opt of opts) {
      const days = this.daysBetween(opt.in, opt.out, isAB);
      const count = parseFloat(opt.count || 0);
      const sale = this.unitSale(opt);

      switch (taxType) {
        case 'iss':
          sum += ((sale * parseFloat(provider.iss_percent || 0)) / 100) * days * count;
          break;
        case 'serv':
          sum += ((sale * parseFloat(provider.service_percent || 0)) / 100) * days * count;
          break;
        case 'iva':
          sum += ((sale * parseFloat(provider.iva_percent || 0)) / 100) * days * count;
          break;
        case 'sc':
          sum += parseFloat(provider.service_charge || 0) * days * count;
          break;
      }
    }
    return sum;
  }

  // --- VIEW DETAILS BUTTON MECHANICS ---
  toggleDetails(tabName: 'hotel' | 'ab' | 'hall' | 'add' | 'transport') {
    switch (tabName) {
      case 'hotel':
        this.showDetailsHotel = !this.showDetailsHotel;
        break;
      case 'ab':
        this.showDetailsAB = !this.showDetailsAB;
        break;
      case 'hall':
        this.showDetailsHall = !this.showDetailsHall;
        break;
      case 'add':
        this.showDetailsAdd = !this.showDetailsAdd;
        break;
      case 'transport':
        this.showDetailsTransport = !this.showDetailsTransport;
        break;
    }
  }

  formatCurrency(value: number, sigla: string = 'BRL'): string {
    const rounded = Math.round(value * 100) / 100;
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: sigla,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(rounded);
  }

  statusBlockEdit(provider: any): boolean {
    if (provider && provider.status_history && provider.status_history.length > 0) {
      const sorted = [...provider.status_history].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
      const status = sorted[0].status;
      return status === 'dating_with_customer' || status === 'Cancelled';
    }
    return false;
  }

  hasPermission(role: string): boolean {
    return this.authService.user()?.permissions?.some((p: any) => p.name === role) || false;
  }
}
