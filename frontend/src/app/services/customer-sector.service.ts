import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface CustomerSector {
  id: number;
  customer_id: number;
  name: string;
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

export interface CustomerSectorCreateUpdateRequest {
  id?: number;
  customer_id: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class CustomerSectorService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getSectors(params: any = {}): Observable<PaginationResponse<CustomerSector>> {
    return this.http.get<PaginationResponse<CustomerSector>>(`${this.apiUrl}/api/customer-sectors`, { params });
  }

  saveSector(data: CustomerSectorCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/customer-sectors`, data);
  }

  deleteSector(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/customer-sectors/${id}`);
  }

  activateSector(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-sectors/activate/${id}`, {});
  }

  deactivateSector(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-sectors/deactivate/${id}`, {});
  }
}
