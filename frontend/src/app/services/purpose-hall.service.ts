import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface PurposeHall {
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

export interface PurposeHallCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class PurposeHallService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista
   */
  getPurposeHalls(params: any = {}): Observable<PaginationResponse<PurposeHall>> {
    return this.http.get<PaginationResponse<PurposeHall>>(`${this.apiUrl}/api/purpose-halls`, { params });
  }

  /**
   * Salvar
   */
  savePurposeHall(data: PurposeHallCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/purpose-halls`, data);
  }

  /**
   * Deletar
   */
  deletePurposeHall(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/purpose-halls/${id}`);
  }

  /**
   * Ativar
   */
  activatePurposeHall(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/purpose-halls/${id}/activate`, {});
  }

  /**
   * Inativar
   */
  deactivatePurposeHall(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/purpose-halls/${id}/deactivate`, {});
  }
}
