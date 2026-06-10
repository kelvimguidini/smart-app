import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Measure {
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

export interface MeasureCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class MeasureService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getMeasures(params: any = {}): Observable<PaginationResponse<Measure>> {
    return this.http.get<PaginationResponse<Measure>>(`${this.apiUrl}/api/measures`, { params });
  }

  saveMeasure(data: MeasureCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/measures`, data);
  }

  deleteMeasure(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/measures/${id}`);
  }

  activateMeasure(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/measures/${id}/activate`, {});
  }

  deactivateMeasure(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/measures/${id}/deactivate`, {});
  }
}
