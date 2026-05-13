import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Regime {
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

export interface RegimeCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class RegimeService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de regimes com paginação e filtros
   */
  getRegimes(params: any = {}): Observable<PaginationResponse<Regime>> {
    return this.http.get<PaginationResponse<Regime>>(`${this.apiUrl}/api/regimes`, { params });
  }

  /**
   * Salvar ou atualizar regime
   */
  saveRegime(data: RegimeCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/regimes`, data);
  }

  /**
   * Deletar regime
   */
  deleteRegime(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/regimes/${id}`);
  }

  /**
   * Ativar regime
   */
  activateRegime(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/regimes/${id}/activate`, {});
  }

  /**
   * Inativar regime
   */
  deactivateRegime(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/regimes/${id}/deactivate`, {});
  }
}
