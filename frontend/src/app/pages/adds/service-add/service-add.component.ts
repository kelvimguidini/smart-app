import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { ServiceAddService, ServiceAdd, ServiceAddCreateUpdateRequest } from '../../../services/service-add.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';

@Component({
  selector: 'app-service-add',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './service-add.component.html',
  styleUrls: ['./service-add.component.scss'],
})
export class ServiceAddComponent implements OnInit {
  private readonly serviceAddService: ServiceAddService = inject(ServiceAddService);
  private readonly toastService: ToastService = inject(ToastService);

  serviceAdds: ServiceAdd[] = [];
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
    this.loadServiceAdds();
  }

  loadServiceAdds(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.serviceAddService.getServiceAdds(params).subscribe({
      next: (response: any) => {
        this.serviceAdds = response.data || [];
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
        this.toastService.error('Erro ao carregar serviços adicionais');
        console.error('Erro ao carregar serviços adicionais:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadServiceAdds();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadServiceAdds();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadServiceAdds();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadServiceAdds();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(serviceAdd: ServiceAdd): void {
    this.inEdition = serviceAdd.id;
    this.form.id = serviceAdd.id;
    this.form.name = serviceAdd.name;
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

    const data: ServiceAddCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.serviceAddService.saveServiceAdd(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Serviço salvo com sucesso');
        this.closeModal();
        this.loadServiceAdds();
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

  deleteServiceAdd(serviceAddId: number): void {
    this.isLoader = true;
    this.serviceAddService.deleteServiceAdd(serviceAddId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço apagado com sucesso');
        this.loadServiceAdds();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar serviço');
        console.error('Erro ao deletar serviço:', error);
      },
    });
  }

  activateServiceAdd(serviceAddId: number): void {
    this.isLoader = true;
    this.serviceAddService.activateServiceAdd(serviceAddId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço ativado com sucesso');
        this.loadServiceAdds();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar serviço');
        console.error('Erro ao ativar serviço:', error);
      },
    });
  }

  deactivateServiceAdd(serviceAddId: number): void {
    this.isLoader = true;
    this.serviceAddService.deactivateServiceAdd(serviceAddId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Serviço inativado com sucesso');
        this.loadServiceAdds();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar serviço');
        console.error('Erro ao inativar serviço:', error);
      },
    });
  }

  isServiceAddInEdition(serviceAddId: number): boolean {
    return this.inEdition === serviceAddId;
  }
}
