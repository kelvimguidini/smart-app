import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap, finalize } from 'rxjs/operators';

import { BrokerService, Broker, BrokerCreateUpdateRequest } from '../../services/broker.service';
import { CityService, City } from '../../services/city.service';
import { ToastService } from '../../services/toast.service';
import { AuthenticatedLayoutComponent } from "../../shared/layouts/authenticated-layout/authenticated-layout.component";
import { ConfirmModalComponent } from "../../shared/components/confirm-modal/confirm-modal.component";
import { DatatableComponent } from "../../shared/components/datatable/datatable.component";
import { AutocompleteComponent } from "../../shared/components/autocomplete/autocomplete.component";
import { NgxMaskDirective } from 'ngx-mask';

@Component({
  selector: 'app-broker',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, AutocompleteComponent, NgxMaskDirective],
  templateUrl: './broker.component.html',
  styleUrls: ['./broker.component.scss']
})
export class BrokerComponent implements OnInit {
  private readonly brokerService: BrokerService = inject(BrokerService);
  private readonly cityService: CityService = inject(CityService);
  private readonly toastService: ToastService = inject(ToastService);

  brokers: Broker[] = [];
  inEdition: number = 0;
  isLoader: boolean = false;
  processing: boolean = false;
  errors: any = {};

  // Pagination and Filtering state
  pagination: any = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  };
  searchQuery: string = '';
  sortColumn: string = 'id';
  sortDirection: string = 'desc';

  selectedCityName: string = '';

  // Autocomplete Functions
  searchCities = (term: string) => this.cityService.searchCities(term);
  displayCity = (city: any) => city ? `${city.name} - ${city.states ? city.states : city.country}` : '';

  form = {
    id: 0,
    name: '',
    city_id: 0,
    contact: '',
    phone: '',
    email: '',
    national: false
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
      sort_direction: this.sortDirection
    };

    this.brokerService.getBrokers(params).subscribe({
      next: (response: any) => {
        this.brokers = response.data || [];
        this.pagination = {
          current_page: response.current_page,
          per_page: response.per_page,
          total: response.total,
          last_page: response.last_page,
          from: response.from,
          to: response.to
        };
        this.isLoader = false;
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao carregar brokers');
        console.error('Erro ao carregar brokers:', error);
      }
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

  edit(broker: Broker): void {
    this.inEdition = broker.id;
    this.form = {
      id: broker.id,
      name: broker.name,
      city_id: broker.city_id,
      contact: broker.contact,
      phone: broker.phone,
      email: broker.email,
      national: broker.national
    };
    
    // Set initial city name for the autocomplete input
    this.selectedCityName = broker.city ? `${broker.city.name} - ${broker.city.states ? broker.city.states : broker.city.country}` : '';
    this.errors = {};
  }

  cancelEdit(): void {
    this.inEdition = 0;
    this.resetForm();
  }

  resetForm(): void {
    this.form = {
      id: 0,
      name: '',
      city_id: 0,
      contact: '',
      phone: '',
      email: '',
      national: false
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
    
    if (!this.form.contact || this.form.contact.trim() === '') {
      this.errors.contact = ['O contato é obrigatório'];
    }

    if (!this.form.phone || this.form.phone.trim() === '') {
      this.errors.phone = ['O telefone é obrigatório'];
    }

    if (!this.form.email || this.form.email.trim() === '') {
      this.errors.email = ['O e-mail é obrigatório'];
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
      this.errors.email = ['O e-mail informado não é válido'];
    }

    if (!this.form.city_id || this.form.city_id <= 0) {
      this.errors.city_id = ['O município é obrigatório. Selecione um município da lista.'];
    }

    return Object.keys(this.errors).length === 0;
  }

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: BrokerCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
      city_id: this.form.city_id,
      contact: this.form.contact,
      phone: this.form.phone,
      email: this.form.email,
      national: this.form.national
    };

    this.brokerService.saveBroker(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Broker salvo com sucesso');
        this.resetForm();
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar broker');
        }
        console.error('Erro ao salvar broker:', error);
      }
    });
  }

  deleteBroker(brokerId: number): void {
    this.isLoader = true;
    this.brokerService.deleteBroker(brokerId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Broker apagado com sucesso');
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar broker');
        console.error('Erro ao deletar broker:', error);
      }
    });
  }

  activateBroker(brokerId: number): void {
    this.isLoader = true;
    this.brokerService.activateBroker(brokerId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Broker ativado com sucesso');
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar broker');
        console.error('Erro ao ativar broker:', error);
      }
    });
  }

  deactivateBroker(brokerId: number): void {
    this.isLoader = true;
    this.brokerService.deactivateBroker(brokerId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Broker inativado com sucesso');
        this.loadBrokers();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar broker');
        console.error('Erro ao inativar broker:', error);
      }
    });
  }

  isBrokerInEdition(brokerId: number): boolean {
    return this.inEdition === brokerId;
  }
}
