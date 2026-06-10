import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { ServiceService, Service, ServiceCreateUpdateRequest } from '../../../services/service.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-service',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './service.component.html',
  styleUrls: ['./service.component.scss'],
})
export class ServiceComponent implements OnInit {
  private readonly serviceService: ServiceService = inject(ServiceService);
  private readonly toastService: ToastService = inject(ToastService);

  services: Service[] = [];
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
    this.loadServices();
  }

  /**
   * Carregar lista de tipos de serviço com paginação e filtros
   */
  loadServices(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.serviceService.getServices(params).subscribe({
      next: (response: any) => {
        this.services = response.data || [];
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
        this.toastService.error('Erro ao carregar serviços');
        console.error('Erro ao carregar serviços:', error);
      },
    });
  }

  /**
   * Pesquisar (chamado pelo DatatableComponent)
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadServices();
  }

  /**
   * Mudar página (chamado pelo DatatableComponent)
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadServices();
  }

  /**
   * Mudar quantidade por página (chamado pelo DatatableComponent)
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadServices();
  }

  /**
   * Ordenar coluna
   */
  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadServices();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  /**
   * Editar serviço
   */
  edit(service: Service): void {
    this.inEdition = service.id;
    this.form.id = service.id;
    this.form.name = service.name;
    this.errors = {};
    this.showModal = true;
  }

  cancelEdit(): void {
    this.closeModal();
  }

  /**
   * Resetar formulário
   */
  resetForm(): void {
    this.form = {
      id: 0,
      name: '',
    };
    this.errors = {};
    this.inEdition = 0;
  }

  /**
   * Validar formulário
   */
  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
      return false;
    }

    return true;
  }

  /**
   * Salvar serviço (criar ou atualizar)
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: ServiceCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.serviceService.saveService(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Serviço salvo com sucesso');
        this.closeModal();
        this.loadServices();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar serviço');
        }
        console.error('Erro ao salvar serviço:', error);
      },
    });
  }

  /**
   * Deletar serviço
   */
  deleteService(serviceId: number): void {
    this.isLoader = true;
    this.serviceService.deleteService(serviceId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço apagado com sucesso');
        this.loadServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar serviço');
        console.error('Erro ao deletar serviço:', error);
      },
    });
  }

  /**
   * Ativar serviço
   */
  activateService(serviceId: number): void {
    this.isLoader = true;
    this.serviceService.activateService(serviceId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço ativado com sucesso');
        this.loadServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar serviço');
        console.error('Erro ao ativar serviço:', error);
      },
    });
  }

  /**
   * Inativar serviço
   */
  deactivateService(serviceId: number): void {
    this.isLoader = true;
    this.serviceService.deactivateService(serviceId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço inativado com sucesso');
        this.loadServices();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar serviço');
        console.error('Erro ao inativar serviço:', error);
      },
    });
  }

  /**
   * Método auxiliar para template
   */
  isServiceInEdition(serviceId: number): boolean {
    return this.inEdition === serviceId;
  }
}
