import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface City {
  id: number;
  name: string;
  states: string;
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
}
