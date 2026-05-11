import { Component, inject, OnInit, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { RoleService, Role, Permission, RoleCreateUpdateRequest } from '../../services/role.service';
import { ToastService } from '../../services/toast.service';
import { environment } from '../../../environments/environment';
import { AuthenticatedLayoutComponent } from "../../shared/layouts/authenticated-layout/authenticated-layout.component";

declare var $: any; // jQuery

@Component({
  selector: 'app-roles',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent],
  templateUrl: './roles.component.html',
  styleUrls: ['./roles.component.scss']
})
export class RolesComponent implements OnInit {
  private readonly roleService: RoleService = inject(RoleService);
  private readonly toastService: ToastService = inject(ToastService);
  private readonly http: HttpClient = inject(HttpClient);

  roles: Role[] = [];
  permissionList: Permission[] = [];
  inEdition: number = 0;
  isLoader: boolean = false;
  processing: boolean = false;
  errors: any = {};

  form = {
    id: 0,
    name: '',
    permissions: [] as string[]
  };

  formDelete = {
    id: 0,
    processing: false
  };

  formRemovePermission = {
    role_id: 0,
    permission_id: 0,
    processing: false
  };

  @ViewChild('dataTable') dataTable?: ElementRef;

  ngOnInit(): void {
    this.loadRoles();
  }

  /**
   * Carregar roles e permissões disponíveis
   */
  loadRoles(): void {
    this.isLoader = true;
    this.roleService.getRoles().subscribe({
      next: (response: any) => {
        this.roles = response.roles || [];
        this.permissionList = response.permissionList || [];
        this.isLoader = false;
        
        // Inicializar DataTable após os dados estarem prontos
        setTimeout(() => {
          this.initializeDataTable();
        }, 100);
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao carregar grupos de acesso');
        console.error('Erro ao carregar roles:', error);
      }
    });
  }

  /**
   * Inicializar DataTable jQuery
   */
  private initializeDataTable(): void {
    try {
      if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
      }
      
      $('#dataTable').DataTable({
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        },
        pageLength: 10,
        ordering: true,
        searching: true
      });
    } catch (e) {
      console.warn('DataTable não inicializado:', e);
    }
  }

  /**
   * Editar role
   */
  edit(role: Role): void {
    this.inEdition = role.id;
    this.form.id = role.id;
    this.form.name = role.name;
    this.form.permissions = role.permissions.map(p => p.name);
    this.errors = {};
  }

  /**
   * Cancelar edição
   */
  cancelEdit(): void {
    this.inEdition = 0;
    this.resetForm();
  }

  /**
   * Resetar formulário
   */
  private resetForm(): void {
    this.form = {
      id: 0,
      name: '',
      permissions: []
    };
    this.errors = {};
  }

  /**
   * Validar formulário
   */
  private validateForm(): boolean {
    this.errors = {};

    if (!this.form.name || this.form.name.trim() === '') {
      this.errors.name = ['O nome é obrigatório'];
      return false;
    }

    if (this.form.permissions.length === 0) {
      this.errors.permissions = ['Selecione pelo menos uma permissão'];
      return false;
    }

    return true;
  }

  /**
   * Salvar role (criar ou atualizar)
   */
  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: RoleCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
      permissions: this.form.permissions,
      active: true
    };

    this.roleService.saveRole(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success('Grupo de acesso salvo com sucesso');
        this.resetForm();
        this.inEdition = 0;
        this.loadRoles();
      },
      error: (error: HttpErrorResponse) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar grupo de acesso');
        }
        console.error('Erro ao salvar role:', error);
      }
    });
  }

  /**
   * Deletar role
   */
  deleteRole(roleId: number): void {
    if (!confirm('Tem certeza que deseja apagar esse registro?')) {
      return;
    }

    this.isLoader = true;
    this.roleService.deleteRole(roleId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success('Grupo de acesso apagado com sucesso');
        this.loadRoles();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar grupo de acesso');
        console.error('Erro ao deletar role:', error);
      }
    });
  }

  /**
   * Remover permissão de uma role
   */
  removePermission(roleId: number, permissionId: number): void {
    if (!confirm('Tem certeza que deseja remover essa permissão?')) {
      return;
    }

    this.isLoader = true;
    this.roleService.removePermission(roleId, permissionId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success('Permissão removida com sucesso');
        this.loadRoles();
      },
      error: (error: HttpErrorResponse) => {
        this.isLoader = false;
        this.toastService.error('Erro ao remover permissão');
        console.error('Erro ao remover permissão:', error);
      }
    });
  }

  /**
   * Obter título da permissão
   */
  getPermissionTitle(permissionName: string): string {
    const permission = this.permissionList.find(p => p.name === permissionName);
    return permission?.title || permissionName;
  }

  /**
   * Método auxiliar para template
   */
  isRoleInEdition(roleId: number): boolean {
    return this.inEdition === roleId;
  }

  /**
   * Gerar cor aleatória para gráficos (se necessário)
   */
  generateColor(): string {
    const colors = [
      '#4e73df', '#858796', '#1cc88a', '#36b9cc',
      '#f6c23e', '#e74c3c', '#9b59b6', '#3498db'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
  }
}
