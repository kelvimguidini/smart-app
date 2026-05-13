import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { CategoryService, Category, CategoryCreateUpdateRequest } from '../../services/category.service';
import { ToastService } from '../../services/toast.service';
import { AuthenticatedLayoutComponent } from "../../shared/layouts/authenticated-layout/authenticated-layout.component";
import { ConfirmModalComponent } from "../../shared/components/confirm-modal/confirm-modal.component";
import { DatatableComponent } from "../../shared/components/datatable/datatable.component";

@Component({
  selector: 'app-category',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './category.component.html',
  styleUrls: ['./category.component.scss']
})
export class CategoryComponent implements OnInit {
  private readonly categoryService: CategoryService = inject(CategoryService);
  private readonly toastService: ToastService = inject(ToastService);

  categories: Category[] = [];
  inEdition: number = 0;
  isLoader: boolean = false;
  processing: boolean = false;
  errors: any = {};

  // Pagination and Filtering state
  pagination: any = {
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  };
  searchQuery: string = '';
  sortColumn: string = 'id';
  sortDirection: string = 'desc';

  form = {
    id: 0,
    name: ''
  };

  ngOnInit(): void {
    this.loadCategories();
  }

  /**
   * Carregar lista de categorias com paginação e filtros
   */
  loadCategories(): void {
    this.isLoader = true;
    const params = {
      page: this.pagination.current_page,
      per_page: this.pagination.per_page,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection
    };

    this.categoryService.getCategories(params).subscribe({
      next: (response: any) => {
        this.categories = response.data || [];
        this.pagination = {
          current_page: response.current_page,
          per_page: response.per_page,
          total: response.total,
          last_page: response.last_page,
          from: response.from,
          to: response.to
        };
        this.isLoader = false;
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao carregar categorias');
        console.error('Erro ao carregar categorias:', error);
      }
    });
  }

  /**
   * Pesquisar (chamado pelo DatatableComponent)
   */
  onSearch(query: string): void {
    this.searchQuery = query;
    this.pagination.current_page = 1;
    this.loadCategories();
  }

  /**
   * Mudar página (chamado pelo DatatableComponent)
   */
  onPageChange(page: number): void {
    this.pagination.current_page = page;
    this.loadCategories();
  }

  /**
   * Mudar quantidade por página (chamado pelo DatatableComponent)
   */
  onPerPageChange(perPage: number): void {
    this.pagination.per_page = perPage;
    this.pagination.current_page = 1;
    this.loadCategories();
  }

  /**
   * Ordenar coluna
   */
  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadCategories();
  }

  /**
   * Editar categoria
   */
  edit(category: Category): void {
    this.inEdition = category.id;
    this.form.id = category.id;
    this.form.name = category.name;
    this.errors = {};
  }

  /**
   * Cancelar edição
   */
  cancelEdit(): void {
    this.inEdition = 0;
    this.resetForm();
  }

  /**
   * Resetar formulário
   */
  resetForm(): void {
    this.form = {
      id: 0,
      name: ''
    };
    this.errors = {};
    this.inEdition = 0;
  }

  /**
   * Validar formulário
   */
  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
      return false;
    }

    return true;
  }

  /**
   * Salvar categoria (criar ou atualizar)
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: CategoryCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name
    };

    this.categoryService.saveCategory(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success(response.message || 'Categoria salva com sucesso');
        this.resetForm();
        this.loadCategories();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar categoria');
        }
        console.error('Erro ao salvar categoria:', error);
      }
    });
  }

  /**
   * Deletar categoria
   */
  deleteCategory(categoryId: number): void {
    this.isLoader = true;
    this.categoryService.deleteCategory(categoryId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Categoria apagada com sucesso');
        this.loadCategories();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar categoria');
        console.error('Erro ao deletar categoria:', error);
      }
    });
  }

  /**
   * Ativar categoria
   */
  activateCategory(categoryId: number): void {
    this.isLoader = true;
    this.categoryService.activateCategory(categoryId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Categoria ativada com sucesso');
        this.loadCategories();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar categoria');
        console.error('Erro ao ativar categoria:', error);
      }
    });
  }

  /**
   * Inativar categoria
   */
  deactivateCategory(categoryId: number): void {
    this.isLoader = true;
    this.categoryService.deactivateCategory(categoryId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success(response.message || 'Categoria inativada com sucesso');
        this.loadCategories();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar categoria');
        console.error('Erro ao inativar categoria:', error);
      }
    });
  }

  /**
   * Método auxiliar para template
   */
  isCategoryInEdition(categoryId: number): boolean {
    return this.inEdition === categoryId;
  }
}
