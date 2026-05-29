import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
 
export interface CarModel {
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
 
export interface CarModelCreateUpdateRequest {
  id?: number;
  name: string;
}
 
@Injectable({
  providedIn: 'root'
})
export class CarModelService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;
 
  getCarModels(params: any = {}): Observable<PaginationResponse<CarModel>> {
    return this.http.get<PaginationResponse<CarModel>>(`${this.apiUrl}/api/car-models`, { params });
  }
 
  saveCarModel(data: CarModelCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/car-models`, data);
  }
 
  deleteCarModel(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/car-models/${id}`);
  }
 
  activateCarModel(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/car-models/${id}/activate`, {});
  }
 
  deactivateCarModel(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/car-models/${id}/deactivate`, {});
  }
}
