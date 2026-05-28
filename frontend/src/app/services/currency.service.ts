import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Currency {
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

export interface CurrencyCreateUpdateRequest {
  id?: number;
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class CurrencyService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getCurrencies(params: any = {}): Observable<PaginationResponse<Currency>> {
    return this.http.get<PaginationResponse<Currency>>(`${this.apiUrl}/api/currencies`, { params });
  }

  saveCurrency(data: CurrencyCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/currencies`, data);
  }

  deleteCurrency(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/currencies/${id}`);
  }

  activateCurrency(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/currencies/activate/${id}`, {});
  }

  deactivateCurrency(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/api/currencies/deactivate/${id}`, {});
  }
}
