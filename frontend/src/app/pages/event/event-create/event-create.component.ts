import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { Observable, of } from 'rxjs';

import { EventService } from '../../../services/event.service';
import { AuthService } from '../../../services/auth.service';
import { CityService } from '../../../services/city.service';
import { ToastService } from '../../../services/toast.service';
import { AutocompleteComponent } from '../../../shared/components/autocomplete/autocomplete.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import flatpickr from 'flatpickr';

@Component({
  selector: 'app-event-create',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, AuthenticatedLayoutComponent, AutocompleteComponent, ConfirmModalComponent, ModalComponent],
  templateUrl: './event-create.component.html',
  styleUrls: ['./event-create.component.scss'],
})
export class EventCreateComponent implements OnInit, AfterViewInit {
  private readonly eventService = inject(EventService);
  private readonly authService = inject(AuthService);
  private readonly cityService = inject(CityService);
  private readonly toastService = inject(ToastService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  @ViewChild('dateRangePicker') dateRangePickerElement!: ElementRef;
  private flatpickrInstance: any;

  isLoader = false;
  processing = false;
  errors: any = {};

  parseFloat(value: any): number {
    return parseFloat(value || 0);
  }

  // Active state
  eventId: number = 0;
  activeTab: number = 0; // 0 = Básico, 1 = Hotel, 2 = A&B, 3 = Salões, 4 = Adicionais, 5 = Transporte, 6 = Aéreo
  event: any = null;

  // Master dropdown lookup data
  customers: any[] = [];
  crds: any[] = [];
  crdsFiltered: any[] = [];
  users: any[] = [];
  providers: any[] = [];
  providersService: any[] = [];
  providersTransport: any[] = [];
  providersAirfare: any[] = [];
  airlines: any[] = [];
  baggages: any[] = [];
  cabins: any[] = [];
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
  customerRequesters: any[] = [];
  customerSectors: any[] = [];
  customerCostCenters: any[] = [];
  requestersFiltered: any[] = [];
  sectorsFiltered: any[] = [];
  costCentersFiltered: any[] = [];

  // Form State - Tab 0: Básico
  basicForm = {
    id: 0,
    name: '',
    customer: '' as string | number,
    code: '',
    requester: '',
    sector: '',
    paxBase: '',
    cc: '' as string | number,
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
  eventAirfares: any[] = [];

  // Tab View Options
  showDetailsHotel = false;
  showDetailsAB = false;
  showDetailsHall = false;
  showDetailsAdd = false;
  showDetailsTransport = false;
  showDetailsAirfare = false;

  // Form State - Provider Links (vincular)
  providerLinkForm: any = {
    id: 0,
    provider_id: '',
    currency_id: '',
    iss_percent: 0,
    service_percent: 0,
    iva_percent: 0,
    taxa_4bts: 0,
    service_charge: 0,
    payment_method: '',
    internal_observation: '',
    customer_observation: '',
  };
  showProviderLinkForm = false;
  providerLinkType: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' | 'airfare' = 'hotel';
  selectedProviderName = '';

  searchProviders = (term: string): Observable<any[]> => {
    const termLower = term.toLowerCase();
    let sourceList: any[] = [];
    if (this.providerLinkType === 'hotel' || this.providerLinkType === 'ab') {
      sourceList = this.providers;
    } else if (this.providerLinkType === 'add') {
      sourceList = this.providersService;
    } else if (this.providerLinkType === 'transport') {
      sourceList = this.providersTransport;
    } else if (this.providerLinkType === 'airfare') {
      sourceList = this.providersAirfare;
    }
    const filtered = sourceList.filter(p => 
      (p.name && p.name.toLowerCase().includes(termLower)) || 
      (p.city && (
        (p.city.name && p.city.name.toLowerCase().includes(termLower)) ||
        (p.city.states && p.city.states.toLowerCase().includes(termLower)) ||
        (p.city.country && p.city.country.toLowerCase().includes(termLower))
      ))
    );
    return of(filtered);
  }

  displayProvider = (provider: any): string => {
    if (!provider) return '';
    let locationStr = 'S/ Cidade';
    if (provider.city) {
      const stateOrCountry = provider.city.states || provider.city.country;
      locationStr = provider.city.name + (stateOrCountry ? ` - ${stateOrCountry}` : '');
    }
    return `${provider.name} (${locationStr})`;
  }

  onProviderChange(providerId: any) {
    if (!providerId) return;
    let found: any = null;
    if (this.providerLinkType === 'hotel' || this.providerLinkType === 'ab') {
      found = this.providers.find(p => p.id === providerId);
    } else if (this.providerLinkType === 'add') {
      found = this.providersService.find(p => p.id === providerId);
    } else if (this.providerLinkType === 'transport') {
      found = this.providersTransport.find(p => p.id === providerId);
    } else if (this.providerLinkType === 'airfare') {
      found = this.providersAirfare.find(p => p.id === providerId);
    }
    
    if (found) {
      this.providerLinkForm.taxa_4bts = found.taxa_4bts || 0;
      this.providerLinkForm.iss_percent = found.iss_percent || 0;
      this.providerLinkForm.service_percent = found.service_percent || 0;
      this.providerLinkForm.iva_percent = found.iva_percent || 0;
      this.providerLinkForm.payment_method = this.normalizePaymentMethod(found.payment_method);
    }
  }

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

    // Airfare-specific fields
    outbound_airline_id: '',
    outbound_flight_number: '',
    outbound_class: '',
    outbound_date: '',
    outbound_origin: '',
    outbound_destination: '',
    outbound_departure_time: '',
    outbound_arrival_time: '',
    outbound_connection_details: '',
    inbound_airline_id: '',
    inbound_flight_number: '',
    inbound_class: '',
    inbound_date: '',
    inbound_origin: '',
    inbound_destination: '',
    inbound_departure_time: '',
    inbound_arrival_time: '',
    inbound_connection_details: '',
    baggage_id: '',
    cabin_id: '',
    observation: '',
    status: '',
  };
  showOptForm = false;
  optFormType: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' | 'airfare' = 'hotel';

  // Autocomplete Functions
  searchCities = (term: string) => this.cityService.searchCities(term);
  displayCity = (city: any) => (city ? `${city.name} - ${city.states ? city.states : city.country}` : '');

  isReadOnly = false;

  ngOnInit() {
    this.route.params.subscribe((params) => {
      this.eventId = params['id'] ? parseInt(params['id']) : 0;
      this.activeTab = params['tab'] ? parseInt(params['tab']) : 0;
      this.loadInitialData();
    });

    this.route.queryParams.subscribe((queryParams) => {
      this.isReadOnly = queryParams['view'] === 'true' || queryParams['view'] === true;
    });
  }

  ngAfterViewInit() {
    this.flatpickrInstance = flatpickr(this.dateRangePickerElement.nativeElement, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      defaultDate: this.basicForm.date && this.basicForm.date_final ? [this.basicForm.date, this.basicForm.date_final] : undefined,
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
          this.basicForm.date = formatDate(selectedDates[0]);
          this.basicForm.date_final = formatDate(selectedDates[1]);
        } else {
          this.basicForm.date = '';
          this.basicForm.date_final = '';
        }
      },
    });
  }

  loadInitialData() {
    this.isLoader = true;
    this.eventService.getEditData(this.eventId).subscribe({
      next: (res) => {
        console.log('loadInitialData: edit-data API response =', res);
        // Load master lists
        this.customers = res.customers || [];
        this.crds = res.crds || [];
        this.users = res.users || [];
        this.providers = res.providers || [];
        this.providersService = res.providersService || [];
        this.providersTransport = res.providersTransport || [];
        this.providersAirfare = res.providersAirfare || [];
        this.airlines = res.airlines || [];
        this.baggages = res.baggages || [];
        this.cabins = res.cabins || [];
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
        this.customerRequesters = res.customerRequesters || [];
        this.customerSectors = res.customerSectors || [];
        this.customerCostCenters = res.customerCostCenters || [];

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
          if (this.flatpickrInstance && this.basicForm.date && this.basicForm.date_final) {
            this.flatpickrInstance.setDate([this.basicForm.date, this.basicForm.date_final]);
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

          // Filter Metadata
          this.filterMetadata(this.basicForm.customer);

          // Provider lists
          this.eventHotels = res.eventHotels || [];
          this.eventABs = res.eventABs || [];
          this.eventHalls = res.eventHalls || [];
          this.eventAdds = res.eventAdds || [];
          this.eventTransports = res.eventTransports || [];
          this.eventAirfares = res.eventAirfares || [];
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
  onCustomerChange(customerId: any) {
    this.basicForm.crd_id = '';
    this.basicForm.requester = '';
    this.basicForm.sector = '';
    this.basicForm.cc = '';
    this.filterCrds(customerId);
    this.filterMetadata(customerId);
  }

  checkCustomerSelected(event?: Event) {
    if (!this.basicForm.customer) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }
      this.toastService.warning('Por favor, selecione primeiro a Empresa / Cliente.');
      return false;
    }
    return true;
  }

  filterMetadata(customerId: any) {
    console.log('filterMetadata: filtering for customerId =', customerId);
    if (customerId === null || customerId === undefined || customerId === '') {
      this.requestersFiltered = [];
      this.sectorsFiltered = [];
      this.costCentersFiltered = [];
      return;
    }
    const targetId = Number(customerId);

    this.requestersFiltered = this.customerRequesters.filter(r => Number(r.customer_id) === targetId);
    this.sectorsFiltered = this.customerSectors.filter(s => Number(s.customer_id) === targetId);
    this.costCentersFiltered = this.customerCostCenters.filter(cc => Number(cc.customer_id) === targetId);

    // Preserve existing event values if they are not in the auxiliary metadata tables
    if (this.basicForm.requester && !this.requestersFiltered.some(r => r.name === this.basicForm.requester)) {
      this.requestersFiltered.push({ name: this.basicForm.requester });
    }
    if (this.basicForm.sector && !this.sectorsFiltered.some(s => s.name === this.basicForm.sector)) {
      this.sectorsFiltered.push({ name: this.basicForm.sector });
    }
    if (this.basicForm.cc && !this.costCentersFiltered.some(cc => cc.name === this.basicForm.cc)) {
      this.costCentersFiltered.push({ name: this.basicForm.cc });
    }
  }

  filterCrds(customerId: any) {
    console.log('filterCrds: filtering for customerId =', customerId, 'Type:', typeof customerId);
    console.log('filterCrds: available CRDs =', this.crds);
    if (customerId === null || customerId === undefined || customerId === '') {
      this.crdsFiltered = [];
      console.log('filterCrds: no customerId, cleared list');
      return;
    }
    const targetId = Number(customerId);
    this.crdsFiltered = this.crds.filter((c) => {
      const cId = c.customer_id ? Number(c.customer_id) : null;
      const match = cId === targetId || !cId;
      console.log(`Checking CRD ID ${c.id}: c.customer_id (${c.customer_id}) == customerId (${customerId}) -> Match: ${match}`);
      return match;
    });

    // Preserve existing CRD value if it is not in the filtered list
    if (this.basicForm.crd_id && !this.crdsFiltered.some(c => c.id === this.basicForm.crd_id)) {
      const existingCrd = this.crds.find(c => c.id === this.basicForm.crd_id);
      if (existingCrd) {
        this.crdsFiltered.push(existingCrd);
      }
    }
    console.log('filterCrds: filtered result =', this.crdsFiltered);
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
          this.toastService.error(err.error?.message || 'Erro de validação, verifique os campos.');
        } else {
          this.toastService.error(err.error?.message || 'Erro ao salvar os dados básicos.');
        }
      },
    });
  }

  // --- PROVIDER LINK VINCULOS ---
  normalizePaymentMethod(method: string | null | undefined): string {
    if (!method) return 'Indefinido';
    const normalized = method.trim().toLowerCase();
    if (normalized === 'dinheiro') return 'Dinheiro';
    if (normalized === 'cartão' || normalized === 'cartao') return 'Cartão';
    return 'Indefinido';
  }

  openAddProviderLink(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' | 'airfare', editItem: any = null) {
    this.providerLinkType = type;
    this.errors = {};

    if (editItem) {
      const pId = editItem.hotel_id || editItem.ab_id || editItem.hall_id || editItem.add_id || editItem.transport_id || editItem.airfare_id;
      this.providerLinkForm = {
        id: editItem.id,
        provider_id: pId,
        currency_id: editItem.currency_id,
        iss_percent: editItem.iss_percent || 0,
        service_percent: editItem.service_percent || 0,
        iva_percent: editItem.iva_percent || 0,
        taxa_4bts: editItem.taxa_4bts || 0,
        service_charge: editItem.service_charge || 0,
        payment_method: this.normalizePaymentMethod(editItem.payment_method),
        internal_observation: editItem.internal_observation || '',
        customer_observation: editItem.customer_observation || '',
      };
      // Find matching provider and display name
      let found: any = null;
      if (type === 'hotel' || type === 'ab') {
        found = this.providers.find(p => p.id === pId);
      } else if (type === 'add') {
        found = this.providersService.find(p => p.id === pId);
      } else if (type === 'transport') {
        found = this.providersTransport.find(p => p.id === pId);
      } else if (type === 'airfare') {
        found = this.providersAirfare.find(p => p.id === pId);
      }
      this.selectedProviderName = found ? this.displayProvider(found) : '';
    } else {
      this.providerLinkForm = {
        id: 0,
        provider_id: '',
        currency_id: this.currencies[0]?.id || '',
        iss_percent: 0,
        service_percent: 0,
        iva_percent: 0,
        taxa_4bts: 0,
        service_charge: 0,
        payment_method: 'Indefinido',
        internal_observation: '',
        customer_observation: '',
      };
      this.selectedProviderName = '';
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
      currency: this.providerLinkForm.currency_id,
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
      case 'airfare':
        obs = this.eventService.saveEventAirfare(payload);
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

  deleteProviderLink(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' | 'airfare', id: number) {
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
      case 'airfare':
        obs = this.eventService.deleteEventAirfare(id);
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
  openAddOpt(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' | 'airfare', parentId: number, editItem: any = null, isDuplicate = false) {
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
        compare_trivago: editItem.compare_trivago || 0,
        compare_website_htl: editItem.compare_website_htl || 0,
        compare_omnibess: editItem.compare_omnibess || 0,

        // Airfare fields
        outbound_airline_id: editItem.outbound_airline_id || '',
        outbound_flight_number: editItem.outbound_flight_number || '',
        outbound_class: editItem.outbound_class || '',
        outbound_date: editItem.outbound_date ? editItem.outbound_date.split('T')[0] : '',
        outbound_origin: editItem.outbound_origin || '',
        outbound_destination: editItem.outbound_destination || '',
        outbound_departure_time: editItem.outbound_departure_time || '',
        outbound_arrival_time: editItem.outbound_arrival_time || '',
        outbound_connection_details: editItem.outbound_connection_details || '',
        inbound_airline_id: editItem.inbound_airline_id || '',
        inbound_flight_number: editItem.inbound_flight_number || '',
        inbound_class: editItem.inbound_class || '',
        inbound_date: editItem.inbound_date ? editItem.inbound_date.split('T')[0] : '',
        inbound_origin: editItem.inbound_origin || '',
        inbound_destination: editItem.inbound_destination || '',
        inbound_departure_time: editItem.inbound_departure_time || '',
        inbound_arrival_time: editItem.inbound_arrival_time || '',
        inbound_connection_details: editItem.inbound_connection_details || '',
        baggage_id: editItem.baggage_id || '',
        cabin_id: editItem.cabin_id || '',
        observation: editItem.observation || '',
        status: editItem.status || '',
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
        compare_trivago: 0,
        compare_website_htl: 0,
        compare_omnibess: 0,

        // Airfare defaults
        outbound_airline_id: '',
        outbound_flight_number: '',
        outbound_class: '',
        outbound_date: '',
        outbound_origin: '',
        outbound_destination: '',
        outbound_departure_time: '',
        outbound_arrival_time: '',
        outbound_connection_details: '',
        inbound_airline_id: '',
        inbound_flight_number: '',
        inbound_class: '',
        inbound_date: '',
        inbound_origin: '',
        inbound_destination: '',
        inbound_departure_time: '',
        inbound_arrival_time: '',
        inbound_connection_details: '',
        baggage_id: '',
        cabin_id: '',
        observation: '',
        status: '',
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
      } else if (type === 'airfare') {
        this.optForm.outbound_airline_id = this.airlines[0]?.id || '';
        this.optForm.inbound_airline_id = this.airlines[0]?.id || '';
        this.optForm.baggage_id = this.baggages[0]?.id || '';
        this.optForm.cabin_id = this.cabins[0]?.id || '';
        this.optForm.currency_id = this.currencies[0]?.id || '';
        this.optForm.outbound_date = this.basicForm.date || '';
        this.optForm.inbound_date = this.basicForm.date_final || '';
        this.optForm.status = 'created';
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
        payload.broker = this.optForm.broker_id;
        payload.regime = this.optForm.regime_id;
        payload.purpose = this.optForm.purpose_id;
        obs = this.eventService.saveHotelOpt(payload);
        break;
      case 'ab':
        payload.event_ab_id = this.optForm.parent_id;
        payload.broker = this.optForm.broker_id;
        obs = this.eventService.saveABOpt(payload);
        break;
      case 'hall':
        payload.event_hall_id = this.optForm.parent_id;
        payload.broker = this.optForm.broker_id;
        obs = this.eventService.saveHallOpt(payload);
        break;
      case 'add':
        payload.event_add_id = this.optForm.parent_id;
        payload.frequency = this.optForm.frequency_id;
        payload.measure = this.optForm.measure_id;
        payload.service = this.optForm.service_id;
        obs = this.eventService.saveAddOpt(payload);
        break;
      case 'transport':
        payload.event_transport_id = this.optForm.parent_id;
        payload.broker = this.optForm.broker_id;
        payload.vehicle = this.optForm.vehicle_id;
        payload.model = this.optForm.car_model_id;
        payload.service = this.optForm.service_id;
        payload.brand = this.optForm.brand_id;
        obs = this.eventService.saveTransportOpt(payload);
        break;
      case 'airfare':
        payload.event_airfare_id = this.optForm.parent_id;
        payload.outbound_airline_id = this.optForm.outbound_airline_id;
        payload.inbound_airline_id = this.optForm.inbound_airline_id;
        payload.baggage = this.optForm.baggage_id;
        payload.cabin = this.optForm.cabin_id;
        payload.currency = this.optForm.currency_id;
        obs = this.eventService.saveAirfareOpt(payload);
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
          // Map backend validator keys to frontend property names for error visual cues
          if (this.errors.broker) this.errors.broker_id = this.errors.broker;
          if (this.errors.regime) this.errors.regime_id = this.errors.regime;
          if (this.errors.purpose) this.errors.purpose_id = this.errors.purpose;
          if (this.errors.vehicle) this.errors.vehicle_id = this.errors.vehicle;
          if (this.errors.model) this.errors.car_model_id = this.errors.model;
          if (this.errors.service) this.errors.service_id = this.errors.service;
          if (this.errors.brand) this.errors.brand_id = this.errors.brand;
          if (this.errors.frequency) this.errors.frequency_id = this.errors.frequency;
          if (this.errors.measure) this.errors.measure_id = this.errors.measure;
          if (this.errors.baggage) this.errors.baggage_id = this.errors.baggage;
          if (this.errors.cabin) this.errors.cabin_id = this.errors.cabin;
        } else {
          this.toastService.error(err.error?.message || 'Erro ao salvar tarifa.');
        }
      },
    });
  }

  deleteOpt(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' | 'airfare', id: number) {
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
      case 'airfare':
        obs = this.eventService.deleteAirfareOpt(id);
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
      provider.eventAirfareOpts ||
      provider.event_airfare_opts ||
      [];
    for (const opt of opts) {
      const dateIn = opt.in || opt.outbound_date;
      const dateOut = opt.out || opt.inbound_date;
      sum += parseFloat(opt.count || 0) * this.daysBetween(dateIn, dateOut, isAB);
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
      provider.eventAirfareOpts ||
      provider.event_airfare_opts ||
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
      provider.eventAirfareOpts ||
      provider.event_airfare_opts ||
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
      provider.eventAirfareOpts ||
      provider.event_airfare_opts ||
      [];
    for (const opt of opts) {
      const dateIn = opt.in || opt.outbound_date;
      const dateOut = opt.out || opt.inbound_date;
      sum += this.daysBetween(dateIn, dateOut, isAB);
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
      provider.eventAirfareOpts ||
      provider.event_airfare_opts ||
      [];
    for (const opt of opts) {
      const dateIn = opt.in || opt.outbound_date;
      const dateOut = opt.out || opt.inbound_date;
      const multiplyDays = (provider.eventAirfareOpts || provider.event_airfare_opts) ? 1 : this.daysBetween(dateIn, dateOut, isAB);
      sum += this.unitSale(opt) * (multiplyDays || 1) * parseFloat(opt.count || 0);
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
      provider.eventAirfareOpts ||
      provider.event_airfare_opts ||
      [];
    for (const opt of opts) {
      const dateIn = opt.in || opt.outbound_date;
      const dateOut = opt.out || opt.inbound_date;
      const multiplyDays = (provider.eventAirfareOpts || provider.event_airfare_opts) ? 1 : this.daysBetween(dateIn, dateOut, isAB);
      sum += this.unitCost(opt) * (multiplyDays || 1) * parseFloat(opt.count || 0);
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
      provider.eventAirfareOpts ||
      provider.event_airfare_opts ||
      [];

    for (const opt of opts) {
      const dateIn = opt.in || opt.outbound_date;
      const dateOut = opt.out || opt.inbound_date;
      const multiplyDays = (provider.eventAirfareOpts || provider.event_airfare_opts) ? 1 : this.daysBetween(dateIn, dateOut, isAB);
      const count = parseFloat(opt.count || 0);
      const sale = this.unitSale(opt);

      switch (taxType) {
        case 'iss':
          sum += ((sale * parseFloat(provider.iss_percent || 0)) / 100) * multiplyDays * count;
          break;
        case 'serv':
          sum += ((sale * parseFloat(provider.service_percent || 0)) / 100) * multiplyDays * count;
          break;
        case 'iva':
          sum += ((sale * parseFloat(provider.iva_percent || 0)) / 100) * multiplyDays * count;
          break;
        case 'sc':
          sum += parseFloat(provider.service_charge || 0) * multiplyDays * count;
          break;
      }
    }
    return sum;
  }

  // --- VIEW DETAILS BUTTON MECHANICS ---
  toggleDetails(tabName: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' | 'airfare') {
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
      case 'airfare':
        this.showDetailsAirfare = !this.showDetailsAirfare;
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

  sumCompare(item: any, field: string): number {
    if (!item || !item.event_hotels_opt) return 0;
    return item.event_hotels_opt.reduce((sum: number, opt: any) => sum + parseFloat(opt[field] || 0), 0);
  }

  hasPermission(role: string): boolean {
    return this.authService.user()?.permissions?.some((p: any) => p.name === role) || false;
  }

  // --- PASSENGER (FICHA DE VOO) METHODS ---
  passengerForm = {
    id: 0,
    parent_id: 0,
    name: '',
    document: '',
    passport_validity: '',
    birth_date: '',
    outbound_date: '',
    outbound_origin: '',
    outbound_destination: '',
    outbound_departure: '',
    outbound_arrival: '',
    inbound_date: '',
    inbound_origin: '',
    inbound_destination: '',
    inbound_departure: '',
    inbound_arrival: ''
  };
  showPassengerForm = false;

  openAddPassenger(parentAirfareId: number, editItem: any = null) {
    this.errors = {};
    if (editItem) {
      this.passengerForm = {
        id: editItem.id,
        parent_id: parentAirfareId,
        name: editItem.name || '',
        document: editItem.document || '',
        passport_validity: editItem.passport_validity ? editItem.passport_validity.split('T')[0] : '',
        birth_date: editItem.birth_date ? editItem.birth_date.split('T')[0] : '',
        outbound_date: editItem.outbound_date ? editItem.outbound_date.split('T')[0] : '',
        outbound_origin: editItem.outbound_origin || '',
        outbound_destination: editItem.outbound_destination || '',
        outbound_departure: editItem.outbound_departure || '',
        outbound_arrival: editItem.outbound_arrival || '',
        inbound_date: editItem.inbound_date ? editItem.inbound_date.split('T')[0] : '',
        inbound_origin: editItem.inbound_origin || '',
        inbound_destination: editItem.inbound_destination || '',
        inbound_departure: editItem.inbound_departure || '',
        inbound_arrival: editItem.inbound_arrival || ''
      };
    } else {
      this.passengerForm = {
        id: 0,
        parent_id: parentAirfareId,
        name: '',
        document: '',
        passport_validity: '',
        birth_date: '',
        outbound_date: this.basicForm.date || '',
        outbound_origin: '',
        outbound_destination: '',
        outbound_departure: '',
        outbound_arrival: '',
        inbound_date: this.basicForm.date_final || '',
        inbound_origin: '',
        inbound_destination: '',
        inbound_departure: '',
        inbound_arrival: ''
      };
    }
    this.showPassengerForm = true;
  }

  closePassengerForm() {
    this.showPassengerForm = false;
    this.errors = {};
  }

  savePassenger() {
    this.processing = true;
    this.errors = {};
    
    const payload = {
      ...this.passengerForm,
      event_airfare_id: this.passengerForm.parent_id
    };

    this.eventService.saveAirfarePassenger(payload).subscribe({
      next: (res) => {
        this.processing = false;
        this.toastService.success(res.message || 'Passageiro salvo com sucesso!');
        this.closePassengerForm();
        this.loadInitialData();
      },
      error: (err: HttpErrorResponse) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastService.error(err.error?.message || 'Erro ao salvar passageiro.');
        }
      }
    });
  }

  deletePassenger(id: number) {
    this.isLoader = true;
    this.eventService.deleteAirfarePassenger(id).subscribe({
      next: (res) => {
        this.toastService.success('Passageiro removido com sucesso!');
        this.loadInitialData();
      },
      error: (err) => {
        this.toastService.error('Erro ao remover passageiro.');
        console.error(err);
        this.isLoader = false;
      }
    });
  }
}
}
