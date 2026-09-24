import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface AirfareAirport {
  id: number;
  iata_code: string;
  name: string;
  city: string;
  state?: string;
  country: string;
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

export interface AirfareAirportCreateUpdateRequest {
  id?: number;
  iata_code: string;
  name: string;
  city: string;
  state?: string;
  country?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AirfareAirportService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getAirports(params: any = {}): Observable<PaginationResponse<AirfareAirport>> {
    return this.http.get<PaginationResponse<AirfareAirport>>(`${this.apiUrl}/api/airports`, { params });
  }

  saveAirport(data: AirfareAirportCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/airports`, data);
  }

  deleteAirport(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/airports/${id}`);
  }

  activateAirport(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airports/${id}/activate`, {});
  }

  deactivateAirport(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airports/${id}/deactivate`, {});
  }
}
