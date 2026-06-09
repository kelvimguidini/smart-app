import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { ServiceHall, ServiceHallCreateUpdateRequest, ServiceHallService } from '../../../services/service-hall.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-service-hall',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './service-hall.component.html',
  styleUrls: ['./service-hall.component.scss'],
})
export class ServiceHallComponent implements OnInit {
  private readonly serviceHallService: ServiceHallService = inject(ServiceHallService);
  private readonly toastService: ToastService = inject(ToastService);

  serviceHalls: ServiceHall[] = [];
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
    this.loadServiceHalls();
  }

  /**
   * Carregar lista
   */
  loadServiceHalls(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.serviceHallService.getServiceHalls(params).subscribe({
      next: (response: any) => {
        this.serviceHalls = response.data || [];
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
        this.toastService.error('Erro ao carregar serviços do salão');
        console.error('Erro ao carregar serviços do salão:', error);
      },
    });
  }

  /**
   * Pesquisar
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadServiceHalls();
  }

  /**
   * Mudar página
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadServiceHalls();
  }

  /**
   * Mudar quantidade por página
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadServiceHalls();
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
    this.loadServiceHalls();
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
   * Editar
   */
  edit(serviceHall: ServiceHall): void {
    this.inEdition = serviceHall.id;
    this.form.id = serviceHall.id;
    this.form.name = serviceHall.name;
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
   * Salvar
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: ServiceHallCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.serviceHallService.saveServiceHall(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Serviço do salão salvo com sucesso');
        this.closeModal();
        this.loadServiceHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar serviço do salão');
        }
        console.error('Erro ao salvar serviço do salão:', error);
      },
    });
  }

  /**
   * Deletar
   */
  deleteServiceHall(serviceHallId: number): void {
    this.isLoader = true;
    this.serviceHallService.deleteServiceHall(serviceHallId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço do salão apagado com sucesso');
        this.loadServiceHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar serviço do salão');
        console.error('Erro ao deletar serviço do salão:', error);
      },
    });
  }

  /**
   * Ativar
   */
  activateServiceHall(serviceHallId: number): void {
    this.isLoader = true;
    this.serviceHallService.activateServiceHall(serviceHallId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço do salão ativado com sucesso');
        this.loadServiceHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar serviço do salão');
        console.error('Erro ao ativar serviço do salão:', error);
      },
    });
  }

  /**
   * Inativar
   */
  deactivateServiceHall(serviceHallId: number): void {
    this.isLoader = true;
    this.serviceHallService.deactivateServiceHall(serviceHallId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço do salão inativado com sucesso');
        this.loadServiceHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar serviço do salão');
        console.error('Erro ao inativar serviço do salão:', error);
      },
    });
  }

  /**
   * Método auxiliar para template
   */
  isServiceHallInEdition(serviceHallId: number): boolean {
    return this.inEdition === serviceHallId;
  }
}
