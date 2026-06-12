import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CustomerMetadataService, CustomerMetadata } from '../../../services/customer-metadata.service';
import { CustomerService, Customer } from '../../../services/customer.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ToastService } from '../../../services/toast.service';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-customer-metadata',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, DatatableComponent, ConfirmModalComponent, ModalComponent],
  templateUrl: './customer-metadata.component.html',
  styleUrls: ['./customer-metadata.component.scss'],
})
export class CustomerMetadataComponent implements OnInit {
  private readonly metadataService = inject(CustomerMetadataService);
  private readonly customerService = inject(CustomerService);
  private readonly toastr = inject(ToastService);

  metadataList: CustomerMetadata[] = [];
  customers: Customer[] = [];

  // Pagination and Filtering
  currentPage = 1;
  lastPage = 1;
  totalItems = 0;
  perPage = 10;
  searchTerm = '';
  sortColumn = 'value';
  sortDirection = 'asc';

  // Filters
  filterCustomer = '';
  filterType = '';

  // Form State
  form = {
    id: 0,
    customer_id: 0,
    type: 'requester' as 'requester' | 'sector' | 'cost_center',
    value: '',
  };

  inEdition = 0;
  processing = false;
  isLoader = false;
  showModal = false;
  errors: any = {};

  typeLabels: { [key: string]: string } = {
    requester: 'Solicitante',
    sector: 'Setor',
    cost_center: 'Centro de Custo',
  };

  ngOnInit(): void {
    this.loadMetadata();
    this.loadCustomers();
  }

  loadMetadata(): void {
    this.isLoader = true;
    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchTerm,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
      customer_id: this.filterCustomer,
      type: this.filterType,
    };

    this.metadataService.getMetadataList(params).subscribe({
      next: (response) => {
        this.metadataList = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.totalItems = response.total;
        this.isLoader = false;
      },
      error: (err: any) => {
        this.toastr.error('Erro ao carregar metadados');
        this.isLoader = false;
        console.error(err);
      },
    });
  }

  loadCustomers(): void {
    this.customerService.getCustomers({ per_page: 500 }).subscribe({
      next: (res) => {
        this.customers = res.data;
      },
      error: (err: any) => console.error(err),
    });
  }

  onFilterChange(): void {
    this.currentPage = 1;
    this.loadMetadata();
  }

  // Datatable Events
  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadMetadata();
  }

  onSearch(term: string): void {
    this.searchTerm = term;
    this.currentPage = 1;
    this.loadMetadata();
  }

  onSort(event: { column: string; direction: string }): void {
    this.sortColumn = event.column;
    this.sortDirection = event.direction;
    this.loadMetadata();
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
    this.form = {
      id: 0,
      customer_id: this.customers[0]?.id || 0,
      type: 'requester',
      value: '',
    };
    this.processing = false;
    this.errors = {};
  }

  edit(meta: CustomerMetadata): void {
    this.inEdition = meta.id;
    this.form = {
      id: meta.id,
      customer_id: meta.customer_id,
      type: meta.type,
      value: meta.value,
    };
    this.errors = {};
    this.showModal = true;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.value || this.form.value.trim() === '') {
      this.errors.value = ['O valor é obrigatório'];
    }

    if (!this.form.customer_id || this.form.customer_id <= 0) {
      this.errors.customer_id = ['O cliente é obrigatório'];
    }

    if (!this.form.type) {
      this.errors.type = ['O tipo é obrigatório'];
    }

    return Object.keys(this.errors).length === 0;
  }

  save(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    this.metadataService.saveMetadata(this.form).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Registro salvo com sucesso');
        this.closeModal();
        this.loadMetadata();
      },
      error: (err: any) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
          if (err.error.message) {
            this.toastr.error(err.error.message);
          }
        } else {
          this.toastr.error('Erro ao salvar registro');
        }
        console.error(err);
      },
    });
  }

  // Activation Actions
  activate(id: number): void {
    this.processing = true;
    this.metadataService.activateMetadata(id).subscribe({
      next: () => {
        this.toastr.success('Registro ativado com sucesso');
        this.loadMetadata();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao ativar registro');
        this.processing = false;
      },
    });
  }

  deactivate(id: number): void {
    this.processing = true;
    this.metadataService.deactivateMetadata(id).subscribe({
      next: () => {
        this.toastr.success('Registro inativado com sucesso');
        this.loadMetadata();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao inativar registro');
        this.processing = false;
      },
    });
  }

  deleteMetadata(id: number): void {
    this.processing = true;
    this.metadataService.deleteMetadata(id).subscribe({
      next: () => {
        this.toastr.success('Registro apagado com sucesso');
        this.loadMetadata();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao apagar registro');
        this.processing = false;
      },
    });
  }
}
