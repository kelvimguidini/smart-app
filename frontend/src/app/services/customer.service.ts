import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Customer {
  id: number;
  name: string;
  color?: string;
  document?: string;
  phone?: string;
  email?: string;
  responsibleAuthorizing?: string;
  logo?: string;
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

@Injectable({
  providedIn: 'root'
})
export class CustomerService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getCustomers(params: any = {}): Observable<PaginationResponse<Customer>> {
    return this.http.get<PaginationResponse<Customer>>(`${this.apiUrl}/api/customers`, { params });
  }

  saveCustomer(formData: FormData): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/customers`, formData);
  }

  deleteCustomer(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/customers/${id}`);
  }

  activateCustomer(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customers/activate/${id}`, {});
  }

  deactivateCustomer(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customers/deactivate/${id}`, {});
  }
}
