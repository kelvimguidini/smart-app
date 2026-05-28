import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Frequency {
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

export interface FrequencyCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class FrequencyService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getFrequencies(params: any = {}): Observable<PaginationResponse<Frequency>> {
    return this.http.get<PaginationResponse<Frequency>>(`${this.apiUrl}/api/frequencies`, { params });
  }

  saveFrequency(data: FrequencyCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/frequencies`, data);
  }

  deleteFrequency(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/frequencies/${id}`);
  }

  activateFrequency(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/frequencies/${id}/activate`, {});
  }

  deactivateFrequency(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/frequencies/${id}/deactivate`, {});
  }
}
