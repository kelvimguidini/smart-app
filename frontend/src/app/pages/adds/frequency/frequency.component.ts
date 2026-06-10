import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { FrequencyService, Frequency, FrequencyCreateUpdateRequest } from '../../../services/frequency.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-frequency',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './frequency.component.html',
  styleUrls: ['./frequency.component.scss'],
})
export class FrequencyComponent implements OnInit {
  private readonly frequencyService: FrequencyService = inject(FrequencyService);
  private readonly toastService: ToastService = inject(ToastService);

  frequencies: Frequency[] = [];
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
    this.loadFrequencies();
  }

  loadFrequencies(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.frequencyService.getFrequencies(params).subscribe({
      next: (response: any) => {
        this.frequencies = response.data || [];
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
        this.toastService.error('Erro ao carregar frequências');
        console.error('Erro ao carregar frequências:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadFrequencies();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadFrequencies();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadFrequencies();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadFrequencies();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(frequency: Frequency): void {
    this.inEdition = frequency.id;
    this.form.id = frequency.id;
    this.form.name = frequency.name;
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

    const data: FrequencyCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.frequencyService.saveFrequency(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Frequência salva com sucesso');
        this.closeModal();
        this.loadFrequencies();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar frequência');
        }
        console.error('Erro ao salvar frequência:', error);
      },
    });
  }

  deleteFrequency(frequencyId: number): void {
    this.isLoader = true;
    this.frequencyService.deleteFrequency(frequencyId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Frequência apagada com sucesso');
        this.loadFrequencies();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar frequência');
        console.error('Erro ao deletar frequência:', error);
      },
    });
  }

  activateFrequency(frequencyId: number): void {
    this.isLoader = true;
    this.frequencyService.activateFrequency(frequencyId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Frequência ativada com sucesso');
        this.loadFrequencies();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar frequência');
        console.error('Erro ao ativar frequência:', error);
      },
    });
  }

  deactivateFrequency(frequencyId: number): void {
    this.isLoader = true;
    this.frequencyService.deactivateFrequency(frequencyId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Frequência inativada com sucesso');
        this.loadFrequencies();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar frequência');
        console.error('Erro ao inativar frequência:', error);
      },
    });
  }

  isFrequencyInEdition(frequencyId: number): boolean {
    return this.inEdition === frequencyId;
  }
}
