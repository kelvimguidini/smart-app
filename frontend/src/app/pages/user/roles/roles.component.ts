import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RoleService, Role, Permission, RoleCreateUpdateRequest, RoleListResponse } from '../../../services/role.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';

@Component({
  selector: 'app-roles',
  standalone: true,
  imports: [CommonModule, FormsModule, AuthenticatedLayoutComponent, ConfirmModalComponent, DatatableComponent],
  templateUrl: './roles.component.html',
  styleUrls: ['./roles.component.scss'],
})
export class RolesComponent implements OnInit {
  private readonly roleService: RoleService = inject(RoleService);
  private readonly toastService: ToastService = inject(ToastService);

  roles: Role[] = [];
  permissionList: Permission[] = [];

  // Controle do Formulário e UI
  inEdition: number = 0;
  isLoader: boolean = false;
  processing: boolean = false;
  errors: any = {};

  form = {
    id: 0,
    name: '',
    permissions: [] as string[],
    active: true,
  };

  // Estado do Datatable
  pagination: any = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0,
  };
  currentPage: number = 1;
  perPage: number = 10;
  searchQuery: string = '';
  sortColumn: string = 'id';
  sortDirection: string = 'desc';

  ngOnInit(): void {
    this.loadRoles();
  }

  /**
   * Carregar roles e permissões disponíveis
   */
  loadRoles(): void {
    this.isLoader = true;

    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection,
    };

    this.roleService.getRoles(params).subscribe({
      next: (response: RoleListResponse) => {
        this.roles = response.roles.data || [];
        this.permissionList = response.permissionList || [];
        this.pagination = response.roles;
        this.isLoader = false;
      },
      error: (error: any) => {
        this.isLoader = false;
        this.toastService.error('Erro ao carregar grupos de acesso');
        console.error('Erro ao carregar roles:', error);
      },
    });
  }

  // --- DATATABLE HANDLers ---

  onSearch(query: string): void {
    this.searchQuery = query;
    this.currentPage = 1;
    this.loadRoles();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadRoles();
  }

  onPerPageChange(perPage: number): void {
    this.perPage = perPage;
    this.currentPage = 1;
    this.loadRoles();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadRoles();
  }

  // --- FORMULÁRIO ---

  edit(role: Role): void {
    this.inEdition = role.id;
    this.form.id = role.id;
    this.form.name = role.name;
    this.form.active = role.active;
    this.form.permissions = role.permissions ? role.permissions.map((p) => p.name) : [];
    this.errors = {};

    // Scroll para o topo
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  cancelEdit(): void {
    this.inEdition = 0;
    this.resetForm();
  }

  togglePermission(permissionName: string, event: Event): void {
    const isChecked = (event.target as HTMLInputElement).checked;
    if (isChecked) {
      if (!this.form.permissions.includes(permissionName)) {
        this.form.permissions.push(permissionName);
      }
    } else {
      this.form.permissions = this.form.permissions.filter((p) => p !== permissionName);
    }
  }

  private resetForm(): void {
    this.form = {
      id: 0,
      name: '',
      permissions: [],
      active: true,
    };
    this.errors = {};
  }

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

  submit(): void {
    if (!this.validateForm()) {
      return;
    }

    this.processing = true;

    const data: RoleCreateUpdateRequest = {
      id: this.form.id,
      name: this.form.name,
      permissions: this.form.permissions,
      active: this.form.active,
    };

    this.roleService.saveRole(data).subscribe({
      next: (response: any) => {
        this.processing = false;
        this.toastService.success('Grupo de acesso salvo com sucesso');
        this.resetForm();
        this.inEdition = 0;
        this.loadRoles();
      },
      error: (error: any) => {
        this.processing = false;
        if (error.status === 422) {
          this.errors = error.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar grupo de acesso');
        }
        console.error('Erro ao salvar role:', error);
      },
    });
  }

  deleteRole(roleId: number): void {
    this.isLoader = true;
    this.roleService.deleteRole(roleId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success('Grupo de acesso apagado com sucesso');
        this.loadRoles();
      },
      error: (error: any) => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar grupo de acesso');
        console.error('Erro ao deletar role:', error);
      },
    });
  }

  removePermission(roleId: number, permissionId: number): void {
    this.isLoader = true;
    this.closePermissionsModal();
    this.roleService.removePermission(roleId, permissionId).subscribe({
      next: (response: any) => {
        this.isLoader = false;
        this.toastService.success('Permissão removida com sucesso');
        this.loadRoles();
      },
      error: (error: any) => {
        this.isLoader = false;
        this.toastService.error('Erro ao remover permissão');
        console.error('Erro ao remover permissão:', error);
      },
    });
  }

  isRoleInEdition(roleId: number): boolean {
    return this.inEdition === roleId;
  }

  showPermissionsModalId: number | null = null;

  /**
   * Abre o modal de permissões via binding do Angular
   */
  openPermissionsModal(role: Role): void {
    this.showPermissionsModalId = role.id;
    document.body.classList.add('modal-open');
  }

  closePermissionsModal(): void {
    this.showPermissionsModalId = null;
    document.body.classList.remove('modal-open');
  }
}
