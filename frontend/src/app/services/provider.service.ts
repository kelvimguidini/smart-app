import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { City } from './city.service';

export interface Provider {
  id: number;
  name: string;
  city_id: number;
  contact: string;
  phone: string;
  email: string;
  national: boolean;
  active: boolean;
  iss_percent: number;
  service_percent: number;
  iva_percent: number;
  payment_method: string;
  checkin_time: string;
  checkin_time_end: string;
  checkout_time: string;
  checkout_time_end: string;
  has_hotel: boolean;
  city?: City;
}

export interface ProviderCreateUpdateRequest {
  id: number;
  name: string;
  city_id: number;
  contact: string;
  phone: string;
  email: string;
  national: boolean;
  iss_percent: number;
  service_percent: number;
  iva_percent: number;
  payment_method: string;
  checkin_time: string;
  checkin_time_end: string;
  checkout_time: string;
  checkout_time_end: string;
  has_hotel: boolean;
  type?: string;
}

export interface ProviderPaginatedResponse {
  current_page: number;
  data: Provider[];
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

@Injectable({
  providedIn: 'root'
})
export class ProviderService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getProviders(params: any): Observable<ProviderPaginatedResponse> {
    let httpParams = new HttpParams();
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined) {
        httpParams = httpParams.set(key, params[key]);
      }
    });

    return this.http.get<ProviderPaginatedResponse>(`${this.apiUrl}/api/hotels`, { params: httpParams });
  }

  saveProvider(data: ProviderCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/hotels`, data);
  }

  deleteProvider(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/hotels/${id}`);
  }

  activateProvider(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/hotels/${id}/activate`, {});
  }

  deactivateProvider(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/hotels/${id}/deactivate`, {});
  }
}
