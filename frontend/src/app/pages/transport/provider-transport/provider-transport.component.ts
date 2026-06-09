import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { ProviderTransportService, ProviderTransport, ProviderTransportCreateUpdateRequest } from '../../../services/provider-transport.service';
import { CityService } from '../../../services/city.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { AutocompleteComponent } from '../../../shared/components/autocomplete/autocomplete.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';
import { NgxMaskDirective } from 'ngx-mask';

@Component({
  selector: 'app-provider-transport',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    AuthenticatedLayoutComponent,
    ConfirmModalComponent,
    DatatableComponent,
    AutocompleteComponent,
    ModalComponent,
    NgxMaskDirective
  ],
  templateUrl: './provider-transport.component.html',
  styleUrls: ['./provider-transport.component.scss'],
})
export class ProviderTransportComponent implements OnInit {
  private readonly providerTransportService: ProviderTransportService = inject(ProviderTransportService);
  private readonly cityService: CityService = inject(CityService);
  private readonly toastService: ToastService = inject(ToastService);

  providers: ProviderTransport[] = [];
  inEdition: number = 0;
  isLoader: boolean = false;
  processing: boolean = false;
  errors: any = {};
  showModal: boolean = false;

  // Pagination and Filtering state
  pagination: any = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0,
  };
  searchQuery: string = '';
  sortColumn: string = 'id';
  sortDirection: string = 'desc';

  selectedCityName: string = '';

  // Autocomplete Functions
  searchCities = (term: string) => this.cityService.searchCities(term);
  displayCity = (city: any) => (city ? `${city.name} - ${city.states ? city.states : city.country}` : '');

  form = {
    id: 0,
    name: '',
    city_id: undefined as number | undefined,
    contact: '',
    phone: '',
    email: '',
    national: true,
    iss_percent: null as number | null,
    service_percent: null as number | null,
    iva_percent: null as number | null,
    payment_method: '',
  };

  ngOnInit(): void {
    this.loadProviders();
  }

  loadProviders(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.providerTransportService.getProviders(params).subscribe({
      next: (response: any) => {
        this.providers = response.data || [];
        this.pagination = {
          current_page: response.current_page,
          per_page: response.per_page,
          total: response.total,
          last_page: response.last_page,
          from: response.from,
          to: response.to,
        };
        this.isLoader = false;
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao carregar fornecedores de transporte');
        console.error('Erro ao carregar fornecedores de transporte:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadProviders();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadProviders();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadProviders();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadProviders();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(provider: ProviderTransport): void {
    this.inEdition = provider.id;
    this.form = {
      id: provider.id,
      name: provider.name,
      city_id: provider.city_id,
      contact: provider.contact,
      phone: provider.phone,
      email: provider.email,
      national: String(provider.national) === '1' || String(provider.national) === 'true',
      iss_percent: provider.iss_percent ?? null,
      service_percent: provider.service_percent ?? null,
      iva_percent: provider.iva_percent ?? null,
      payment_method: provider.payment_method ?? '',
    };

    this.selectedCityName = provider.city
      ? `${provider.city.name} - ${provider.city.states ? provider.city.states : provider.city.country}`
      : '';
    this.errors = {};
    this.showModal = true;
  }

  cancelEdit(): void {
    this.closeModal();
  }

  resetForm(): void {
    this.form = {
      id: 0,
      name: '',
      city_id: undefined,
      contact: '',
      phone: '',
      email: '',
      national: true,
      iss_percent: null,
      service_percent: null,
      iva_percent: null,
      payment_method: '',
    };
    this.selectedCityName = '';
    this.errors = {};
    this.inEdition = 0;
  }

  private validateForm(): boolean {
    this.errors = {};
    let isValid = true;

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
      isValid = false;
    }

    if (!this.form.contact || this.form.contact.trim() === '') {
      this.errors.contact = ['O contato é obrigatório'];
      isValid = false;
    }

    if (!this.form.phone || this.form.phone.trim() === '') {
      this.errors.phone = ['O telefone é obrigatório'];
      isValid = false;
    }

    if (!this.form.email || this.form.email.trim() === '') {
      this.errors.email = ['O e-mail é obrigatório'];
      isValid = false;
    } else {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.form.email)) {
        this.errors.email = ['O e-mail informado é inválido'];
        isValid = false;
      }
    }

    if (!this.form.city_id) {
      this.errors.city_id = ['A cidade é obrigatória'];
      isValid = false;
    }

    return isValid;
  }

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: ProviderTransportCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
      city_id: this.form.city_id,
      contact: this.form.contact,
      phone: this.form.phone,
      email: this.form.email,
      national: this.form.national,
      iss_percent: this.form.iss_percent,
      service_percent: this.form.service_percent,
      iva_percent: this.form.iva_percent,
      payment_method: this.form.payment_method || null,
    };

    this.providerTransportService.saveProvider(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Fornecedor de transporte salvo com sucesso');
        this.closeModal();
        this.loadProviders();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar fornecedor de transporte');
        }
        console.error('Erro ao salvar fornecedor de transporte:', error);
      },
    });
  }

  deleteProvider(id: number): void {
    this.isLoader = true;
    this.providerTransportService.deleteProvider(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Fornecedor de transporte apagado com sucesso');
        this.loadProviders();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar fornecedor de transporte');
        console.error('Erro ao deletar fornecedor de transporte:', error);
      },
    });
  }

  activateProvider(id: number): void {
    this.isLoader = true;
    this.providerTransportService.activateProvider(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Fornecedor de transporte ativado com sucesso');
        this.loadProviders();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar fornecedor de transporte');
        console.error('Erro ao ativar fornecedor de transporte:', error);
      },
    });
  }

  deactivateProvider(id: number): void {
    this.isLoader = true;
    this.providerTransportService.deactivateProvider(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Fornecedor de transporte inativado com sucesso');
        this.loadProviders();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar fornecedor de transporte');
        console.error('Erro ao inativar fornecedor de transporte:', error);
      },
    });
  }

  isProviderInEdition(id: number): boolean {
    return this.inEdition === id;
  }
}
