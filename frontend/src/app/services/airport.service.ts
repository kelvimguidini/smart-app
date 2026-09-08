import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../environments/environment';

export interface Airport {
  id: number;
  iata_code: string;
  name: string;
  city: string;
  state?: string;
  country: string;
  active: boolean;
  formatted?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AirportService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  searchAirports(term: string): Observable<Airport[]> {
    let params = new HttpParams();
    if (term) {
      params = params.set('term', term);
    }
    return this.http.get<Airport[]>(`${this.apiUrl}/api/airports/search`, { params }).pipe(
      map(airports => (airports || []).map(a => ({
        ...a,
        formatted: `${a.name} (${a.iata_code})`
      })))
    );
  }
}
