import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BudgetService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  getBudget(token: string, prove: boolean = false, user: number = 0): Observable<any> {
    let params = new HttpParams()
      .set('token', token)
      .set('prove', prove.toString())
      .set('user', user.toString());
    return this.http.get<any>(`${this.apiUrl}/api/budget`, { params });
  }

  saveBudget(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/budget-save`, data);
  }

  proveBudget(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/budget-prove`, data);
  }
}
