import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
 
import { VehicleService, Vehicle, VehicleCreateUpdateRequest } from '../../../services/vehicle.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-vehicle',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent, ModalComponent],
  templateUrl: './vehicle.component.html',
  styleUrls: ['./vehicle.component.scss'],
})
export class VehicleComponent implements OnInit {
  private readonly vehicleService: VehicleService = inject(VehicleService);
  private readonly toastService: ToastService = inject(ToastService);
 
  vehicles: Vehicle[] = [];
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
    this.loadVehicles();
  }
 
  loadVehicles(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };
 
    this.vehicleService.getVehicles(params).subscribe({
      next: (response: any) => {
        this.vehicles = response.data || [];
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
        this.toastService.error('Erro ao carregar tipos de veículo');
        console.error('Erro ao carregar tipos de veículo:', error);
      },
    });
  }
 
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadVehicles();
  }
 
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadVehicles();
  }
 
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadVehicles();
  }
 
  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadVehicles();
  }
 
  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }
 
  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }
 
  edit(vehicle: Vehicle): void {
    this.inEdition = vehicle.id;
    this.form.id = vehicle.id;
    this.form.name = vehicle.name;
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
 
    const data: VehicleCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };
 
    this.vehicleService.saveVehicle(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Tipo de veículo salvo com sucesso');
        this.closeModal();
        this.loadVehicles();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar tipo de veículo');
        }
        console.error('Erro ao salvar tipo de veículo:', error);
      },
    });
  }
 
  deleteVehicle(vehicleId: number): void {
    this.isLoader = true;
    this.vehicleService.deleteVehicle(vehicleId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Tipo de veículo apagado com sucesso');
        this.loadVehicles();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar tipo de veículo');
        console.error('Erro ao deletar tipo de veículo:', error);
      },
    });
  }
 
  activateVehicle(vehicleId: number): void {
    this.isLoader = true;
    this.vehicleService.activateVehicle(vehicleId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Tipo de veículo ativado com sucesso');
        this.loadVehicles();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar tipo de veículo');
        console.error('Erro ao ativar tipo de veículo:', error);
      },
    });
  }
 
  deactivateVehicle(vehicleId: number): void {
    this.isLoader = true;
    this.vehicleService.deactivateVehicle(vehicleId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Tipo de veículo inativado com sucesso');
        this.loadVehicles();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar tipo de veículo');
        console.error('Erro ao inativar tipo de veículo:', error);
      },
    });
  }
 
  isVehicleInEdition(vehicleId: number): boolean {
    return this.inEdition === vehicleId;
  }
}
