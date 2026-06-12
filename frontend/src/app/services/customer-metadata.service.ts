import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface CustomerMetadata {
  id: number;
  customer_id: number;
  type: 'requester' | 'sector' | 'cost_center';
  value: string;
  customer?: {
    id: number;
    name: string;
  };
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

export interface CustomerMetadataCreateUpdateRequest {
  id?: number;
  customer_id: number;
  type: 'requester' | 'sector' | 'cost_center';
  value: string;
}

@Injectable({
  providedIn: 'root'
})
export class CustomerMetadataService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getMetadataList(params: any = {}): Observable<PaginationResponse<CustomerMetadata>> {
    return this.http.get<PaginationResponse<CustomerMetadata>>(`${this.apiUrl}/api/customer-metadata`, { params });
  }

  saveMetadata(data: CustomerMetadataCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/customer-metadata`, data);
  }

  deleteMetadata(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/customer-metadata/${id}`);
  }

  activateMetadata(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-metadata/activate/${id}`, {});
  }

  deactivateMetadata(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-metadata/deactivate/${id}`, {});
  }
}
