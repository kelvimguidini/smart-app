import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface ProviderAirfare {
  id: number;
  name: string;
  city_id?: number;
  city?: {
    id: number;
    name: string;
    states?: string;
    country?: string;
  };
  contact: string;
  phone: string;
  email: string;
  national: boolean;
  iss_percent?: number | null;
  service_percent?: number | null;
  iva_percent?: number | null;
  payment_method?: string | null;
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

export interface ProviderAirfareCreateUpdateRequest {
  id?: number;
  name: string;
  city_id?: number;
  contact: string;
  phone: string;
  email: string;
  national: boolean;
  iss_percent?: number | null;
  service_percent?: number | null;
  iva_percent?: number | null;
  payment_method?: string | null;
}

@Injectable({
  providedIn: 'root'
})
export class ProviderAirfareService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getProviders(params: any = {}): Observable<PaginationResponse<ProviderAirfare>> {
    return this.http.get<PaginationResponse<ProviderAirfare>>(`${this.apiUrl}/api/provider-airfares`, { params });
  }

  saveProvider(data: ProviderAirfareCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/provider-airfares`, data);
  }

  deleteProvider(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/provider-airfares/${id}`);
  }

  activateProvider(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/provider-airfares/${id}/activate`, {});
  }

  deactivateProvider(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/provider-airfares/${id}/deactivate`, {});
  }
}
