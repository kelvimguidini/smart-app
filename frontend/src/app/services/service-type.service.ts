import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface ServiceType {
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

export interface ServiceTypeCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class ServiceTypeService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de tipos de serviço com paginação e filtros
   */
  getServiceTypes(params: any = {}): Observable<PaginationResponse<ServiceType>> {
    return this.http.get<PaginationResponse<ServiceType>>(`${this.apiUrl}/api/service-types`, { params });
  }

  /**
   * Salvar ou atualizar tipo de serviço
   */
  saveServiceType(data: ServiceTypeCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/service-types`, data);
  }

  /**
   * Deletar tipo de serviço
   */
  deleteServiceType(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/service-types/${id}`);
  }

  /**
   * Ativar tipo de serviço
   */
  activateServiceType(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/service-types/${id}/activate`, {});
  }

  /**
   * Inativar tipo de serviço
   */
  deactivateServiceType(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/service-types/${id}/deactivate`, {});
  }
}
