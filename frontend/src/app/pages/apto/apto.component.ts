import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { AptoService, Apto, AptoCreateUpdateRequest } from '../../services/apto.service';
import { ToastService } from '../../services/toast.service';
import { AuthenticatedLayoutComponent } from "../../shared/layouts/authenticated-layout/authenticated-layout.component";
import { ConfirmModalComponent } from "../../shared/components/confirm-modal/confirm-modal.component";



import { DatatableComponent } from "../../shared/components/datatable/datatable.component";

@Component({
  selector: 'app-apto',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './apto.component.html',
  styleUrls: ['./apto.component.scss']
})
export class AptoComponent implements OnInit {
  private readonly aptoService: AptoService = inject(AptoService);
  private readonly toastService: ToastService = inject(ToastService);

  aptos: Apto[] = [];
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
    this.loadAptos();
  }

  /**
   * Carregar lista de apartamentos com paginação e filtros
   */
  loadAptos(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection
    };

    this.aptoService.getAptos(params).subscribe({
      next: (response: any) => {
        this.aptos = response.data || [];
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
        this.toastService.error('Erro ao carregar apartamentos');
        console.error('Erro ao carregar aptos:', error);
      }
    });
  }

  /**
   * Pesquisar (chamado pelo DatatableComponent)
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadAptos();
  }

  /**
   * Mudar página (chamado pelo DatatableComponent)
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadAptos();
  }

  /**
   * Mudar quantidade por página (chamado pelo DatatableComponent)
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadAptos();
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
    this.loadAptos();
  }

  /**
   * Editar apartamento
   */
  edit(apto: Apto): void {
    this.inEdition = apto.id;
    this.form.id = apto.id;
    this.form.name = apto.name;
    this.errors = {};
  }

  /**
   * Cancelar edição
   */
  cancelEdit(): void {
    this.inEdition = 0;
    this.resetForm();
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
   * Salvar apartamento (criar ou atualizar)
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: AptoCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name
    };

    this.aptoService.saveApto(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Apartamento salvo com sucesso');
        this.resetForm();
        this.loadAptos();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar apartamento');
        }
        console.error('Erro ao salvar apto:', error);
      }
    });
  }

  /**
   * Deletar apartamento
   */
  deleteApto(aptoId: number): void {
    this.isLoader = true;
    this.aptoService.deleteApto(aptoId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Apartamento apagado com sucesso');
        this.loadAptos();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar apartamento');
        console.error('Erro ao deletar apto:', error);
      }
    });
  }

  /**
   * Ativar apartamento
   */
  activateApto(aptoId: number): void {
    this.isLoader = true;
    this.aptoService.activateApto(aptoId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Apartamento ativado com sucesso');
        this.loadAptos();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar apartamento');
        console.error('Erro ao ativar apto:', error);
      }
    });
  }

  /**
   * Inativar apartamento
   */
  deactivateApto(aptoId: number): void {
    this.isLoader = true;
    this.aptoService.deactivateApto(aptoId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Apartamento inativado com sucesso');
        this.loadAptos();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar apartamento');
        console.error('Erro ao inativar apto:', error);
      }
    });
  }

  /**
   * Método auxiliar para template
   */
  isAptoInEdition(aptoId: number): boolean {
    return this.inEdition === aptoId;
  }
}
