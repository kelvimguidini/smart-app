import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { City } from './city.service';

export interface ProviderService {
  id: number;
  name: string;
  city_id: number;
  city?: City;
  contact?: string;
  phone?: string;
  email?: string;
  national: boolean;
  iss_percent?: number;
  service_percent?: number;
  iva_percent?: number;
  active: boolean;
  codestur?: string;
  payment_method?: string;
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

export interface ProviderServiceCreateUpdateRequest {
  id?: number;
  name: string;
  city_id: number;
  contact?: string;
  phone?: string;
  email?: string;
  national: boolean;
  iss_percent?: number;
  service_percent?: number;
  iva_percent?: number;
  codestur?: string;
  payment_method?: string;
}

@Injectable({
  providedIn: 'root'
})
export class ProviderServiceService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getProviderServices(params: any = {}): Observable<PaginationResponse<ProviderService>> {
    return this.http.get<PaginationResponse<ProviderService>>(`${this.apiUrl}/api/provider-services`, { params });
  }

  saveProviderService(data: ProviderServiceCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/provider-services`, data);
  }

  deleteProviderService(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/provider-services/${id}`);
  }

  activateProviderService(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/provider-services/${id}/activate`, {});
  }

  deactivateProviderService(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/provider-services/${id}/deactivate`, {});
  }
}
