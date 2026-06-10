import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface TransportService {
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

export interface TransportServiceCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class TransportServiceService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getTransportServices(params: any = {}): Observable<PaginationResponse<TransportService>> {
    return this.http.get<PaginationResponse<TransportService>>(`${this.apiUrl}/api/transport-services`, { params });
  }

  saveTransportService(data: TransportServiceCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/transport-services`, data);
  }

  deleteTransportService(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/transport-services/${id}`);
  }

  activateTransportService(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/transport-services/${id}/activate`, {});
  }

  deactivateTransportService(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/transport-services/${id}/deactivate`, {});
  }
}
