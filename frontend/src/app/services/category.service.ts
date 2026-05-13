import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Category {
  id: number;
  name: string;
  active: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface PaginationResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: any[];
}

export interface CategoryCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de categorias com paginação e filtros
   */
  getCategories(params: any = {}): Observable<PaginationResponse<Category>> {
    return this.http.get<PaginationResponse<Category>>(`${this.apiUrl}/api/categories`, { params });
  }

  /**
   * Salvar ou atualizar categoria
   */
  saveCategory(data: CategoryCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/categories`, data);
  }

  /**
   * Deletar categoria
   */
  deleteCategory(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/categories/${id}`);
  }

  /**
   * Ativar categoria
   */
  activateCategory(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/categories/${id}/activate`, {});
  }

  /**
   * Inativar categoria
   */
  deactivateCategory(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/categories/${id}/deactivate`, {});
  }
}
