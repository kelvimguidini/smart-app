import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { AirfareBaggageService, AirfareBaggage, AirfareBaggageCreateUpdateRequest } from '../../../services/airfare-baggage.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-baggage',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './baggage.component.html',
  styleUrls: ['./baggage.component.scss'],
})
export class BaggageComponent implements OnInit {
  private readonly baggageService: AirfareBaggageService = inject(AirfareBaggageService);
  private readonly toastService: ToastService = inject(ToastService);

  baggages: AirfareBaggage[] = [];
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
    this.loadBaggages();
  }

  loadBaggages(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.baggageService.getBaggages(params).subscribe({
      next: (response: any) => {
        this.baggages = response.data || [];
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
        this.toastService.error('Erro ao carregar bagagens');
        console.error('Erro ao carregar bagagens:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadBaggages();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadBaggages();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadBaggages();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadBaggages();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(baggage: AirfareBaggage): void {
    this.inEdition = baggage.id;
    this.form.id = baggage.id;
    this.form.name = baggage.name;
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
      this.errors.name = ['A descrição é obrigatória'];
      return false;
    }

    return true;
  }

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: AirfareBaggageCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.baggageService.saveBaggage(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Bagagem salva com sucesso');
        this.closeModal();
        this.loadBaggages();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar bagagem');
        }
        console.error('Erro ao salvar bagagem:', error);
      },
    });
  }

  deleteBaggage(baggageId: number): void {
    this.isLoader = true;
    this.baggageService.deleteBaggage(baggageId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Bagagem apagada com sucesso');
        this.loadBaggages();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar bagagem');
        console.error('Erro ao deletar bagagem:', error);
      },
    });
  }

  activateBaggage(baggageId: number): void {
    this.isLoader = true;
    this.baggageService.activateBaggage(baggageId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Bagagem ativada com sucesso');
        this.loadBaggages();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar bagagem');
        console.error('Erro ao ativar bagagem:', error);
      },
    });
  }

  deactivateBaggage(baggageId: number): void {
    this.isLoader = true;
    this.baggageService.deactivateBaggage(baggageId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Bagagem inativada com sucesso');
        this.loadBaggages();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar bagagem');
        console.error('Erro ao inativar bagagem:', error);
      },
    });
  }

  isBaggageInEdition(baggageId: number): boolean {
    return this.inEdition === baggageId;
  }
}
