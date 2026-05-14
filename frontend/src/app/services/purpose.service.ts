import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Purpose {
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

export interface PurposeCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class PurposeService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de propósitos com paginação e filtros
   */
  getPurposes(params: any = {}): Observable<PaginationResponse<Purpose>> {
    return this.http.get<PaginationResponse<Purpose>>(`${this.apiUrl}/api/purposes`, { params });
  }

  /**
   * Salvar ou atualizar propósito
   */
  savePurpose(data: PurposeCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/purposes`, data);
  }

  /**
   * Deletar propósito
   */
  deletePurpose(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/purposes/${id}`);
  }

  /**
   * Ativar propósito
   */
  activatePurpose(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/purposes/${id}/activate`, {});
  }

  /**
   * Inativar propósito
   */
  deactivatePurpose(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/purposes/${id}/deactivate`, {});
  }
}
