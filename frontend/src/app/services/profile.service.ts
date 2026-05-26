import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { User } from './user.service';

@Injectable({
  providedIn: 'root'
})
export class ProfileService {
  private apiUrl = `${environment.apiUrl}/api/profile`;

  constructor(private http: HttpClient) { }

  getProfile(): Observable<{ user: User }> {
    return this.http.get<{ user: User }>(this.apiUrl);
  }

  saveProfile(profile: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, profile);
  }
}
