import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
 
export interface Brand {
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
 
export interface BrandCreateUpdateRequest {
  id?: number;
  name: string;
}
 
@Injectable({
  providedIn: 'root'
})
export class BrandService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;
 
  getBrands(params: any = {}): Observable<PaginationResponse<Brand>> {
    return this.http.get<PaginationResponse<Brand>>(`${this.apiUrl}/api/brands`, { params });
  }
 
  saveBrand(data: BrandCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/brands`, data);
  }
 
  deleteBrand(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/brands/${id}`);
  }
 
  activateBrand(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/brands/${id}/activate`, {});
  }
 
  deactivateBrand(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/brands/${id}/deactivate`, {});
  }
}
