import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  signature?: string;
  roles?: any[];
  active?: boolean;
  is_api_user?: boolean | number;
  email_verified_at?: string | null;
}

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = `${environment.apiUrl}/api/users`;

  constructor(private http: HttpClient) { }

  getUsers(params: any = {}): Observable<any> {
    return this.http.get<any>(this.apiUrl, { params });
  }

  saveUser(user: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, user);
  }

  deleteUser(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/${id}`);
  }

  activateUser(id: number): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}/${id}/activate`, {});
  }

  deactivateUser(id: number): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}/${id}/deactivate`, {});
  }

  removeRole(userId: number, roleId: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/role`, {
      body: { user_id: userId, role_id: roleId }
    });
  }
}
