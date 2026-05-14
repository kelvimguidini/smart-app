import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { ServiceTypeService, ServiceType, ServiceTypeCreateUpdateRequest } from '../../services/service-type.service';
import { ToastService } from '../../services/toast.service';
import { AuthenticatedLayoutComponent } from "../../shared/layouts/authenticated-layout/authenticated-layout.component";
import { ConfirmModalComponent } from "../../shared/components/confirm-modal/confirm-modal.component";
import { DatatableComponent } from "../../shared/components/datatable/datatable.component";

@Component({
  selector: 'app-service-type',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './service-type.component.html',
  styleUrls: ['./service-type.component.scss']
})
export class ServiceTypeComponent implements OnInit {
  private readonly serviceTypeService: ServiceTypeService = inject(ServiceTypeService);
  private readonly toastService: ToastService = inject(ToastService);

  serviceTypes: ServiceType[] = [];
  inEdition: number = 0;
  isLoader: boolean = false;
  processing: boolean = false;
  errors: any = {};

  // Pagination and Filtering state
  pagination: any = {
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  };
  searchQuery: string = '';
  sortColumn: string = 'id';
  sortDirection: string = 'desc';

  form = {
    id: 0,
    name: ''
  };

  ngOnInit(): void {
    this.loadServiceTypes();
  }

  /**
   * Carregar lista de tipos de serviço com paginação e filtros
   */
  loadServiceTypes(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection
    };

    this.serviceTypeService.getServiceTypes(params).subscribe({
      next: (response: any) => {
        this.serviceTypes = response.data || [];
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
        this.toastService.error('Erro ao carregar tipos de serviço');
        console.error('Erro ao carregar tipos de serviço:', error);
      }
    });
  }

  /**
   * Pesquisar (chamado pelo DatatableComponent)
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadServiceTypes();
  }

  /**
   * Mudar página (chamado pelo DatatableComponent)
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadServiceTypes();
  }

  /**
   * Mudar quantidade por página (chamado pelo DatatableComponent)
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadServiceTypes();
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
    this.loadServiceTypes();
  }

  /**
   * Editar tipo de serviço
   */
  edit(serviceType: ServiceType): void {
    this.inEdition = serviceType.id;
    this.form.id = serviceType.id;
    this.form.name = serviceType.name;
    this.errors = {};
  }

  /**
   * Resetar formulário
   */
  resetForm(): void {
    this.form = {
      id: 0,
      name: ''
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
   * Salvar tipo de serviço (criar ou atualizar)
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: ServiceTypeCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name
    };

    this.serviceTypeService.saveServiceType(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Tipo de serviço salvo com sucesso');
        this.resetForm();
        this.loadServiceTypes();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar tipo de serviço');
        }
        console.error('Erro ao salvar tipo de serviço:', error);
      }
    });
  }

  /**
   * Deletar tipo de serviço
   */
  deleteServiceType(serviceTypeId: number): void {
    this.isLoader = true;
    this.serviceTypeService.deleteServiceType(serviceTypeId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Tipo de serviço apagado com sucesso');
        this.loadServiceTypes();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar tipo de serviço');
        console.error('Erro ao deletar tipo de serviço:', error);
      }
    });
  }

  /**
   * Ativar tipo de serviço
   */
  activateServiceType(serviceTypeId: number): void {
    this.isLoader = true;
    this.serviceTypeService.activateServiceType(serviceTypeId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Tipo de serviço ativado com sucesso');
        this.loadServiceTypes();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar tipo de serviço');
        console.error('Erro ao ativar tipo de serviço:', error);
      }
    });
  }

  /**
   * Inativar tipo de serviço
   */
  deactivateServiceType(serviceTypeId: number): void {
    this.isLoader = true;
    this.serviceTypeService.deactivateServiceType(serviceTypeId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Tipo de serviço inativado com sucesso');
        this.loadServiceTypes();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar tipo de serviço');
        console.error('Erro ao inativar tipo de serviço:', error);
      }
    });
  }

  /**
   * Método auxiliar para template
   */
  isServiceTypeInEdition(serviceTypeId: number): boolean {
    return this.inEdition === serviceTypeId;
  }
}
