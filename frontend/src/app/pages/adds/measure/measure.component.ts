import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { MeasureService, Measure, MeasureCreateUpdateRequest } from '../../../services/measure.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-measure',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './measure.component.html',
  styleUrls: ['./measure.component.scss'],
})
export class MeasureComponent implements OnInit {
  private readonly measureService: MeasureService = inject(MeasureService);
  private readonly toastService: ToastService = inject(ToastService);

  measures: Measure[] = [];
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
    this.loadMeasures();
  }

  loadMeasures(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.measureService.getMeasures(params).subscribe({
      next: (response: any) => {
        this.measures = response.data || [];
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
        this.toastService.error('Erro ao carregar medidas');
        console.error('Erro ao carregar medidas:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadMeasures();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadMeasures();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadMeasures();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadMeasures();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(measure: Measure): void {
    this.inEdition = measure.id;
    this.form.id = measure.id;
    this.form.name = measure.name;
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

    const data: MeasureCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.measureService.saveMeasure(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Medida salva com sucesso');
        this.closeModal();
        this.loadMeasures();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar medida');
        }
        console.error('Erro ao salvar medida:', error);
      },
    });
  }

  deleteMeasure(measureId: number): void {
    this.isLoader = true;
    this.measureService.deleteMeasure(measureId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Medida apagada com sucesso');
        this.loadMeasures();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar medida');
        console.error('Erro ao deletar medida:', error);
      },
    });
  }

  activateMeasure(measureId: number): void {
    this.isLoader = true;
    this.measureService.activateMeasure(measureId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Medida ativada com sucesso');
        this.loadMeasures();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar medida');
        console.error('Erro ao ativar medida:', error);
      },
    });
  }

  deactivateMeasure(measureId: number): void {
    this.isLoader = true;
    this.measureService.deactivateMeasure(measureId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Medida inativada com sucesso');
        this.loadMeasures();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar medida');
        console.error('Erro ao inativar medida:', error);
      },
    });
  }

  isMeasureInEdition(measureId: number): boolean {
    return this.inEdition === measureId;
  }
}
