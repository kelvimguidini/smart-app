import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { CurrencyService, Currency } from '../../../services/currency.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ToastService } from '../../../services/toast.service';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';

@Component({
  selector: 'app-currencies',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, DatatableComponent, ConfirmModalComponent],
  templateUrl: './currencies.component.html',
  styleUrls: ['./currencies.component.scss'],
})
export class CurrenciesComponent implements OnInit {
  private readonly currencyService = inject(CurrencyService);
  private readonly toastr = inject(ToastService);

  currencies: Currency[] = [];

  // Pagination and Filtering
  currentPage = 1;
  lastPage = 1;
  totalItems = 0;
  perPage = 10;
  searchTerm = '';
  sortColumn = 'name';
  sortDirection = 'asc';

  // Form State
  form: { id: number; name: string; symbol: string; sigla: string } = {
    id: 0,
    name: '',
    symbol: '',
    sigla: '',
  };
  inEdition = 0;
  processing = false;
  isLoader = false;
  showModal = false;
  errors: any = {};

  ngOnInit(): void {
    this.loadCurrencies();
  }

  loadCurrencies(): void {
    this.isLoader = true;
    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchTerm,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.currencyService.getCurrencies(params).subscribe({
      next: (response) => {
        this.currencies = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.totalItems = response.total;
        this.isLoader = false;
      },
      error: (err) => {
        this.toastr.error('Erro ao carregar moedas');
        this.isLoader = false;
        console.error(err);
      },
    });
  }

  // Datatable Events
  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadCurrencies();
  }

  onSearch(term: string): void {
    this.searchTerm = term;
    this.currentPage = 1;
    this.loadCurrencies();
  }

  onSort(event: { column: string; direction: string }): void {
    this.sortColumn = event.column;
    this.sortDirection = event.direction;
    this.loadCurrencies();
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
    this.form = { id: 0, name: '', symbol: '', sigla: '' };
    this.processing = false;
    this.errors = {};
  }

  edit(currency: Currency): void {
    this.inEdition = currency.id;
    this.form = { id: currency.id, name: currency.name, symbol: currency.symbol || '', sigla: currency.sigla || '' };
    this.errors = {};
    this.showModal = true;
  }

  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
    }

    if (!this.form.symbol || this.form.symbol.trim() === '') {
      this.errors.symbol = ['O símbolo é obrigatório'];
    }

    return Object.keys(this.errors).length === 0;
  }

  save(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;
    this.currencyService.saveCurrency(this.form).subscribe({
      next: (res) => {
        this.toastr.success(res.message || 'Moeda salva com sucesso');
        this.closeModal();
        this.loadCurrencies();
      },
      error: (err) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastr.error('Erro ao salvar moeda');
        }
        console.error(err);
      },
    });
  }

  // Action Actions
  activate(id: number): void {
    this.processing = true;
    this.currencyService.activateCurrency(id).subscribe({
      next: () => {
        this.toastr.success('Moeda ativada com sucesso');
        this.loadCurrencies();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao ativar moeda');
        this.processing = false;
      },
    });
  }

  deactivate(id: number): void {
    this.processing = true;
    this.currencyService.deactivateCurrency(id).subscribe({
      next: () => {
        this.toastr.success('Moeda inativada com sucesso');
        this.loadCurrencies();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao inativar moeda');
        this.processing = false;
      },
    });
  }

  deleteCurrency(id: number): void {
    this.processing = true;
    this.currencyService.deleteCurrency(id).subscribe({
      next: () => {
        this.toastr.success('Moeda apagada com sucesso');
        this.loadCurrencies();
      },
      error: (err: any) => {
        this.toastr.error('Erro ao apagar moeda');
        this.processing = false;
      },
    });
  }
}
