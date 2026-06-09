import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { TransportServiceService, TransportService, TransportServiceCreateUpdateRequest } from '../../../services/transport-service.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-transport-service',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './transport-service.component.html',
  styleUrls: ['./transport-service.component.scss'],
})
export class TransportServiceComponent implements OnInit {
  private readonly transportServiceService: TransportServiceService = inject(TransportServiceService);
  private readonly toastService: ToastService = inject(ToastService);

  transportServices: TransportService[] = [];
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

  form = {
    id: 0,
    name: '',
  };

  ngOnInit(): void {
    this.loadTransportServices();
  }

  loadTransportServices(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.transportServiceService.getTransportServices(params).subscribe({
      next: (response: any) => {
        this.transportServices = response.data || [];
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
        this.toastService.error('Erro ao carregar serviços de transporte');
        console.error('Erro ao carregar serviços de transporte:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadTransportServices();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadTransportServices();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadTransportServices();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadTransportServices();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(service: TransportService): void {
    this.inEdition = service.id;
    this.form.id = service.id;
    this.form.name = service.name;
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
    };
    this.errors = {};
    this.inEdition = 0;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
      return false;
    }

    return true;
  }

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: TransportServiceCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.transportServiceService.saveTransportService(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Serviço de transporte salvo com sucesso');
        this.closeModal();
        this.loadTransportServices();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar serviço de transporte');
        }
        console.error('Erro ao salvar serviço de transporte:', error);
      },
    });
  }

  deleteTransportService(id: number): void {
    this.isLoader = true;
    this.transportServiceService.deleteTransportService(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço de transporte apagado com sucesso');
        this.loadTransportServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar serviço de transporte');
        console.error('Erro ao deletar serviço de transporte:', error);
      },
    });
  }

  activateTransportService(id: number): void {
    this.isLoader = true;
    this.transportServiceService.activateTransportService(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço de transporte ativado com sucesso');
        this.loadTransportServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar serviço de transporte');
        console.error('Erro ao ativar serviço de transporte:', error);
      },
    });
  }

  deactivateTransportService(id: number): void {
    this.isLoader = true;
    this.transportServiceService.deactivateTransportService(id).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço de transporte inativado com sucesso');
        this.loadTransportServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar serviço de transporte');
        console.error('Erro ao inativar serviço de transporte:', error);
      },
    });
  }

  isTransportServiceInEdition(id: number): boolean {
    return this.inEdition === id;
  }
}
