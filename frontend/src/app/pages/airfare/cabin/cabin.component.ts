import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { AirfareCabinService, AirfareCabin, AirfareCabinCreateUpdateRequest } from '../../../services/airfare-cabin.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-cabin',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './cabin.component.html',
  styleUrls: ['./cabin.component.scss'],
})
export class CabinComponent implements OnInit {
  private readonly cabinService: AirfareCabinService = inject(AirfareCabinService);
  private readonly toastService: ToastService = inject(ToastService);

  cabins: AirfareCabin[] = [];
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
    this.loadCabins();
  }

  loadCabins(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.cabinService.getCabins(params).subscribe({
      next: (response: any) => {
        this.cabins = response.data || [];
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
        this.toastService.error('Erro ao carregar cabines');
        console.error('Erro ao carregar cabines:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadCabins();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadCabins();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadCabins();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadCabins();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(cabin: AirfareCabin): void {
    this.inEdition = cabin.id;
    this.form.id = cabin.id;
    this.form.name = cabin.name;
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
      this.errors.name = ['O nome da cabine é obrigatório'];
      return false;
    }

    return true;
  }

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: AirfareCabinCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.cabinService.saveCabin(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Cabine salva com sucesso');
        this.closeModal();
        this.loadCabins();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar cabine');
        }
        console.error('Erro ao salvar cabine:', error);
      },
    });
  }

  deleteCabin(cabinId: number): void {
    this.isLoader = true;
    this.cabinService.deleteCabin(cabinId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Cabine apagada com sucesso');
        this.loadCabins();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar cabine');
        console.error('Erro ao deletar cabine:', error);
      },
    });
  }

  activateCabin(cabinId: number): void {
    this.isLoader = true;
    this.cabinService.activateCabin(cabinId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Cabine ativada com sucesso');
        this.loadCabins();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar cabine');
        console.error('Erro ao ativar cabine:', error);
      },
    });
  }

  deactivateCabin(cabinId: number): void {
    this.isLoader = true;
    this.cabinService.deactivateCabin(cabinId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Cabine inativada com sucesso');
        this.loadCabins();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar cabine');
        console.error('Erro ao inativar cabine:', error);
      },
    });
  }

  isCabinInEdition(cabinId: number): boolean {
    return this.inEdition === cabinId;
  }
}
