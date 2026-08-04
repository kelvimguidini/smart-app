import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CustomerSectorService, CustomerSector } from '../../../services/customer-sector.service';
import { CustomerService, Customer } from '../../../services/customer.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ToastService } from '../../../services/toast.service';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-customer-sector',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, RouterLinkActive, AuthenticatedLayoutComponent, DatatableComponent, ConfirmModalComponent, ModalComponent],
  templateUrl: './customer-sector.component.html',
  styleUrls: ['./customer-sector.component.scss'],
})
export class CustomerSectorComponent implements OnInit {
  private readonly sectorService = inject(CustomerSectorService);
  private readonly customerService = inject(CustomerService);
  private readonly toastr = inject(ToastService);

  sectors: CustomerSector[] = [];
  customers: Customer[] = [];

  // Pagination and Filtering
  currentPage = 1;
  lastPage = 1;
  totalItems = 0;
  perPage = 10;
  searchTerm = '';
  sortColumn = 'name';
  sortDirection = 'asc';

  // Form State
  form: {
    id: number;
    name: string;
    customer_id: number;
  } = {
    id: 0,
    name: '',
    customer_id: 0,
  };

  inEdition = 0;
  processing = false;
  isLoader = false;
  showModal = false;
  errors: any = {};

  ngOnInit(): void {
    this.loadSectors();
    this.loadCustomers();
  }

  loadSectors(): void {
    this.isLoader = true;
    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchTerm,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.sectorService.getSectors(params).subscribe({
      next: (response) => {
        this.sectors = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.totalItems = response.total;
        this.isLoader = false;
      },
      error: (err: any) => {
        this.toastr.error('Erro ao carregar setores');
        this.isLoader = false;
        console.error(err);
      },
    });
  }

  loadCustomers(): void {
    this.customerService.getCustomers({ per_page: 500 }).subscribe({
      next: (res) => {
        this.customers = (res.data || []).sort((a: any, b: any) => {
          const nameA = a.name || '';
          const nameB = b.name || '';
          return nameA.localeCompare(nameB, 'pt-BR');
        });
      },
      error: (err: any) => console.error(err),
    });
  }

  // Datatable Events
  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadSectors();
  }

  onSearch(term: string): void {
    this.searchTerm = term;
    this.currentPage = 1;
    this.loadSectors();
  }

  onSort(event: { column: string; direction: string }): void {
    this.sortColumn = event.column;
    this.sortDirection = event.direction;
    this.loadSectors();
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
      name: '',
      customer_id: 0,
    };
    this.processing = false;
    this.errors = {};
  }

  edit(item: CustomerSector): void {
    this.inEdition = item.id;
    this.form = {
      id: item.id,
      name: item.name,
      customer_id: item.customer_id,
    };
    this.errors = {};
    this.showModal = true;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
    }

    if (!this.form.customer_id || this.form.customer_id <= 0) {
      this.errors.customer_id = ['O cliente é obrigatório'];
    }

    return Object.keys(this.errors).length === 0;
  }

  save(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    this.sectorService.saveSector(this.form).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Setor salvo com sucesso');
        this.closeModal();
        this.loadSectors();
      },
      error: (err: any) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastr.error('Erro ao salvar setor');
        }
        console.error(err);
      },
    });
  }

  activate(id: number): void {
    this.processing = true;
    this.sectorService.activateSector(id).subscribe({
      next: () => {
        this.toastr.success('Setor ativado com sucesso');
        this.loadSectors();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao ativar setor');
        this.processing = false;
      },
    });
  }

  deactivate(id: number): void {
    this.processing = true;
    this.sectorService.deactivateSector(id).subscribe({
      next: () => {
        this.toastr.success('Setor inativado com sucesso');
        this.loadSectors();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao inativar setor');
        this.processing = false;
      },
    });
  }

  deleteItem(id: number): void {
    this.processing = true;
    this.sectorService.deleteSector(id).subscribe({
      next: () => {
        this.toastr.success('Setor apagado com sucesso');
        this.loadSectors();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao apagar setor');
        this.processing = false;
      },
    });
  }
}
