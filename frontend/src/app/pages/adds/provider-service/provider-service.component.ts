import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { AutocompleteComponent } from '../../../shared/components/autocomplete/autocomplete.component';

import { ProviderServiceService, ProviderService, ProviderServiceCreateUpdateRequest } from '../../../services/provider-service.service';
import { CityService, City } from '../../../services/city.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { NgxMaskDirective, NgxMaskPipe } from 'ngx-mask';

@Component({
  selector: 'app-provider-service',
  standalone: true,
  imports: [CommonModule, FormsModule, AutocompleteComponent, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, NgxMaskDirective, NgxMaskPipe],
  templateUrl: './provider-service.component.html',
  styleUrls: ['./provider-service.component.scss'],
})
export class ProviderServiceComponent implements OnInit {
  private readonly providerServiceService: ProviderServiceService = inject(ProviderServiceService);
  private readonly cityService: CityService = inject(CityService);
  private readonly toastService: ToastService = inject(ToastService);

  providerServices: ProviderService[] = [];
  cities: City[] = [];
  inEdition: number = 0;
  isLoader: boolean = false;
  processing: boolean = false;
  errors: any = {};
  selectedCityName: string = '';
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

  form = {
    id: 0,
    name: '',
    city_id: null as number | null,
    contact: '',
    phone: '',
    email: '',
    national: true,
    iss_percent: 0,
    service_percent: 0,
    iva_percent: 0,
    codestur: '',
    payment_method: '',
  };

  ngOnInit(): void {
    this.loadProviderServices();
  }

  // --- Autocomplete City ---
  searchCities = (term: string) => {
    return this.cityService.searchCities(term);
  };

  displayCity(city: City): string {
    if (!city) return '';
    return city.states ? `${city.name} - ${city.states}` : `${city.name} - ${city.country}`;
  }
  // -------------------------

  loadProviderServices(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.providerServiceService.getProviderServices(params).subscribe({
      next: (response: any) => {
        this.providerServices = response.data || [];
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
        this.toastService.error('Erro ao carregar fornecedores');
        console.error('Erro ao carregar fornecedores:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadProviderServices();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadProviderServices();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadProviderServices();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadProviderServices();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(providerService: ProviderService): void {
    this.inEdition = providerService.id;
    this.form = {
      id: providerService.id,
      name: providerService.name,
      city_id: providerService.city_id,
      contact: providerService.contact || '',
      phone: providerService.phone || '',
      email: providerService.email || '',
      national: providerService.national,
      iss_percent: providerService.iss_percent || 0,
      service_percent: providerService.service_percent || 0,
      iva_percent: providerService.iva_percent || 0,
      codestur: providerService.codestur || '',
      payment_method: providerService.payment_method || '',
    };
    if (providerService.city) {
      this.selectedCityName = this.displayCity(providerService.city);
    } else {
      this.selectedCityName = '';
    }
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
      city_id: null,
      contact: '',
      phone: '',
      email: '',
      national: true,
      iss_percent: 0,
      service_percent: 0,
      iva_percent: 0,
      codestur: '',
      payment_method: '',
    };
    this.selectedCityName = '';
    this.errors = {};
    this.inEdition = 0;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
    }

    if (!this.form.city_id) {
      this.errors.city_id = ['A cidade é obrigatória'];
    }

    if (!this.form.contact || this.form.contact.trim() === '') {
      this.errors.contact = ['O contato é obrigatório'];
    }

    if (this.form.email && this.form.email.trim() !== '') {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.form.email)) {
        this.errors.email = ['Informe um endereço de e-mail válido'];
      }
    }

    if (!this.form.payment_method || this.form.payment_method.trim() === '') {
      this.errors.payment_method = ['A condição de pagamento é obrigatória'];
    }

    if (this.form.iss_percent === null || this.form.iss_percent === undefined) {
      this.errors.iss_percent = ['O % ISS é obrigatório'];
    }

    if (this.form.service_percent === null || this.form.service_percent === undefined) {
      this.errors.service_percent = ['O % Serviço é obrigatório'];
    }

    if (this.form.iva_percent === null || this.form.iva_percent === undefined) {
      this.errors.iva_percent = ['O % IVA é obrigatório'];
    }

    return Object.keys(this.errors).length === 0;
  }

  validatePercent(event: any, field: string): void {
    let value = parseFloat(event.target.value);
    if (value > 100) {
      event.target.value = 100;
      (this.form as any)[field] = 100;
    }
    if (value < 0) {
      event.target.value = 0;
      (this.form as any)[field] = 0;
    }
  }

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: ProviderServiceCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
      city_id: this.form.city_id as number,
      contact: this.form.contact,
      phone: this.form.phone,
      email: this.form.email,
      national: this.form.national,
      iss_percent: this.form.iss_percent,
      service_percent: this.form.service_percent,
      iva_percent: this.form.iva_percent,
      codestur: this.form.codestur,
      payment_method: this.form.payment_method,
    };

    this.providerServiceService.saveProviderService(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Fornecedor salvo com sucesso');
        this.closeModal();
        this.loadProviderServices();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar fornecedor');
        }
        console.error('Erro ao salvar fornecedor:', error);
      },
    });
  }

  deleteProviderService(providerServiceId: number): void {
    this.isLoader = true;
    this.providerServiceService.deleteProviderService(providerServiceId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Fornecedor apagado com sucesso');
        this.loadProviderServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar fornecedor');
        console.error('Erro ao deletar fornecedor:', error);
      },
    });
  }

  activateProviderService(providerServiceId: number): void {
    this.isLoader = true;
    this.providerServiceService.activateProviderService(providerServiceId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Fornecedor ativado com sucesso');
        this.loadProviderServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar fornecedor');
        console.error('Erro ao ativar fornecedor:', error);
      },
    });
  }

  deactivateProviderService(providerServiceId: number): void {
    this.isLoader = true;
    this.providerServiceService.deactivateProviderService(providerServiceId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Fornecedor inativado com sucesso');
        this.loadProviderServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar fornecedor');
        console.error('Erro ao inativar fornecedor:', error);
      },
    });
  }

  isProviderServiceInEdition(providerServiceId: number): boolean {
    return this.inEdition === providerServiceId;
  }
}
