import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
 
export interface Vehicle {
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
 
export interface VehicleCreateUpdateRequest {
  id?: number;
  name: string;
}
 
@Injectable({
  providedIn: 'root'
})
export class VehicleService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;
 
  getVehicles(params: any = {}): Observable<PaginationResponse<Vehicle>> {
    return this.http.get<PaginationResponse<Vehicle>>(`${this.apiUrl}/api/vehicles`, { params });
  }
 
  saveVehicle(data: VehicleCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/vehicles`, data);
  }
 
  deleteVehicle(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/vehicles/${id}`);
  }
 
  activateVehicle(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/vehicles/${id}/activate`, {});
  }
 
  deactivateVehicle(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/vehicles/${id}/deactivate`, {});
  }
}
