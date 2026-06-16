import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface AirfareBaggage {
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

export interface AirfareBaggageCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class AirfareBaggageService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getBaggages(params: any = {}): Observable<PaginationResponse<AirfareBaggage>> {
    return this.http.get<PaginationResponse<AirfareBaggage>>(`${this.apiUrl}/api/airfare-baggages`, { params });
  }

  saveBaggage(data: AirfareBaggageCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/airfare-baggages`, data);
  }

  deleteBaggage(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/airfare-baggages/${id}`);
  }

  activateBaggage(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airfare-baggages/${id}/activate`, {});
  }

  deactivateBaggage(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airfare-baggages/${id}/deactivate`, {});
  }
}
