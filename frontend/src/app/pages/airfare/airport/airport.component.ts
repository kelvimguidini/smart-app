import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { AirfareAirportService, AirfareAirport, AirfareAirportCreateUpdateRequest } from '../../../services/airfare-airport.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-airport',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './airport.component.html',
  styleUrls: ['./airport.component.scss'],
})
export class AirportComponent implements OnInit {
  private readonly airportService: AirfareAirportService = inject(AirfareAirportService);
  private readonly toastService: ToastService = inject(ToastService);

  airports: AirfareAirport[] = [];
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
  sortColumn: string = 'iata_code';
  sortDirection: string = 'asc';

  form = {
    id: 0,
    iata_code: '',
    name: '',
    city: '',
    state: '',
    country: 'Brasil',
  };

  ngOnInit(): void {
    this.loadAirports();
  }

  loadAirports(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.airportService.getAirports(params).subscribe({
      next: (response: any) => {
        this.airports = response.data || [];
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
        this.toastService.error('Erro ao carregar aeroportos');
        console.error('Erro ao carregar aeroportos:', error);
      },
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadAirports();
  }

  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadAirports();
  }

  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadAirports();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadAirports();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  edit(airport: AirfareAirport): void {
    this.inEdition = airport.id;
    this.form.id = airport.id;
    this.form.iata_code = airport.iata_code;
    this.form.name = airport.name;
    this.form.city = airport.city;
    this.form.state = airport.state || '';
    this.form.country = airport.country || 'Brasil';
    this.errors = {};
    this.showModal = true;
  }

  cancelEdit(): void {
    this.closeModal();
  }

  resetForm(): void {
    this.form = {
      id: 0,
      iata_code: '',
      name: '',
      city: '',
      state: '',
      country: 'Brasil',
    };
    this.errors = {};
    this.inEdition = 0;
  }

  private validateForm(): boolean {
    this.errors = {};
    let isValid = true;

    if (!this.form.iata_code || this.form.iata_code.trim() === '') {
      this.errors.iata_code = ['O código IATA é obrigatório'];
      isValid = false;
    } else if (this.form.iata_code.trim().length !== 3) {
      this.errors.iata_code = ['O código IATA deve ter exatamente 3 letras'];
      isValid = false;
    }

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome do aeroporto é obrigatório'];
      isValid = false;
    }

    if (!this.form.city || this.form.city.trim() === '') {
      this.errors.city = ['A cidade é obrigatória'];
      isValid = false;
    }

    return isValid;
  }

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: AirfareAirportCreateUpdateRequest = {
      id: this.form.id,
      iata_code: this.form.iata_code.trim().toUpperCase(),
      name: this.form.name.trim(),
      city: this.form.city.trim(),
      state: this.form.state ? this.form.state.trim().toUpperCase() : undefined,
      country: this.form.country ? this.form.country.trim() : 'Brasil',
    };

    this.airportService.saveAirport(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Aeroporto salvo com sucesso');
        this.closeModal();
        this.loadAirports();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
          if (error.error.message && !this.errors.iata_code && !this.errors.name && !this.errors.city) {
            this.toastService.error(error.error.message);
          }
        } else {
          this.toastService.error('Erro ao salvar aeroporto');
        }
        console.error('Erro ao salvar aeroporto:', error);
      },
    });
  }

  deleteAirport(airportId: number): void {
    this.isLoader = true;
    this.airportService.deleteAirport(airportId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Aeroporto apagado com sucesso');
        this.loadAirports();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar aeroporto');
        console.error('Erro ao deletar aeroporto:', error);
      },
    });
  }

  activateAirport(airportId: number): void {
    this.isLoader = true;
    this.airportService.activateAirport(airportId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Aeroporto ativado com sucesso');
        this.loadAirports();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar aeroporto');
        console.error('Erro ao ativar aeroporto:', error);
      },
    });
  }

  deactivateAirport(airportId: number): void {
    this.isLoader = true;
    this.airportService.deactivateAirport(airportId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Aeroporto inativado com sucesso');
        this.loadAirports();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar aeroporto');
        console.error('Erro ao inativar aeroporto:', error);
      },
    });
  }

  isAirportInEdition(airportId: number): boolean {
    return this.inEdition === airportId;
  }
}
