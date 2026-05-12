import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Apto {
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

export interface AptoCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class AptoService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de apartamentos com paginação e filtros
   */
  getAptos(params: any = {}): Observable<PaginationResponse<Apto>> {
    return this.http.get<PaginationResponse<Apto>>(`${this.apiUrl}/api/aptos`, { params });
  }

  /**
   * Salvar ou atualizar apartamento
   */
  saveApto(data: AptoCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/aptos`, data);
  }

  /**
   * Deletar apartamento
   */
  deleteApto(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/aptos/${id}`);
  }

  /**
   * Ativar apartamento
   */
  activateApto(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/aptos/${id}/activate`, {});
  }

  /**
   * Inativar apartamento
   */
  deactivateApto(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/aptos/${id}/deactivate`, {});
  }
}
