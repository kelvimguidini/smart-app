import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface CustomerCostCenter {
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

export interface CustomerCostCenterCreateUpdateRequest {
  id?: number;
  customer_id: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class CustomerCostCenterService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getCostCenters(params: any = {}): Observable<PaginationResponse<CustomerCostCenter>> {
    return this.http.get<PaginationResponse<CustomerCostCenter>>(`${this.apiUrl}/api/customer-cost-centers`, { params });
  }

  saveCostCenter(data: CustomerCostCenterCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/customer-cost-centers`, data);
  }

  deleteCostCenter(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/customer-cost-centers/${id}`);
  }

  activateCostCenter(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-cost-centers/activate/${id}`, {});
  }

  deactivateCostCenter(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/customer-cost-centers/deactivate/${id}`, {});
  }
}
