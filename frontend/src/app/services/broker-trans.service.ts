import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface BrokerTransport {
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

export interface BrokerTransportCreateUpdateRequest {
  id?: number;
  name: string;
  city_id?: number;
  contact: string;
  phone: string;
  email: string;
  national: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class BrokerTransportService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getBrokers(params: any = {}): Observable<PaginationResponse<BrokerTransport>> {
    return this.http.get<PaginationResponse<BrokerTransport>>(`${this.apiUrl}/api/broker-transports`, { params });
  }

  saveBroker(data: BrokerTransportCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/broker-transports`, data);
  }

  deleteBroker(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/broker-transports/${id}`);
  }

  activateBroker(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/broker-transports/${id}/activate`, {});
  }

  deactivateBroker(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/broker-transports/${id}/deactivate`, {});
  }
}
