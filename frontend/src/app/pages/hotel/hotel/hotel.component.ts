import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ProviderService, Provider, ProviderCreateUpdateRequest } from '../../../services/provider.service';
import { CityService, City } from '../../../services/city.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ToastService } from '../../../services/toast.service';
import { AutocompleteComponent } from '../../../shared/components/autocomplete/autocomplete.component';
import { NgxMaskDirective, provideNgxMask } from 'ngx-mask';

@Component({
  selector: 'app-hotel',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    AuthenticatedLayoutComponent,
    DatatableComponent,
    ConfirmModalComponent,
    AutocompleteComponent,
    NgxMaskDirective,
  ],
  providers: [provideNgxMask()],
  templateUrl: './hotel.component.html',
  styleUrls: ['./hotel.component.scss'],
})
export class HotelComponent implements OnInit {
  private readonly providerService = inject(ProviderService);
  private readonly cityService = inject(CityService);
  private readonly toastr = inject(ToastService);

  providers: Provider[] = [];
  isLoader = false;
  processing = false;
  inEdition = 0;

  pagination = {
    currentPage: 1,
    perPage: 10,
    totalItems: 0,
  };

  searchTerm = '';
  sortColumn = 'id';
  sortDirection = 'desc';

  form: ProviderCreateUpdateRequest = this.getEmptyForm();
  errors: any = {};
  selectedCityName = '';

  ngOnInit(): void {
    this.loadProviders();
  }

  validatePercent(event: any): void {
    const input = event.target as HTMLInputElement;
    const name = input.name;
    let val = parseFloat(input.value);
    if (isNaN(val) || val < 0) {
      val = 0;
    } else if (val > 100) {
      val = 100;
    }
    // round to one decimal place
    val = Math.round(val * 10) / 10;
    // Update form model
    (this.form as any)[name] = val;
    // Reflect corrected value in input
    input.value = val.toString();
  }

  getEmptyForm(): ProviderCreateUpdateRequest {
    return {
      id: 0,
      name: '',
      city_id: 0,
      contact: '',
      phone: '',
      email: '',
      national: true,
      iss_percent: 0,
      service_percent: 0,
      iva_percent: 0,
      payment_method: '',
      checkin_time: '',
      checkin_time_end: '',
      checkout_time: '',
      checkout_time_end: '',
      has_hotel: true,
    };
  }

  loadProviders(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.currentPage,
      per_page: this.pagination.perPage,
      search: this.searchTerm,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
      is_hotel: 1,
    };

    this.providerService.getProviders(params).subscribe({
      next: (response) => {
        this.providers = response.data;
        this.pagination.currentPage = response.current_page;
        this.pagination.totalItems = response.total;
        this.pagination.perPage = response.per_page;
        this.isLoader = false;
      },
      error: () => {
        this.toastr.error('Erro ao carregar hotéis.');
        this.isLoader = false;
      },
    });
  }

  onSearch(term: string): void {
    this.searchTerm = term;
    this.pagination.currentPage = 1;
    this.loadProviders();
  }

  onPageChange(page: number): void {
    this.pagination.currentPage = page;
    this.loadProviders();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.perPage = perPage;
    this.pagination.currentPage = 1;
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

  edit(provider: Provider): void {
    this.inEdition = provider.id;
    this.form = {
      id: provider.id,
      name: provider.name,
      city_id: provider.city_id,
      contact: provider.contact || '',
      phone: provider.phone || '',
      email: provider.email || '',
      national: String(provider.national) === '1' || String(provider.national) === 'true',
      iss_percent: provider.iss_percent || 0,
      service_percent: provider.service_percent || 0,
      iva_percent: provider.iva_percent || 0,
      payment_method: provider.payment_method || '',
      checkin_time: provider.checkin_time || '',
      checkin_time_end: provider.checkin_time_end || '',
      checkout_time: provider.checkout_time || '',
      checkout_time_end: provider.checkout_time_end || '',
      has_hotel: true,
    };

    this.selectedCityName = provider.city
      ? `${provider.city.name} - ${provider.city.states ? provider.city.states : provider.city.country}`
      : '';
    this.errors = {};
  }

  cancelEdit(): void {
    this.inEdition = 0;
    this.resetForm();
  }

  resetForm(): void {
    this.form = this.getEmptyForm();
    this.selectedCityName = '';
    this.errors = {};
  }

  isProviderInEdition(id: number): boolean {
    return this.inEdition === id;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors['name'] = ['O nome é obrigatório'];
    }

    if (!this.form.city_id || this.form.city_id <= 0) {
      this.errors['city_id'] = ['A cidade é obrigatória. Selecione uma cidade da lista.'];
    }

    return Object.keys(this.errors).length === 0;
  }

  submit(): void {
    if (!this.validateForm()) {
      this.toastr.warning('Verifique os campos do formulário.');
      return;
    }

    this.processing = true;
    this.errors = {};

    // Enforce max 100 with at most 1 decimal place on percentages
    const percentFields: ('iss_percent' | 'service_percent' | 'iva_percent')[] = ['iss_percent', 'service_percent', 'iva_percent'];
    percentFields.forEach((field) => {
      if (this.form[field] !== undefined && this.form[field] !== null) {
        let val = Number(this.form[field]);
        if (isNaN(val) || val < 0) {
          this.form[field] = 0;
        } else {
          if (val > 100) {
            val = 100;
          }
          // Round to at most 1 decimal place
          this.form[field] = Math.round(val * 10) / 10;
        }
      }
    });

    this.providerService.saveProvider(this.form).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Hotel salvo com sucesso!');
        this.resetForm();
        this.inEdition = 0;
        this.loadProviders();
        this.processing = false;
      },
      error: (err) => {
        if (err.status === 422) {
          this.errors = err.error.errors;
          this.toastr.warning('Verifique os campos do formulário.');
        } else {
          this.toastr.error('Erro ao salvar o hotel.');
        }
        this.processing = false;
      },
    });
  }

  deleteProvider(id: number): void {
    this.providerService.deleteProvider(id).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Hotel excluído com sucesso!');
        this.loadProviders();
      },
      error: () => {
        this.toastr.error('Erro ao excluir o hotel.');
      },
    });
  }

  activateProvider(id: number): void {
    this.providerService.activateProvider(id).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Hotel ativado com sucesso!');
        this.loadProviders();
      },
      error: () => {
        this.toastr.error('Erro ao ativar o hotel.');
      },
    });
  }

  deactivateProvider(id: number): void {
    this.providerService.deactivateProvider(id).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Hotel inativado com sucesso!');
        this.loadProviders();
      },
      error: () => {
        this.toastr.error('Erro ao inativar o hotel.');
      },
    });
  }

  searchCities = (term: string) => {
    return this.cityService.searchCities(term);
  };

  displayCity = (city: City) => {
    return `${city.name} - ${city.states ? city.states : city.country}`;
  };
}
