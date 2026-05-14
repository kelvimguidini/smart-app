import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { PurposeService, Purpose, PurposeCreateUpdateRequest } from '../../services/purpose.service';
import { ToastService } from '../../services/toast.service';
import { AuthenticatedLayoutComponent } from "../../shared/layouts/authenticated-layout/authenticated-layout.component";
import { ConfirmModalComponent } from "../../shared/components/confirm-modal/confirm-modal.component";
import { DatatableComponent } from "../../shared/components/datatable/datatable.component";

@Component({
  selector: 'app-purpose',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './purpose.component.html',
  styleUrls: ['./purpose.component.scss']
})
export class PurposeComponent implements OnInit {
  private readonly purposeService: PurposeService = inject(PurposeService);
  private readonly toastService: ToastService = inject(ToastService);

  purposes: Purpose[] = [];
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
    this.loadPurposes();
  }

  /**
   * Carregar lista de propósitos com paginação e filtros
   */
  loadPurposes(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection
    };

    this.purposeService.getPurposes(params).subscribe({
      next: (response: any) => {
        this.purposes = response.data || [];
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
        this.toastService.error('Erro ao carregar propósitos');
        console.error('Erro ao carregar propósitos:', error);
      }
    });
  }

  /**
   * Pesquisar (chamado pelo DatatableComponent)
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadPurposes();
  }

  /**
   * Mudar página (chamado pelo DatatableComponent)
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadPurposes();
  }

  /**
   * Mudar quantidade por página (chamado pelo DatatableComponent)
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadPurposes();
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
    this.loadPurposes();
  }

  /**
   * Editar propósito
   */
  edit(purpose: Purpose): void {
    this.inEdition = purpose.id;
    this.form.id = purpose.id;
    this.form.name = purpose.name;
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
   * Salvar propósito (criar ou atualizar)
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: PurposeCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name
    };

    this.purposeService.savePurpose(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Propósito salvo com sucesso');
        this.resetForm();
        this.loadPurposes();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar propósito');
        }
        console.error('Erro ao salvar propósito:', error);
      }
    });
  }

  /**
   * Deletar propósito
   */
  deletePurpose(purposeId: number): void {
    this.isLoader = true;
    this.purposeService.deletePurpose(purposeId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Propósito apagado com sucesso');
        this.loadPurposes();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar propósito');
        console.error('Erro ao deletar propósito:', error);
      }
    });
  }

  /**
   * Ativar propósito
   */
  activatePurpose(purposeId: number): void {
    this.isLoader = true;
    this.purposeService.activatePurpose(purposeId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Propósito ativado com sucesso');
        this.loadPurposes();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar propósito');
        console.error('Erro ao ativar propósito:', error);
      }
    });
  }

  /**
   * Inativar propósito
   */
  deactivatePurpose(purposeId: number): void {
    this.isLoader = true;
    this.purposeService.deactivatePurpose(purposeId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Propósito inativado com sucesso');
        this.loadPurposes();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar propósito');
        console.error('Erro ao inativar propósito:', error);
      }
    });
  }

  /**
   * Método auxiliar para template
   */
  isPurposeInEdition(purposeId: number): boolean {
    return this.inEdition === purposeId;
  }
}
