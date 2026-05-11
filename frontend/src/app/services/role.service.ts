import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Permission {
  id: number;
  name: string;
  title: string;
  description?: string;
}

export interface Role {
  id: number;
  name: string;
  active: boolean;
  permissions: Permission[];
  created_at?: string;
  updated_at?: string;
}

export interface RoleCreateUpdateRequest {
  id?: number;
  name: string;
  permissions: string[];
  active?: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class RoleService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  /**
   * Obter lista de roles com suas permissões e lista de permissões disponíveis
   */
  getRoles(): Observable<any> {
    return this.http.get(`${this.apiUrl}/api/roles`);
  }

  /**
   * Salvar nova role ou atualizar existente
   */
  saveRole(data: RoleCreateUpdateRequest): Observable<any> {
    return this.http.post(`${this.apiUrl}/api/roles`, data);
  }

  /**
   * Deletar uma role
   */
  deleteRole(roleId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/roles`, {
      body: { id: roleId }
    });
  }

  /**
   * Remover uma permissão de uma role
   */
  removePermission(roleId: number, permissionId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/api/roles/permission`, {
      body: { role_id: roleId, permission_id: permissionId }
    });
  }
}
