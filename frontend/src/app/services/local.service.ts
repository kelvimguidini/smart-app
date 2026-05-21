import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Local {
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

export interface LocalCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class LocalService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de locais com paginação e filtros
   */
  getLocals(params: any = {}): Observable<PaginationResponse<Local>> {
    return this.http.get<PaginationResponse<Local>>(`${this.apiUrl}/api/locals`, { params });
  }

  /**
   * Salvar ou atualizar local
   */
  saveLocal(data: LocalCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/locals`, data);
  }

  /**
   * Deletar local
   */
  deleteLocal(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/locals/${id}`);
  }

  /**
   * Ativar local
   */
  activateLocal(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/locals/${id}/activate`, {});
  }

  /**
   * Inativar local
   */
  deactivateLocal(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/locals/${id}/deactivate`, {});
  }
}
