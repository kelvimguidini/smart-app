import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { BrokerTransportService, BrokerTransport, BrokerTransportCreateUpdateRequest } from '../../../services/broker-trans.service';
import { CityService } from '../../../services/city.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { AutocompleteComponent } from '../../../shared/components/autocomplete/autocomplete.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';
import { NgxMaskDirective } from 'ngx-mask';

@Component({
  selector: 'app-broker-trans',
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
  templateUrl: './broker-trans.component.html',
  styleUrls: ['./broker-trans.component.scss'],
})
export class BrokerTransComponent implements OnInit {
  private readonly brokerTransportService: BrokerTransportService = inject(BrokerTransportService);
  private readonly cityService: CityService = inject(CityService);
  private readonly toastService: ToastService = inject(ToastService);

  brokers: BrokerTransport[] = [];
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
  };

  ngOnInit(): void {
    this.loadBrokers();
  }

  loadBrokers(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.brokerTransportService.getBrokers(params).subscribe({
      next: (response: any) => {
        this.brokers = response.data || [];
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
        this.toastService.error('Erro ao carregar brokers de transporte');
        console.error('Erro ao carregar brokers de transporte:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadBrokers();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadBrokers();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadBrokers();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadBrokers();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(broker: BrokerTransport): void {
    this.inEdition = broker.id;
    this.form = {
      id: broker.id,
      name: broker.name,
      city_id: broker.city_id,
      contact: broker.contact,
      phone: broker.phone,
      email: broker.email,
      national: String(broker.national) === '1' || String(broker.national) === 'true',
    };

    this.selectedCityName = broker.city ? `${broker.city.name} - ${broker.city.states ? broker.city.states : broker.city.country}` : '';
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

    const data: BrokerTransportCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
      city_id: this.form.city_id,
      contact: this.form.contact,
      phone: this.form.phone,
      email: this.form.email,
      national: this.form.national,
    };

    this.brokerTransportService.saveBroker(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Broker de transporte salvo com sucesso');
        this.closeModal();
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar broker de transporte');
        }
        console.error('Erro ao salvar broker de transporte:', error);
      },
    });
  }

  deleteBroker(id: number): void {
    this.isLoader = true;
    this.brokerTransportService.deleteBroker(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Broker de transporte apagado com sucesso');
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar broker de transporte');
        console.error('Erro ao deletar broker de transporte:', error);
      },
    });
  }

  activateBroker(id: number): void {
    this.isLoader = true;
    this.brokerTransportService.activateBroker(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Broker de transporte ativado com sucesso');
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar broker de transporte');
        console.error('Erro ao ativar broker de transporte:', error);
      },
    });
  }

  deactivateBroker(id: number): void {
    this.isLoader = true;
    this.brokerTransportService.deactivateBroker(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Broker de transporte inativado com sucesso');
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar broker de transporte');
        console.error('Erro ao inativar broker de transporte:', error);
      },
    });
  }

  isBrokerInEdition(id: number): boolean {
    return this.inEdition === id;
  }
}
