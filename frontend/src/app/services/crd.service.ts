import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Crd {
  id: number;
  name: string;
  number: string;
  customer_id: number;
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

export interface CrdCreateUpdateRequest {
  id?: number;
  name: string;
  number: string;
  customer_id: number;
}

@Injectable({
  providedIn: 'root'
})
export class CrdService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getCrds(params: any = {}): Observable<PaginationResponse<Crd>> {
    return this.http.get<PaginationResponse<Crd>>(`${this.apiUrl}/api/crds`, { params });
  }

  saveCrd(data: CrdCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/crds`, data);
  }

  deleteCrd(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/crds/${id}`);
  }

  activateCrd(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/crds/activate/${id}`, {});
  }

  deactivateCrd(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/crds/deactivate/${id}`, {});
  }
}
