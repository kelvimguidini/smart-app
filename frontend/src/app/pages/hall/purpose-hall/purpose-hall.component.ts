import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { PurposeHallService, PurposeHall, PurposeHallCreateUpdateRequest } from '../../../services/purpose-hall.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';

@Component({
  selector: 'app-purpose-hall',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './purpose-hall.component.html',
  styleUrls: ['./purpose-hall.component.scss'],
})
export class PurposeHallComponent implements OnInit {
  private readonly purposeHallService: PurposeHallService = inject(PurposeHallService);
  private readonly toastService: ToastService = inject(ToastService);

  purposeHalls: PurposeHall[] = [];
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
    this.loadPurposeHalls();
  }

  /**
   * Carregar lista
   */
  loadPurposeHalls(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.purposeHallService.getPurposeHalls(params).subscribe({
      next: (response: any) => {
        this.purposeHalls = response.data || [];
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
        this.toastService.error('Erro ao carregar propósitos do salão');
        console.error('Erro ao carregar propósitos do salão:', error);
      },
    });
  }

  /**
   * Pesquisar
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadPurposeHalls();
  }

  /**
   * Mudar página
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadPurposeHalls();
  }

  /**
   * Mudar quantidade por página
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadPurposeHalls();
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
    this.loadPurposeHalls();
  }

  /**
   * Editar
   */
  edit(purposeHall: PurposeHall): void {
    this.inEdition = purposeHall.id;
    this.form.id = purposeHall.id;
    this.form.name = purposeHall.name;
    this.errors = {};
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

    const data: PurposeHallCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.purposeHallService.savePurposeHall(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Propósito do salão salvo com sucesso');
        this.resetForm();
        this.loadPurposeHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar propósito do salão');
        }
        console.error('Erro ao salvar propósito do salão:', error);
      },
    });
  }

  /**
   * Deletar
   */
  deletePurposeHall(purposeHallId: number): void {
    this.isLoader = true;
    this.purposeHallService.deletePurposeHall(purposeHallId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Propósito do salão apagado com sucesso');
        this.loadPurposeHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar propósito do salão');
        console.error('Erro ao deletar propósito do salão:', error);
      },
    });
  }

  /**
   * Ativar
   */
  activatePurposeHall(purposeHallId: number): void {
    this.isLoader = true;
    this.purposeHallService.activatePurposeHall(purposeHallId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Propósito do salão ativado com sucesso');
        this.loadPurposeHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar propósito do salão');
        console.error('Erro ao ativar propósito do salão:', error);
      },
    });
  }

  /**
   * Inativar
   */
  deactivatePurposeHall(purposeHallId: number): void {
    this.isLoader = true;
    this.purposeHallService.deactivatePurposeHall(purposeHallId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Propósito do salão inativado com sucesso');
        this.loadPurposeHalls();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar propósito do salão');
        console.error('Erro ao inativar propósito do salão:', error);
      },
    });
  }

  /**
   * Método auxiliar para template
   */
  isPurposeHallInEdition(purposeHallId: number): boolean {
    return this.inEdition === purposeHallId;
  }
}
