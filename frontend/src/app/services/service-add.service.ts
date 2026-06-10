import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface ServiceAdd {
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

export interface ServiceAddCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class ServiceAddService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getServiceAdds(params: any = {}): Observable<PaginationResponse<ServiceAdd>> {
    return this.http.get<PaginationResponse<ServiceAdd>>(`${this.apiUrl}/api/service-adds`, { params });
  }

  saveServiceAdd(data: ServiceAddCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/service-adds`, data);
  }

  deleteServiceAdd(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/service-adds/${id}`);
  }

  activateServiceAdd(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/service-adds/${id}/activate`, {});
  }

  deactivateServiceAdd(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/service-adds/${id}/deactivate`, {});
  }
}
