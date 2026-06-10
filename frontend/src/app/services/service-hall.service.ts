import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface ServiceHall {
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

export interface ServiceHallCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class ServiceHallService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista
   */
  getServiceHalls(params: any = {}): Observable<PaginationResponse<ServiceHall>> {
    return this.http.get<PaginationResponse<ServiceHall>>(`${this.apiUrl}/api/service-halls`, { params });
  }

  /**
   * Salvar
   */
  saveServiceHall(data: ServiceHallCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/service-halls`, data);
  }

  /**
   * Deletar
   */
  deleteServiceHall(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/service-halls/${id}`);
  }

  /**
   * Ativar
   */
  activateServiceHall(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/service-halls/${id}/activate`, {});
  }

  /**
   * Inativar
   */
  deactivateServiceHall(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/service-halls/${id}/deactivate`, {});
  }
}
