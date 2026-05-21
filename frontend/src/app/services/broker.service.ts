import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { City } from './city.service';

export interface Broker {
  id: number;
  name: string;
  city_id: number;
  contact: string;
  phone: string;
  email: string;
  national: boolean;
  active: boolean;
  city?: City;
}

export interface BrokerCreateUpdateRequest {
  id: number;
  name: string;
  city_id: number;
  contact: string;
  phone: string;
  email: string;
  national: boolean;
}

export interface BrokerPaginatedResponse {
  current_page: number;
  data: Broker[];
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

@Injectable({
  providedIn: 'root'
})
export class BrokerService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = '/api/brokers';

  getBrokers(params: any): Observable<BrokerPaginatedResponse> {
    let httpParams = new HttpParams();
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined) {
        httpParams = httpParams.set(key, params[key]);
      }
    });

    return this.http.get<BrokerPaginatedResponse>(this.apiUrl, { params: httpParams });
  }

  saveBroker(data: BrokerCreateUpdateRequest): Observable<any> {
    return this.http.post(this.apiUrl, data);
  }

  deleteBroker(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }

  activateBroker(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/${id}/activate`, {});
  }

  deactivateBroker(id: number): Observable<any> {
    return this.http.put(`${this.apiUrl}/${id}/deactivate`, {});
  }
}
