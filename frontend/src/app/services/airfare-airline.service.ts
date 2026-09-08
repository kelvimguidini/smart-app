import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface AirfareAirline {
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

export interface AirfareAirlineCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class AirfareAirlineService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getAirlines(params: any = {}): Observable<PaginationResponse<AirfareAirline>> {
    return this.http.get<PaginationResponse<AirfareAirline>>(`${this.apiUrl}/api/airfare-airlines`, { params });
  }

  saveAirline(data: AirfareAirlineCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/airfare-airlines`, data);
  }

  deleteAirline(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/airfare-airlines/${id}`);
  }

  activateAirline(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airfare-airlines/${id}/activate`, {});
  }

  deactivateAirline(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airfare-airlines/${id}/deactivate`, {});
  }
}
