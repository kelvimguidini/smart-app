import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CrdService, Crd } from '../../../services/crd.service';
import { CustomerService, Customer } from '../../../services/customer.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ToastService } from '../../../services/toast.service';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { NgxMaskDirective } from 'ngx-mask';

@Component({
  selector: 'app-crds',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, DatatableComponent, ConfirmModalComponent, NgxMaskDirective],
  templateUrl: './crds.component.html',
  styleUrls: ['./crds.component.scss'],
})
export class CrdsComponent implements OnInit {
  private readonly crdService = inject(CrdService);
  private readonly customerService = inject(CustomerService);
  private readonly toastr = inject(ToastService);

  crds: Crd[] = [];
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
    number: string;
    customer_id: number;
  } = {
    id: 0,
    name: '',
    number: '',
    customer_id: 0,
  };

  inEdition = 0;
  processing = false;
  isLoader = false;
  showModal = false;
  errors: any = {};

  ngOnInit(): void {
    this.loadCrds();
    this.loadCustomers();
  }

  loadCrds(): void {
    this.isLoader = true;
    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchTerm,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.crdService.getCrds(params).subscribe({
      next: (response) => {
        this.crds = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.totalItems = response.total;
        this.isLoader = false;
      },
      error: (err: any) => {
        this.toastr.error('Erro ao carregar CRDs');
        this.isLoader = false;
        console.error(err);
      },
    });
  }

  loadCustomers(): void {
    // Carregar todos os clientes para o select (limitando a 100 para simplificar, ou backend deve suportar list sem paginação)
    this.customerService.getCustomers({ per_page: 500 }).subscribe({
      next: (res) => {
        this.customers = res.data;
      },
      error: (err: any) => console.error(err),
    });
  }

  // Datatable Events
  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadCrds();
  }

  onSearch(term: string): void {
    this.searchTerm = term;
    this.currentPage = 1;
    this.loadCrds();
  }

  onSort(event: { column: string; direction: string }): void {
    this.sortColumn = event.column;
    this.sortDirection = event.direction;
    this.loadCrds();
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
      number: '',
      customer_id: 0,
    };
    this.processing = false;
    this.errors = {};
  }

  edit(crd: Crd): void {
    this.inEdition = crd.id;
    this.form = {
      id: crd.id,
      name: crd.name,
      number: crd.number,
      customer_id: crd.customer_id,
    };
    this.errors = {};
    this.showModal = true;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
    }

    if (!this.form.number || this.form.number.trim() === '') {
      this.errors.number = ['O número é obrigatório'];
    } else if (this.form.number.replace(/\D/g, '').length !== 14) {
      this.errors.number = ['O número do CRD deve conter exatamente 14 dígitos'];
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

    this.crdService.saveCrd(this.form).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'CRD salvo com sucesso');
        this.closeModal();
        this.loadCrds();
      },
      error: (err: any) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastr.error('Erro ao salvar CRD');
        }
        console.error(err);
      },
    });
  }

  // Action Actions
  activate(id: number): void {
    this.processing = true;
    this.crdService.activateCrd(id).subscribe({
      next: () => {
        this.toastr.success('CRD ativado com sucesso');
        this.loadCrds();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao ativar CRD');
        this.processing = false;
      },
    });
  }

  deactivate(id: number): void {
    this.processing = true;
    this.crdService.deactivateCrd(id).subscribe({
      next: () => {
        this.toastr.success('CRD inativado com sucesso');
        this.loadCrds();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao inativar CRD');
        this.processing = false;
      },
    });
  }

  deleteCrd(id: number): void {
    this.processing = true;
    this.crdService.deleteCrd(id).subscribe({
      next: () => {
        this.toastr.success('CRD apagado com sucesso');
        this.loadCrds();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao apagar CRD');
        this.processing = false;
      },
    });
  }
}
