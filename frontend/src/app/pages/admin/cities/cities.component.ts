import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CityService, City } from '../../../services/city.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ToastService } from '../../../services/toast.service';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-cities',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, DatatableComponent, ConfirmModalComponent, ModalComponent],
  templateUrl: './cities.component.html',
  styleUrls: ['./cities.component.scss'],
})
export class CitiesComponent implements OnInit {
  private readonly cityService = inject(CityService);
  private readonly toastr = inject(ToastService);

  cities: City[] = [];

  // Pagination and Filtering
  currentPage = 1;
  lastPage = 1;
  totalItems = 0;
  perPage = 10;
  searchTerm = '';
  sortColumn = 'name';
  sortDirection = 'asc';

  // Form State
  form: { id: number; name: string; states: string; country: string } = {
    id: 0,
    name: '',
    states: '',
    country: '',
  };
  inEdition = 0;
  processing = false;
  isLoader = false;
  showModal = false;
  errors: any = {};

  ngOnInit(): void {
    this.loadCities();
  }

  loadCities(): void {
    this.isLoader = true;
    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchTerm,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.cityService.getCities(params).subscribe({
      next: (response) => {
        this.cities = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.totalItems = response.total;
        this.isLoader = false;
      },
      error: (err) => {
        this.toastr.error('Erro ao carregar cidades');
        this.isLoader = false;
        console.error(err);
      },
    });
  }

  // Datatable Events
  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadCities();
  }

  onSearch(term: string): void {
    this.searchTerm = term;
    this.currentPage = 1;
    this.loadCities();
  }

  onSort(event: { column: string; direction: string }): void {
    this.sortColumn = event.column;
    this.sortDirection = event.direction;
    this.loadCities();
  }

  // Form Actions
  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  resetForm(): void {
    this.inEdition = 0;
    this.form = { id: 0, name: '', states: '', country: '' };
    this.processing = false;
    this.errors = {};
  }

  edit(city: City): void {
    this.inEdition = city.id;
    this.form = { id: city.id, name: city.name, states: city.states || '', country: city.country || '' };
    this.errors = {};
    this.showModal = true;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
    }

    if (!this.form.country || this.form.country.trim() === '') {
      this.errors.country = ['O país é obrigatório'];
    }

    return Object.keys(this.errors).length === 0;
  }

  save(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;
    this.cityService.saveCity(this.form).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Cidade salva com sucesso');
        this.closeModal();
        this.loadCities();
      },
      error: (err) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastr.error('Erro ao salvar cidade');
        }
        console.error(err);
      },
    });
  }

  // Action Actions
  activate(id: number): void {
    this.processing = true;
    this.cityService.activateCity(id).subscribe({
      next: () => {
        this.toastr.success('Cidade ativada com sucesso');
        this.loadCities();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao ativar cidade');
        this.processing = false;
      },
    });
  }

  deactivate(id: number): void {
    this.processing = true;
    this.cityService.deactivateCity(id).subscribe({
      next: () => {
        this.toastr.success('Cidade inativada com sucesso');
        this.loadCities();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao inativar cidade');
        this.processing = false;
      },
    });
  }

  deleteCity(id: number): void {
    this.processing = true;
    this.cityService.deleteCity(id).subscribe({
      next: () => {
        this.toastr.success('Cidade apagada com sucesso');
        this.loadCities();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao apagar cidade');
        this.processing = false;
      },
    });
  }
}
