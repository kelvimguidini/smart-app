import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Service {
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

export interface ServiceCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class ServiceService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de serviços com paginação e filtros
   */
  getServices(params: any = {}): Observable<PaginationResponse<Service>> {
    return this.http.get<PaginationResponse<Service>>(`${this.apiUrl}/api/services`, { params });
  }

  /**
   * Salvar ou atualizar serviço
   */
  saveService(data: ServiceCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/services`, data);
  }

  /**
   * Deletar serviço
   */
  deleteService(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/services/${id}`);
  }

  /**
   * Ativar serviço
   */
  activateService(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/services/${id}/activate`, {});
  }

  /**
   * Inativar serviço
   */
  deactivateService(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/services/${id}/deactivate`, {});
  }
}
