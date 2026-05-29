import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
 
import { CarModelService, CarModel, CarModelCreateUpdateRequest } from '../../../services/car-model.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
 
@Component({
  selector: 'app-car-model',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './car-model.component.html',
  styleUrls: ['./car-model.component.scss'],
})
export class CarModelComponent implements OnInit {
  private readonly carModelService: CarModelService = inject(CarModelService);
  private readonly toastService: ToastService = inject(ToastService);
 
  carModels: CarModel[] = [];
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
    this.loadCarModels();
  }
 
  loadCarModels(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };
 
    this.carModelService.getCarModels(params).subscribe({
      next: (response: any) => {
        this.carModels = response.data || [];
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
        this.toastService.error('Erro ao carregar modelos');
        console.error('Erro ao carregar modelos:', error);
      },
    });
  }
 
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadCarModels();
  }
 
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadCarModels();
  }
 
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadCarModels();
  }
 
  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadCarModels();
  }
 
  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }
 
  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }
 
  edit(carModel: CarModel): void {
    this.inEdition = carModel.id;
    this.form.id = carModel.id;
    this.form.name = carModel.name;
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
 
    const data: CarModelCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };
 
    this.carModelService.saveCarModel(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Modelo salvo com sucesso');
        this.closeModal();
        this.loadCarModels();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar modelo');
        }
        console.error('Erro ao salvar modelo:', error);
      },
    });
  }
 
  deleteCarModel(carModelId: number): void {
    this.isLoader = true;
    this.carModelService.deleteCarModel(carModelId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Modelo apagado com sucesso');
        this.loadCarModels();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar modelo');
        console.error('Erro ao deletar modelo:', error);
      },
    });
  }
 
  activateCarModel(carModelId: number): void {
    this.isLoader = true;
    this.carModelService.activateCarModel(carModelId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Modelo ativado com sucesso');
        this.loadCarModels();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar modelo');
        console.error('Erro ao ativar modelo:', error);
      },
    });
  }
 
  deactivateCarModel(carModelId: number): void {
    this.isLoader = true;
    this.carModelService.deactivateCarModel(carModelId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Modelo inativado com sucesso');
        this.loadCarModels();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar modelo');
        console.error('Erro ao inativar modelo:', error);
      },
    });
  }
 
  isCarModelInEdition(carModelId: number): boolean {
    return this.inEdition === carModelId;
  }
}
