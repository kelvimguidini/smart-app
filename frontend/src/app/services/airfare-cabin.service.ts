import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface AirfareCabin {
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

export interface AirfareCabinCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class AirfareCabinService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getCabins(params: any = {}): Observable<PaginationResponse<AirfareCabin>> {
    return this.http.get<PaginationResponse<AirfareCabin>>(`${this.apiUrl}/api/airfare-cabins`, { params });
  }

  saveCabin(data: AirfareCabinCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/airfare-cabins`, data);
  }

  deleteCabin(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/airfare-cabins/${id}`);
  }

  activateCabin(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airfare-cabins/${id}/activate`, {});
  }

  deactivateCabin(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/airfare-cabins/${id}/deactivate`, {});
  }
}
