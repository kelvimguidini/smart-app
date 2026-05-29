import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { LocalService, Local, LocalCreateUpdateRequest } from '../../../services/local.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';

@Component({
  selector: 'app-local',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './local.component.html',
  styleUrls: ['./local.component.scss'],
})
export class LocalComponent implements OnInit {
  private readonly localService: LocalService = inject(LocalService);
  private readonly toastService: ToastService = inject(ToastService);

  locals: Local[] = [];
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
    this.loadLocals();
  }

  /**
   * Carregar lista de tipos de serviço com paginação e filtros
   */
  loadLocals(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.localService.getLocals(params).subscribe({
      next: (response: any) => {
        this.locals = response.data || [];
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
        this.toastService.error('Erro ao carregar locais');
        console.error('Erro ao carregar locais:', error);
      },
    });
  }

  /**
   * Pesquisar (chamado pelo DatatableComponent)
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadLocals();
  }

  /**
   * Mudar página (chamado pelo DatatableComponent)
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadLocals();
  }

  /**
   * Mudar quantidade por página (chamado pelo DatatableComponent)
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadLocals();
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
    this.loadLocals();
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
   * Editar local
   */
  edit(local: Local): void {
    this.inEdition = local.id;
    this.form.id = local.id;
    this.form.name = local.name;
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
   * Salvar local (criar ou atualizar)
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: LocalCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.localService.saveLocal(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Local salvo com sucesso');
        this.closeModal();
        this.loadLocals();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar local');
        }
        console.error('Erro ao salvar local:', error);
      },
    });
  }

  /**
   * Deletar local
   */
  deleteLocal(localId: number): void {
    this.isLoader = true;
    this.localService.deleteLocal(localId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Local apagado com sucesso');
        this.loadLocals();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar local');
        console.error('Erro ao deletar local:', error);
      },
    });
  }

  /**
   * Ativar local
   */
  activateLocal(localId: number): void {
    this.isLoader = true;
    this.localService.activateLocal(localId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Local ativado com sucesso');
        this.loadLocals();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar local');
        console.error('Erro ao ativar local:', error);
      },
    });
  }

  /**
   * Inativar local
   */
  deactivateLocal(localId: number): void {
    this.isLoader = true;
    this.localService.deactivateLocal(localId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Local inativado com sucesso');
        this.loadLocals();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar local');
        console.error('Erro ao inativar local:', error);
      },
    });
  }

  /**
   * Método auxiliar para template
   */
  isLocalInEdition(localId: number): boolean {
    return this.inEdition === localId;
  }
}
