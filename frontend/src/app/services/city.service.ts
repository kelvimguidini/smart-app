import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface City {
  id: number;
  name: string;
  states?: string;
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

export interface CityCreateUpdateRequest {
  id?: number;
  name: string;
  states?: string;
  country: string;
}

@Injectable({
  providedIn: 'root'
})
export class CityService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  searchCities(term: string): Observable<City[]> {
    let params = new HttpParams();
    if (term) {
      params = params.set('term', term);
    }
    return this.http.get<City[]>(`${this.apiUrl}/api/cities/search`, { params });
  }

  getCities(params: any = {}): Observable<PaginationResponse<City>> {
    return this.http.get<PaginationResponse<City>>(`${this.apiUrl}/api/cities`, { params });
  }

  saveCity(data: CityCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/cities`, data);
  }

  deleteCity(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/cities/${id}`);
  }

  activateCity(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/cities/activate/${id}`, {});
  }

  deactivateCity(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/cities/deactivate/${id}`, {});
  }
}
