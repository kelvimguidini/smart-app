import { Component, OnInit, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { Observable, of, forkJoin } from 'rxjs';

import { EventService } from '../../../services/event.service';
import { AuthService } from '../../../services/auth.service';
import { CityService } from '../../../services/city.service';
import { ToastService } from '../../../services/toast.service';
import { AutocompleteComponent } from '../../../shared/components/autocomplete/autocomplete.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { NgxMaskDirective } from 'ngx-mask';
import flatpickr from 'flatpickr';

@Component({
  selector: 'app-event-create',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, AuthenticatedLayoutComponent, AutocompleteComponent, ConfirmModalComponent, ModalComponent, NgxMaskDirective],
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

  private flatpickrInstance: any;
  private flatpickrElement: any = null;

  @ViewChild('dateRangePicker') set dateRangePicker(element: ElementRef) {
    if (element) {
      if (element.nativeElement !== this.flatpickrElement) {
        this.flatpickrElement = element.nativeElement;
        setTimeout(() => {
          if (element.nativeElement === this.flatpickrElement) {
            this.initFlatpickr(element.nativeElement);
          }
        });
      }
    } else {
      if (this.flatpickrInstance) {
        this.flatpickrInstance.destroy();
        this.flatpickrInstance = null;
      }
      this.flatpickrElement = null;
    }
  }

  private optFlatpickrInstance: any;

  isLoader = false;
  processing = false;
  errors: any = {};

  parseFloat(value: any): number {
    return parseFloat(value || 0);
  }

  sortByName<T>(list: T[], key: string = 'name'): T[] {
    if (!list || list.length === 0) return [];
    return [...list].sort((a: any, b: any) => {
      const valA = String(a[key] || '');
      const valB = String(b[key] || '');
      return valA.localeCompare(valB, 'pt-BR');
    });
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

  // Tab View Options
  showDetailsHotel = false;
  showDetailsAB = false;
  showDetailsHall = false;
  showDetailsAdd = false;
  showDetailsTransport = false;

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
    invoice: false,
    iof: 0,
    change_hotel_times: false,
    checkin_time: '',
    checkin_time_end: '',
    checkout_time: '',
    checkout_time_end: '',
  };
  showProviderLinkForm = false;
  providerLinkType: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' = 'hotel';
  selectedProviderName = '';

  searchProviders = (term: string): Observable<any[]> => {
    const termLower = term.toLowerCase();
    let sourceList: any[] = [];
    if (this.providerLinkType === 'hotel' || this.providerLinkType === 'ab' || this.providerLinkType === 'hall') {
      sourceList = this.providers;
    } else if (this.providerLinkType === 'add') {
      sourceList = this.providersService;
    } else if (this.providerLinkType === 'transport') {
      sourceList = this.providersTransport;
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
    if (this.providerLinkType === 'hotel' || this.providerLinkType === 'ab' || this.providerLinkType === 'hall') {
      found = this.providers.find(p => p.id === providerId);
    } else if (this.providerLinkType === 'add') {
      found = this.providersService.find(p => p.id === providerId);
    } else if (this.providerLinkType === 'transport') {
      found = this.providersTransport.find(p => p.id === providerId);
    }
    
    if (found) {
      this.providerLinkForm.taxa_4bts = found.taxa_4bts || 0;
      this.providerLinkForm.iss_percent = found.iss_percent || 0;
      this.providerLinkForm.service_percent = found.service_percent || 0;
      this.providerLinkForm.iva_percent = found.iva_percent || 0;
      this.providerLinkForm.payment_method = this.normalizePaymentMethod(found.payment_method);
      if (this.providerLinkType === 'hotel') {
        if (!this.providerLinkForm.change_hotel_times) {
          this.providerLinkForm.checkin_time = found.checkin_time || '';
          this.providerLinkForm.checkin_time_end = found.checkin_time_end || '';
          this.providerLinkForm.checkout_time = found.checkout_time || '';
          this.providerLinkForm.checkout_time_end = found.checkout_time_end || '';
        }
      }
    }
  }

  onChangeHotelTimesToggle() {
    if (!this.providerLinkForm.change_hotel_times) {
      const found = this.providers.find(p => p.id === this.providerLinkForm.provider_id);
      if (found) {
        this.providerLinkForm.checkin_time = found.checkin_time || '';
        this.providerLinkForm.checkin_time_end = found.checkin_time_end || '';
        this.providerLinkForm.checkout_time = found.checkout_time || '';
        this.providerLinkForm.checkout_time_end = found.checkout_time_end || '';
      }
    }
  }

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
    received_proposal_percent: 0.8,
    order: 0,
  };
  showOptForm = false;
  optFormType: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' = 'hotel';
  showMarkupForm = false;
  bulkMarkupValue = 100.00;
  bulkMarkupTargetItem: any = null;
  bulkMarkupTargetType: 'hotel' | 'ab' | 'hall' | 'add' | 'transport' = 'hotel';

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

  initFlatpickr(element: any) {
    if (this.flatpickrInstance) {
      this.flatpickrInstance.destroy();
    }
    this.flatpickrInstance = flatpickr(element, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      disableMobile: true,
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
        } else if (selectedDates.length === 0) {
          this.basicForm.date = '';
          this.basicForm.date_final = '';
        }
      },
      onClose: (selectedDates) => {
        if (selectedDates.length !== 2) {
          if (this.basicForm.date && this.basicForm.date_final) {
            this.flatpickrInstance.setDate([this.basicForm.date, this.basicForm.date_final]);
          } else {
            this.flatpickrInstance.clear();
          }
        }
      },
    });

    if (this.basicForm.date && this.basicForm.date_final) {
      this.flatpickrInstance.setDate([this.basicForm.date, this.basicForm.date_final]);
    }
  }

  loadInitialData() {
    this.isLoader = true;
    this.eventService.getEditData(this.eventId).subscribe({
      next: (res) => {
        console.log('loadInitialData: edit-data API response =', res);
        // Load master lists sorted alphabetically
        this.customers = this.sortByName(res.customers || []);
        this.crds = this.sortByName(res.crds || []);
        this.users = this.sortByName(res.users || []);
        this.providers = this.sortByName(res.providers || []);
        this.providersService = this.sortByName(res.providersService || []);
        this.providersTransport = this.sortByName(res.providersTransport || []);
        this.brokers = this.sortByName(res.brokers || []);
        this.currencies = this.sortByName(res.currencies || []);
        this.regimes = this.sortByName(res.regimes || []);
        this.purposes = this.sortByName(res.purposes || []);
        this.services = this.sortByName(res.services || []);
        this.servicesType = this.sortByName(res.servicesType || []);
        this.locals = this.sortByName(res.locals || []);
        this.catsHotel = this.sortByName(res.catsHotel || []);
        this.aptosHotel = this.sortByName(res.aptosHotel || []);
        this.brokersT = this.sortByName(res.brokersT || []);
        this.vehicles = this.sortByName(res.vehicles || []);
        this.models = this.sortByName(res.models || []);
        this.servicesT = this.sortByName(res.servicesT || []);
        this.brands = this.sortByName(res.brands || []);
        this.servicesHall = this.sortByName(res.servicesHall || []);
        this.purposesHall = this.sortByName(res.purposesHall || []);
        this.servicesAdd = this.sortByName(res.servicesAdd || []);
        this.frequencies = this.sortByName(res.frequencies || []);
        this.measures = this.sortByName(res.measures || []);
        this.allStatus = res.allStatus || {};
        this.customerRequesters = this.sortByName(res.customerRequesters || []);
        this.customerSectors = this.sortByName(res.customerSectors || []);
        this.customerCostCenters = this.sortByName(res.customerCostCenters || []);

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
          if (this.flatpickrInstance) {
            if (this.basicForm.date && this.basicForm.date_final) {
              this.flatpickrInstance.setDate([this.basicForm.date, this.basicForm.date_final]);
            } else {
              this.flatpickrInstance.clear();
            }
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

    // Sort alphabetically
    this.requestersFiltered = this.sortByName(this.requestersFiltered);
    this.sectorsFiltered = this.sortByName(this.sectorsFiltered);
    this.costCentersFiltered = this.sortByName(this.costCentersFiltered);
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
    // Sort alphabetically
    this.crdsFiltered = this.sortByName(this.crdsFiltered);
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

  getSelectedCurrencySymbol(): string {
    const cId = this.providerLinkForm.currency_id;
    if (!cId) return '';
    const found = this.currencies.find(c => Number(c.id) === Number(cId));
    return found ? (found.symbol || found.sigla || '') : '';
  }

  getOptCurrencySymbol(): string {
    const parentId = this.optForm.parent_id;
    if (!parentId) return '';
    let foundParent: any = null;
    switch (this.optFormType) {
      case 'hotel':
        foundParent = this.eventHotels.find(h => h.id === parentId);
        break;
      case 'ab':
        foundParent = this.eventABs.find(ab => ab.id === parentId);
        break;
      case 'hall':
        foundParent = this.eventHalls.find(h => h.id === parentId);
        break;
      case 'add':
        foundParent = this.eventAdds.find(a => a.id === parentId);
        break;
      case 'transport':
        foundParent = this.eventTransports.find(t => t.id === parentId);
        break;
    }
    if (foundParent) {
      const cId = foundParent.currency_id;
      if (cId) {
        const foundCurr = this.currencies.find(c => Number(c.id) === Number(cId));
        return foundCurr ? (foundCurr.symbol || foundCurr.sigla || '') : '';
      }
    }
    return '';
  }

  formatMoney(value: any): string {
    const num = parseFloat(value);
    if (isNaN(num)) return '0,00';
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  formatPercent(value: any): string {
    const num = parseFloat(value);
    if (isNaN(num)) return '0,0';
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
  }

  onMoneyInput(event: any, field: string, form: 'providerLinkForm' | 'optForm') {
    const raw = event.target.value;
    const clean = raw.replace(/\D/g, '');
    if (!clean) {
      if (form === 'providerLinkForm') {
        this.providerLinkForm[field] = 0;
      } else {
        this.optForm[field] = 0;
      }
      event.target.value = '0,00';
      return;
    }
    const parsed = parseFloat(clean) / 100;
    if (form === 'providerLinkForm') {
      this.providerLinkForm[field] = parsed;
    } else {
      this.optForm[field] = parsed;
    }
    event.target.value = this.formatMoney(parsed);
  }

  onPercentInput(event: any, field: string, form: 'providerLinkForm' | 'optForm' | 'bulkForm') {
    const raw = event.target.value;
    const clean = raw.replace(/\D/g, '');
    if (!clean) {
      if (form === 'providerLinkForm') {
        this.providerLinkForm[field] = 0;
      } else if (form === 'optForm') {
        this.optForm[field] = 0;
      } else {
        (this as any)[field] = 0;
      }
      event.target.value = '0,0';
      return;
    }
    let parsed = parseFloat(clean) / 10;
    if (parsed > 100) {
      parsed = 100;
    }
    if (form === 'providerLinkForm') {
      this.providerLinkForm[field] = parsed;
    } else if (form === 'optForm') {
      this.optForm[field] = parsed;
    } else {
      (this as any)[field] = parsed;
    }
    event.target.value = this.formatPercent(parsed);
  }

  openAddProviderLink(type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport', editItem: any = null) {
    this.providerLinkType = type;
    this.errors = {};

    if (editItem) {
      const pId = editItem.hotel_id || editItem.ab_id || editItem.hall_id || editItem.add_id || editItem.transport_id;
      // Find matching provider and display name
      let found: any = null;
      if (type === 'hotel' || type === 'ab' || type === 'hall') {
        found = this.providers.find(p => p.id === pId);
      } else if (type === 'add') {
        found = this.providersService.find(p => p.id === pId);
      } else if (type === 'transport') {
        found = this.providersTransport.find(p => p.id === pId);
      }
      this.selectedProviderName = found ? this.displayProvider(found) : '';

      let isDifferent = false;
      if (type === 'hotel' && found) {
        isDifferent =
          (editItem.checkin_time || '') !== (found.checkin_time || '') ||
          (editItem.checkin_time_end || '') !== (found.checkin_time_end || '') ||
          (editItem.checkout_time || '') !== (found.checkout_time || '') ||
          (editItem.checkout_time_end || '') !== (found.checkout_time_end || '');
      }

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
        invoice: editItem.invoice !== undefined ? !!editItem.invoice : false,
        iof: editItem.iof || 0,
        change_hotel_times: isDifferent,
        checkin_time: editItem.checkin_time || '',
        checkin_time_end: editItem.checkin_time_end || '',
        checkout_time: editItem.checkout_time || '',
        checkout_time_end: editItem.checkout_time_end || '',
      };
    } else {
      this.providerLinkForm = {
        id: 0,
        provider_id: '',
        currency_id: '',
        iss_percent: 0,
        service_percent: 0,
        iva_percent: 0,
        taxa_4bts: 0,
        service_charge: 0,
        payment_method: 'Indefinido',
        internal_observation: '',
        customer_observation: '',
        invoice: false,
        iof: 0,
        change_hotel_times: false,
        checkin_time: '',
        checkin_time_end: '',
        checkout_time: '',
        checkout_time_end: '',
      };
      this.selectedProviderName = '';
    }

    this.showProviderLinkForm = true;
  }

  closeProviderLinkForm() {
    this.showProviderLinkForm = false;
    this.errors = {};
  }

  onCurrencyChange(currencyId: any) {
    if (!currencyId) return;
    const currency = this.currencies.find(c => Number(c.id) === Number(currencyId));
    if (currency) {
      if (currency.sigla !== 'BRL') {
        if (!this.providerLinkForm.iof || Number(this.providerLinkForm.iof) === 0) {
          this.providerLinkForm.iof = 3.5;
        }
      } else {
        this.providerLinkForm.iof = 0;
      }
    }
  }

  saveProviderLink() {
    this.processing = true;
    this.errors = {};

    let hasErrors = false;

    if (!this.providerLinkForm.provider_id) {
      this.errors.provider_id = ['O campo fornecedor é obrigatório.'];
      hasErrors = true;
    }

    if (!this.providerLinkForm.currency_id) {
      this.errors.currency_id = ['O campo moeda é obrigatório.'];
      hasErrors = true;
    }

    if (
      this.providerLinkForm.taxa_4bts === null ||
      this.providerLinkForm.taxa_4bts === undefined ||
      this.providerLinkForm.taxa_4bts === ''
    ) {
      this.errors.taxa_4bts = ['O campo taxa 4BTS é obrigatório.'];
      hasErrors = true;
    }

    const selectedCurrency = this.currencies.find(c => Number(c.id) === Number(this.providerLinkForm.currency_id));
    if (selectedCurrency && selectedCurrency.sigla !== 'BRL') {
      const iofVal = Number(this.providerLinkForm.iof || 0);
      if (iofVal <= 0) {
        this.errors.iof = ['O IOF não pode ser zero ou menor para moedas estrangeiras.'];
        hasErrors = true;
      }
    }

    if (hasErrors) {
      this.processing = false;
      this.toastService.error('Por favor, preencha todos os campos obrigatórios.');
      return;
    }

    if (this.providerLinkType === 'hotel' && !this.providerLinkForm.change_hotel_times) {
      const found = this.providers.find(p => p.id === this.providerLinkForm.provider_id);
      if (found) {
        this.providerLinkForm.checkin_time = found.checkin_time || '';
        this.providerLinkForm.checkin_time_end = found.checkin_time_end || '';
        this.providerLinkForm.checkout_time = found.checkout_time || '';
        this.providerLinkForm.checkout_time_end = found.checkout_time_end || '';
      }
    }

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

  updateMarkupBulk(item: any, type: 'hotel' | 'ab' | 'hall' | 'add' | 'transport') {
    // Get the options array first to check if they have any options
    let options: any[] = [];
    switch (type) {
      case 'hotel': options = item.event_hotels_opt || []; break;
      case 'ab': options = item.event_ab_opts || []; break;
      case 'hall': options = item.event_hall_opts || []; break;
      case 'add': options = item.event_add_opts || []; break;
      case 'transport': options = item.event_transport_opts || []; break;
    }

    if (options.length === 0) {
      this.toastService.warning('Este fornecedor não possui tarifas cadastradas.');
      return;
    }

    this.bulkMarkupTargetItem = item;
    this.bulkMarkupTargetType = type;
    this.bulkMarkupValue = options[0].received_proposal_percent !== undefined
      ? options[0].received_proposal_percent
      : 100.00;

    this.showMarkupForm = true;
  }

  closeMarkupForm() {
    this.showMarkupForm = false;
    this.bulkMarkupTargetItem = null;
  }

  confirmMarkupBulk() {
    if (!this.bulkMarkupTargetItem) return;
    const item = this.bulkMarkupTargetItem;
    const type = this.bulkMarkupTargetType;
    const parsedPercent = this.bulkMarkupValue;

    if (parsedPercent <= 0 || parsedPercent > 100) {
      this.toastService.error('O markup divisor deve ser maior que 0% e no máximo 100%.');
      return;
    }

    let options: any[] = [];
    switch (type) {
      case 'hotel': options = item.event_hotels_opt || []; break;
      case 'ab': options = item.event_ab_opts || []; break;
      case 'hall': options = item.event_hall_opts || []; break;
      case 'add': options = item.event_add_opts || []; break;
      case 'transport': options = item.event_transport_opts || []; break;
    }

    this.processing = true;
    this.isLoader = true;
    const requests: Observable<any>[] = [];

    options.forEach(opt => {
      let payload: any = {
        id: opt.id,
        parent_id: item.id,
        broker_id: opt.broker_id,
        regime_id: opt.regime_id,
        purpose_id: opt.purpose_id,
        category_id: opt.category_hotel_id || opt.category_id,
        apto_id: opt.apto_hotel_id || opt.apto_id,
        service_id: opt.service_id,
        service_type_id: opt.service_type_id,
        local_id: opt.local_id,
        frequency_id: opt.frequency_id,
        measure_id: opt.measure_id,
        vehicle_id: opt.vehicle_id,
        car_model_id: opt.car_model_id,
        brand_id: opt.brand_id,
        in: opt.in ? opt.in.substring(0, 10) : '',
        out: opt.out ? opt.out.substring(0, 10) : '',
        count: opt.count,
        kickback: opt.kickback,
        received_proposal: opt.received_proposal,
        received_proposal_percent: parsedPercent,
        compare_trivago: opt.compare_trivago,
        compare_website_htl: opt.compare_website_htl,
        compare_omnibess: opt.compare_omnibess,
        observation: opt.observation,
        order: opt.order || 0
      };

      switch (type) {
        case 'hotel':
          payload.event_hotel_id = item.id;
          payload.broker = opt.broker_id;
          payload.regime = opt.regime_id;
          payload.purpose = opt.purpose_id;
          requests.push(this.eventService.saveHotelOpt(payload));
          break;
        case 'ab':
          payload.event_ab_id = item.id;
          payload.broker = opt.broker_id;
          requests.push(this.eventService.saveABOpt(payload));
          break;
        case 'hall':
          payload.event_hall_id = item.id;
          payload.broker = opt.broker_id;
          requests.push(this.eventService.saveHallOpt(payload));
          break;
        case 'add':
          payload.event_add_id = item.id;
          payload.frequency = opt.frequency_id;
          payload.measure = opt.measure_id;
          payload.service = opt.service_id;
          requests.push(this.eventService.saveAddOpt(payload));
          break;
        case 'transport':
          payload.event_transport_id = item.id;
          payload.broker = opt.broker_id;
          payload.vehicle = opt.vehicle_id;
          payload.model = opt.car_model_id;
          payload.service = opt.service_id;
          payload.brand = opt.brand_id;
          requests.push(this.eventService.saveTransportOpt(payload));
          break;
      }
    });

    forkJoin(requests).subscribe({
      next: () => {
        this.isLoader = false;
        this.processing = false;
        this.toastService.success('Markup de todas as tarifas atualizado com sucesso!');
        this.closeMarkupForm();
        this.loadInitialData();
      },
      error: (err) => {
        this.isLoader = false;
        this.processing = false;
        this.toastService.error('Erro ao atualizar o markup de algumas tarifas.');
        console.error(err);
      }
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
        category_id: editItem.category_hotel_id || editItem.category_id || '',
        apto_id: editItem.apto_hotel_id || editItem.apto_id || '',
        service_id: editItem.service_id || '',
        service_type_id: editItem.service_type_id || '',
        local_id: editItem.local_id || '',
        frequency_id: editItem.frequency_id || '',
        measure_id: editItem.measure_id || '',
        vehicle_id: editItem.vehicle_id || '',
        car_model_id: editItem.car_model_id || '',
        brand_id: editItem.brand_id || '',
        in: editItem.in ? editItem.in.substring(0, 10) : '',
        out: editItem.out ? editItem.out.substring(0, 10) : '',
        count: editItem.count ? Math.round(parseFloat(editItem.count)) : 1,
        kickback: editItem.kickback || 0,
        received_proposal: editItem.received_proposal || 0,
        received_proposal_percent: editItem.received_proposal_percent || 0.8,
        compare_trivago: editItem.compare_trivago || 0,
        compare_website_htl: editItem.compare_website_htl || 0,
        compare_omnibess: editItem.compare_omnibess || 0,
        observation: editItem.observation || '',
        order: editItem.order || 0,
      };
    } else {
      this.optForm = {
        id: 0,
        parent_id: parentId,
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
        received_proposal_percent: 0.8,
        compare_trivago: 0,
        compare_website_htl: 0,
        compare_omnibess: 0,
        observation: '',
        order: 0,
      };
    }

    this.showOptForm = true;
    setTimeout(() => {
      const element = document.getElementById('opt_date_range');
      if (element) {
        this.initOptFlatpickr(element);
      }
    });
  }

  closeOptForm() {
    this.showOptForm = false;
    this.errors = {};
    if (this.optFlatpickrInstance) {
      this.optFlatpickrInstance.clear(false);
      this.optFlatpickrInstance.destroy();
      this.optFlatpickrInstance = null;
    }
  }

  formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  initOptFlatpickr(element: any) {
    if (this.optFlatpickrInstance) {
      this.optFlatpickrInstance.destroy();
    }
    this.optFlatpickrInstance = flatpickr(element, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      disableMobile: true,
      defaultDate: this.optForm.in && this.optForm.out ? [this.optForm.in, this.optForm.out] : undefined,
      locale: this.getFlatpickrLocale(),
      onChange: (selectedDates) => {
        if (selectedDates.length === 2) {
          this.optForm.in = this.formatDate(selectedDates[0]);
          this.optForm.out = this.formatDate(selectedDates[1]);
        } else if (selectedDates.length === 0) {
          this.optForm.in = '';
          this.optForm.out = '';
        }
      },
      onClose: (selectedDates) => {
        if (selectedDates.length !== 2) {
          if (this.optForm.in && this.optForm.out) {
            this.optFlatpickrInstance.setDate([this.optForm.in, this.optForm.out]);
          } else {
            this.optFlatpickrInstance.clear();
          }
        }
      },
    });

    if (this.optForm.in && this.optForm.out) {
      this.optFlatpickrInstance.setDate([this.optForm.in, this.optForm.out], false);
    } else {
      this.optFlatpickrInstance.clear(false);
    }
  }

  getFlatpickrLocale(): any {
    return {
      firstDayOfWeek: 1,
      weekdays: {
        shorthand: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        longhand: ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'],
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
    } as any;
  }

  saveOpt() {
    this.processing = true;
    this.errors = {};

    let hasErrors = false;

    // Period validation
    if (!this.optForm.in || !this.optForm.out) {
      if (!this.optForm.in) this.errors.in = ['O campo de entrada é obrigatório.'];
      if (!this.optForm.out) this.errors.out = ['O campo de saída é obrigatório.'];
      hasErrors = true;
    } else {
      const dateIn = new Date(this.optForm.in);
      const dateOut = new Date(this.optForm.out);
      if (dateOut < dateIn) {
        this.errors.out = ['A data de saída deve ser igual ou posterior à data de entrada.'];
        hasErrors = true;
      }
    }

    // Count validation
    if (this.optForm.count === null || this.optForm.count === undefined || this.optForm.count === '') {
      this.errors.count = ['O campo quantidade é obrigatório.'];
      hasErrors = true;
    } else {
      const countVal = Number(this.optForm.count);
      if (isNaN(countVal) || countVal <= 0) {
        this.errors.count = ['A quantidade deve ser maior que zero.'];
        hasErrors = true;
      } else if (countVal % 1 !== 0) {
        this.errors.count = ['A quantidade deve ser um número inteiro (sem frações).'];
        hasErrors = true;
      }
    }

    // Received proposal validation
    if (this.optForm.received_proposal === null || this.optForm.received_proposal === undefined || this.optForm.received_proposal === '') {
      this.errors.received_proposal = ['O campo proposta recebida é obrigatório.'];
      hasErrors = true;
    }

    // Markup percentage validation
    if (this.optForm.received_proposal_percent === null || this.optForm.received_proposal_percent === undefined || this.optForm.received_proposal_percent === '') {
      this.errors.received_proposal_percent = ['O campo markup é obrigatório.'];
      hasErrors = true;
    } else {
      const markupVal = Number(this.optForm.received_proposal_percent);
      if (isNaN(markupVal) || markupVal <= 0 || markupVal > 100) {
        this.errors.received_proposal_percent = ['O markup deve ser maior que 0% e no máximo 100%.'];
        hasErrors = true;
      }
    }

    // Dynamic select validations
    switch (this.optFormType) {
      case 'hotel':
        if (!this.optForm.broker_id) { this.errors.broker_id = ['O campo broker é obrigatório.']; hasErrors = true; }
        if (!this.optForm.regime_id) { this.errors.regime_id = ['O campo regime é obrigatório.']; hasErrors = true; }
        if (!this.optForm.purpose_id) { this.errors.purpose_id = ['O campo propósito é obrigatório.']; hasErrors = true; }
        if (!this.optForm.category_id) { this.errors.category_id = ['O campo categoria apto é obrigatório.']; hasErrors = true; }
        if (!this.optForm.apto_id) { this.errors.apto_id = ['O campo tipo apto é obrigatório.']; hasErrors = true; }
        break;
      case 'ab':
        if (!this.optForm.broker_id) { this.errors.broker_id = ['O campo broker é obrigatório.']; hasErrors = true; }
        if (!this.optForm.service_id) { this.errors.service_id = ['O campo serviço é obrigatório.']; hasErrors = true; }
        if (!this.optForm.service_type_id) { this.errors.service_type_id = ['O campo tipo de serviço é obrigatório.']; hasErrors = true; }
        if (!this.optForm.local_id) { this.errors.local_id = ['O campo local é obrigatório.']; hasErrors = true; }
        break;
      case 'hall':
        if (!this.optForm.broker_id) { this.errors.broker_id = ['O campo broker é obrigatório.']; hasErrors = true; }
        if (!this.optForm.purpose_id) { this.errors.purpose_id = ['O campo propósito é obrigatório.']; hasErrors = true; }
        if (!this.optForm.service_id) { this.errors.service_id = ['O campo serviço é obrigatório.']; hasErrors = true; }
        break;
      case 'add':
        if (!this.optForm.frequency_id) { this.errors.frequency_id = ['O campo frequência é obrigatório.']; hasErrors = true; }
        if (!this.optForm.measure_id) { this.errors.measure_id = ['O campo medida é obrigatório.']; hasErrors = true; }
        if (!this.optForm.service_id) { this.errors.service_id = ['O campo serviço é obrigatório.']; hasErrors = true; }
        break;
      case 'transport':
        if (!this.optForm.broker_id) { this.errors.broker_id = ['O campo broker é obrigatório.']; hasErrors = true; }
        if (!this.optForm.service_id) { this.errors.service_id = ['O campo serviço/trecho é obrigatório.']; hasErrors = true; }
        if (!this.optForm.vehicle_id) { this.errors.vehicle_id = ['O campo tipo veículo é obrigatório.']; hasErrors = true; }
        if (!this.optForm.car_model_id) { this.errors.car_model_id = ['O campo modelo veículo é obrigatório.']; hasErrors = true; }
        if (!this.optForm.brand_id) { this.errors.brand_id = ['O campo marca veículo é obrigatório.']; hasErrors = true; }
        break;
    }

    if (hasErrors) {
      this.processing = false;
      // Map frontend keys to backend keys for visual error cues consistency in HTML
      if (this.errors.broker_id) this.errors.broker = this.errors.broker_id;
      if (this.errors.regime_id) this.errors.regime = this.errors.regime_id;
      if (this.errors.purpose_id) this.errors.purpose = this.errors.purpose_id;
      if (this.errors.vehicle_id) this.errors.vehicle = this.errors.vehicle_id;
      if (this.errors.car_model_id) this.errors.model = this.errors.car_model_id;
      if (this.errors.service_id) this.errors.service = this.errors.service_id;
      if (this.errors.brand_id) this.errors.brand = this.errors.brand_id;
      if (this.errors.frequency_id) this.errors.frequency = this.errors.frequency_id;
      if (this.errors.measure_id) this.errors.measure = this.errors.measure_id;

      this.toastService.error('Por favor, preencha todos os campos obrigatórios.');
      return;
    }

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
        const message = err?.error?.message || err?.message || 'Erro ao remover opção.';
        this.toastService.error(message);
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

    const result = includeLastDay ? days + 1 : days;
    return result === 0 ? 1 : result;
  }

  unitCost(opt: any): number {
    return parseFloat(opt.received_proposal || 0);
  }

  unitSale(opt: any): number {
    const cost = this.unitCost(opt);
    const percent = parseFloat(opt.received_proposal_percent || 0);
    if (percent > 0) {
      const factor = percent > 2 ? percent / 100 : percent;
      return Math.ceil(cost / factor);
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

  sumCompare(item: any, field: string): number {
    if (!item || !item.event_hotels_opt) return 0;
    return item.event_hotels_opt.reduce((sum: number, opt: any) => sum + parseFloat(opt[field] || 0), 0);
  }

  hasPermission(role: string): boolean {
    return this.authService.user()?.permissions?.some((p: any) => p.name === role) || false;
  }
}
