import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface CustomerRequester {
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

export interface CustomerRequesterCreateUpdateRequest {
  id?: number;
  customer_id: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class CustomerRequesterService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getRequesters(params: any = {}): Observable<PaginationResponse<CustomerRequester>> {
    return this.http.get<PaginationResponse<CustomerRequester>>(`${this.apiUrl}/api/customer-requesters`, { params });
  }

  saveRequester(data: CustomerRequesterCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/customer-requesters`, data);
  }

  deleteRequester(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/customer-requesters/${id}`);
  }

  activateRequester(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-requesters/activate/${id}`, {});
  }

  deactivateRequester(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-requesters/deactivate/${id}`, {});
  }
}
