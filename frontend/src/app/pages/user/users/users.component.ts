import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService, User } from '../../../services/user.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { DatatableComponent } from '../../../shared/components/datatable/datatable.component';
import { ConfirmModalComponent } from '../../../shared/components/confirm-modal/confirm-modal.component';
import { ModalComponent } from '../../../shared/components/modal/modal.component';
import { QuillModule } from 'ngx-quill';
import { NgxMaskDirective } from 'ngx-mask';

@Component({
  selector: 'app-users',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, AuthenticatedLayoutComponent, DatatableComponent, ConfirmModalComponent, ModalComponent, QuillModule, NgxMaskDirective],
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.scss']
})
export class UsersComponent implements OnInit {
  users: User[] = [];
  roles: any[] = [];
  userForm: FormGroup;
  isLoader = false;
  processing = false;
  userInEdition = 0;
  showRolesModalId = 0;
  showModal = false;
  errors: any = {};
  
  quillModules = {
    toolbar: [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote', 'code-block'],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'color': [] }, { 'background': [] }],
      ['link', 'image']
    ]
  };

  // Pagination state
  pagination: any = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0,
  };
  currentPage = 1;
  perPage = 10;
  searchQuery = '';
  sortColumn = 'id';
  sortDirection = 'desc';

  constructor(
    private userService: UserService,
    private toastService: ToastService,
    private fb: FormBuilder
  ) {
    this.userForm = this.fb.group({
      id: [0],
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', Validators.required],
      signature: [''],
      roles: [[], [Validators.required, (c: any) => c.value && c.value.length > 0 ? null : { required: true }]]
    });
  }

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.isLoader = true;
    
    const params = {
      page: this.currentPage,
      per_page: this.perPage,
      search: this.searchQuery,
      sort_column: this.sortColumn,
      sort_direction: this.sortDirection
    };

    this.userService.getUsers(params).subscribe({
      next: (res) => {
        this.users = res.users.data || [];
        this.roles = res.roles || [];
        this.pagination = res.users;
        this.isLoader = false;
      },
      error: () => {
        this.isLoader = false;
        this.toastService.error('Erro ao carregar dados');
      }
    });
  }

  onSearch(query: string): void {
    this.searchQuery = query;
    this.currentPage = 1;
    this.loadUsers();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadUsers();
  }

  onPerPageChange(perPage: number): void {
    this.perPage = perPage;
    this.currentPage = 1;
    this.loadUsers();
  }

  sortBy(column: string): void {
    if (this.sortColumn === column) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortColumn = column;
      this.sortDirection = 'asc';
    }
    this.loadUsers();
  }

  openModal(): void {
    this.resetForm();
    this.showModal = true;
  }

  closeModal(): void {
    this.showModal = false;
    this.resetForm();
  }

  submit(): void {
    this.userForm.markAllAsTouched();
    if (this.userForm.invalid) return;
    this.processing = true;
    this.errors = {};

    this.userService.saveUser(this.userForm.value).subscribe({
      next: () => {
        this.toastService.success('Registro salvo com sucesso');
        this.closeModal();
        this.loadUsers();
      },
      error: (err: any) => {
        this.processing = false;
        if (err.status === 422) {
          this.errors = err.error.errors || {};
        } else {
          this.toastService.error('Erro ao salvar');
        }
        console.error(err);
      }
    });
  }

  edit(user: User): void {
    this.userInEdition = user.id;
    this.errors = {};
    this.userForm.patchValue({
      id: user.id,
      name: user.name || '',
      email: user.email || '',
      phone: user.phone || '',
      signature: user.signature || '',
      roles: user.roles?.map((r: any) => r.id) || []
    });
    this.showModal = true;
  }

  deleteUser(id: number): void {
    this.isLoader = true;
    this.userService.deleteUser(id).subscribe({
      next: () => {
        this.toastService.success('Registro apagado com sucesso');
        this.loadUsers();
      },
      error: () => {
        this.isLoader = false;
        this.toastService.error('Erro ao apagar');
      }
    });
  }

  activate(id: number): void {
    this.isLoader = true;
    this.userService.activateUser(id).subscribe({
      next: () => {
        this.toastService.success('Usuário ativado');
        this.loadUsers();
      },
      error: () => {
        this.isLoader = false;
        this.toastService.error('Erro ao ativar usuário');
      }
    });
  }

  deactivate(id: number): void {
    this.isLoader = true;
    this.userService.deactivateUser(id).subscribe({
      next: () => {
        this.toastService.success('Usuário inativado');
        this.loadUsers();
      },
      error: () => {
        this.isLoader = false;
        this.toastService.error('Erro ao inativar usuário');
      }
    });
  }

  removeRole(userId: number, roleId: number): void {
    this.userService.removeRole(userId, roleId).subscribe({
      next: () => {
        this.toastService.success('Grupo removido');
        // Refresh local data to keep modal updated without closing it
        this.loadUsers();
      },
      error: () => {
        this.toastService.error('Erro ao remover');
      }
    });
  }

  openRolesModal(user: User): void {
    this.showRolesModalId = user.id;
  }

  closeRolesModal(): void {
    this.showRolesModalId = 0;
  }

  onRoleChange(event: any, roleId: number): void {
    const rolesControl = this.userForm.get('roles');
    const roles = rolesControl?.value || [];
    if (event.target.checked) {
      roles.push(roleId);
    } else {
      const index = roles.indexOf(roleId);
      if (index > -1) {
        roles.splice(index, 1);
      }
    }
    rolesControl?.setValue(roles);
    rolesControl?.markAsTouched();
  }

  hasRole(roleId: number): boolean {
    const roles = this.userForm.get('roles')?.value || [];
    return roles.includes(roleId);
  }

  resetForm(): void {
    this.userForm.reset({ id: 0, roles: [] });
    this.userInEdition = 0;
    this.processing = false;
    this.errors = {};
  }
}
