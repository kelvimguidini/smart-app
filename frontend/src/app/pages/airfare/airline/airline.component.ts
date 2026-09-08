import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { AirfareAirlineService, AirfareAirline, AirfareAirlineCreateUpdateRequest } from '../../../services/airfare-airline.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-airline',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './airline.component.html',
  styleUrls: ['./airline.component.scss'],
})
export class AirlineComponent implements OnInit {
  private readonly airlineService: AirfareAirlineService = inject(AirfareAirlineService);
  private readonly toastService: ToastService = inject(ToastService);

  airlines: AirfareAirline[] = [];
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
    this.loadAirlines();
  }

  loadAirlines(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.airlineService.getAirlines(params).subscribe({
      next: (response: any) => {
        this.airlines = response.data || [];
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
        this.toastService.error('Erro ao carregar companhias aéreas');
        console.error('Erro ao carregar companhias aéreas:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadAirlines();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadAirlines();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadAirlines();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadAirlines();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(airline: AirfareAirline): void {
    this.inEdition = airline.id;
    this.form.id = airline.id;
    this.form.name = airline.name;
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

    const data: AirfareAirlineCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };

    this.airlineService.saveAirline(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Companhia aérea salva com sucesso');
        this.closeModal();
        this.loadAirlines();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar companhia aérea');
        }
        console.error('Erro ao salvar companhia aérea:', error);
      },
    });
  }

  deleteAirline(airlineId: number): void {
    this.isLoader = true;
    this.airlineService.deleteAirline(airlineId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Companhia aérea apagada com sucesso');
        this.loadAirlines();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar companhia aérea');
        console.error('Erro ao deletar companhia aérea:', error);
      },
    });
  }

  activateAirline(airlineId: number): void {
    this.isLoader = true;
    this.airlineService.activateAirline(airlineId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Companhia aérea ativada com sucesso');
        this.loadAirlines();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar companhia aérea');
        console.error('Erro ao ativar companhia aérea:', error);
      },
    });
  }

  deactivateAirline(airlineId: number): void {
    this.isLoader = true;
    this.airlineService.deactivateAirline(airlineId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Companhia aérea inativada com sucesso');
        this.loadAirlines();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar companhia aérea');
        console.error('Erro ao inativar companhia aérea:', error);
      },
    });
  }

  isAirlineInEdition(airlineId: number): boolean {
    return this.inEdition === airlineId;
  }
}
