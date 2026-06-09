import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CustomerService, Customer } from '../../../services/customer.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ToastService } from '../../../services/toast.service';
import { NgxMaskDirective } from 'ngx-mask';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-customers',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, DatatableComponent, NgxMaskDirective, ConfirmModalComponent, ModalComponent],
  templateUrl: './customers.component.html',
  styleUrls: ['./customers.component.scss'],
})
export class CustomersComponent implements OnInit {
  private readonly customerService = inject(CustomerService);
  private readonly toastr = inject(ToastService);

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
    document: string;
    color: string;
    phone: string;
    email: string;
    responsibleAuthorizing: string;
  } = {
    id: 0,
    name: '',
    document: '',
    color: '#000000',
    phone: '',
    email: '',
    responsibleAuthorizing: '',
  };
  logoFile: File | null = null;
  currentLogoUrl: string | null = null;

  inEdition = 0;
  processing = false;
  isLoader = false;
  showModal = false;
  errors: any = {};

  ngOnInit(): void {
    this.loadCustomers();
  }

  loadCustomers(): void {
    this.isLoader = true;
    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchTerm,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.customerService.getCustomers(params).subscribe({
      next: (response) => {
        this.customers = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.totalItems = response.total;
        this.isLoader = false;
      },
      error: (err) => {
        this.toastr.error('Erro ao carregar clientes');
        this.isLoader = false;
        console.error(err);
      },
    });
  }

  // Datatable Events
  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadCustomers();
  }

  onSearch(term: string): void {
    this.searchTerm = term;
    this.currentPage = 1;
    this.loadCustomers();
  }

  onSort(event: { column: string; direction: string }): void {
    this.sortColumn = event.column;
    this.sortDirection = event.direction;
    this.loadCustomers();
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
      document: '',
      color: '#000000',
      phone: '',
      email: '',
      responsibleAuthorizing: '',
    };
    this.logoFile = null;
    this.currentLogoUrl = null;
    this.processing = false;
    this.errors = {};

    // Reset file input
    const fileInput = document.getElementById('logoFile') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
  }

  edit(customer: Customer): void {
    this.inEdition = customer.id;
    this.form = {
      id: customer.id,
      name: customer.name,
      document: customer.document || '',
      color: customer.color || '#000000',
      phone: customer.phone || '',
      email: customer.email || '',
      responsibleAuthorizing: customer.responsibleAuthorizing || '',
    };
    this.currentLogoUrl = customer.logo || null;
    this.logoFile = null;
    this.errors = {};

    // Reset file input
    const fileInput = document.getElementById('logoFile') as HTMLInputElement;
    if (fileInput) fileInput.value = '';

    this.showModal = true;
  }

  onFileChange(event: any): void {
    if (event.target.files && event.target.files.length > 0) {
      const file = event.target.files[0];
      this.logoFile = file;
      // Create local URL for immediate visual preview inside the modal
      this.currentLogoUrl = URL.createObjectURL(file);
    }
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
    }

    if (this.form.email && this.form.email.trim() !== '') {
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
        this.errors.email = ['O e-mail informado não é válido'];
      }
    }

    return Object.keys(this.errors).length === 0;
  }

  save(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const formData = new FormData();
    formData.append('name', this.form.name);
    if (this.form.id > 0) formData.append('id', this.form.id.toString());
    if (this.form.document) formData.append('document', this.form.document);
    if (this.form.color) formData.append('color', this.form.color);
    if (this.form.phone) formData.append('phone', this.form.phone);
    if (this.form.email) formData.append('email', this.form.email);
    if (this.form.responsibleAuthorizing) formData.append('responsibleAuthorizing', this.form.responsibleAuthorizing);

    if (this.logoFile) {
      formData.append('logo', this.logoFile);
    } else if (this.currentLogoUrl) {
      formData.append('logo', this.currentLogoUrl); // Keep existing
    }

    this.customerService.saveCustomer(formData).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Cliente salvo com sucesso');
        this.closeModal();
        this.loadCustomers();
      },
      error: (err) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastr.error('Erro ao salvar cliente');
        }
        console.error(err);
      },
    });
  }

  // Action Actions
  activate(id: number): void {
    this.processing = true;
    this.customerService.activateCustomer(id).subscribe({
      next: () => {
        this.toastr.success('Cliente ativado com sucesso');
        this.loadCustomers();
      },
      error: (err) => {
        this.toastr.error('Erro ao ativar cliente');
        this.processing = false;
        console.error(err);
      },
    });
  }

  deactivate(id: number): void {
    this.processing = true;
    this.customerService.deactivateCustomer(id).subscribe({
      next: () => {
        this.toastr.success('Cliente inativado com sucesso');
        this.loadCustomers();
      },
      error: (err) => {
        this.toastr.error('Erro ao inativar cliente');
        this.processing = false;
        console.error(err);
      },
    });
  }

  deleteCustomer(id: number): void {
    this.processing = true;
    this.customerService.deleteCustomer(id).subscribe({
      next: () => {
        this.toastr.success('Cliente apagado com sucesso');
        this.loadCustomers();
      },
      error: (err) => {
        this.toastr.error('Erro ao apagar cliente');
        this.processing = false;
        console.error(err);
      },
    });
  }
}
