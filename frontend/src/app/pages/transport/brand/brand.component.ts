import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
 
import { BrandService, Brand, BrandCreateUpdateRequest } from '../../../services/brand.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
 
@Component({
  selector: 'app-brand',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './brand.component.html',
  styleUrls: ['./brand.component.scss'],
})
export class BrandComponent implements OnInit {
  private readonly brandService: BrandService = inject(BrandService);
  private readonly toastService: ToastService = inject(ToastService);
 
  brands: Brand[] = [];
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
    this.loadBrands();
  }
 
  loadBrands(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };
 
    this.brandService.getBrands(params).subscribe({
      next: (response: any) => {
        this.brands = response.data || [];
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
        this.toastService.error('Erro ao carregar marcas');
        console.error('Erro ao carregar marcas:', error);
      },
    });
  }
 
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadBrands();
  }
 
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadBrands();
  }
 
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadBrands();
  }
 
  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadBrands();
  }
 
  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }
 
  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }
 
  edit(brand: Brand): void {
    this.inEdition = brand.id;
    this.form.id = brand.id;
    this.form.name = brand.name;
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
 
    const data: BrandCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
    };
 
    this.brandService.saveBrand(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Marca salva com sucesso');
        this.closeModal();
        this.loadBrands();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar marca');
        }
        console.error('Erro ao salvar marca:', error);
      },
    });
  }
 
  deleteBrand(brandId: number): void {
    this.isLoader = true;
    this.brandService.deleteBrand(brandId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Marca apagada com sucesso');
        this.loadBrands();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar marca');
        console.error('Erro ao deletar marca:', error);
      },
    });
  }
 
  activateBrand(brandId: number): void {
    this.isLoader = true;
    this.brandService.activateBrand(brandId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Marca ativada com sucesso');
        this.loadBrands();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar marca');
        console.error('Erro ao ativar marca:', error);
      },
    });
  }
 
  deactivateBrand(brandId: number): void {
    this.isLoader = true;
    this.brandService.deactivateBrand(brandId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Marca inativada com sucesso');
        this.loadBrands();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar marca');
        console.error('Erro ao inativar marca:', error);
      },
    });
  }
 
  isBrandInEdition(brandId: number): boolean {
    return this.inEdition === brandId;
  }
}
