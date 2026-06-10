import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ProfileService } from '../../../services/profile.service';
import { ToastService } from '../../../services/toast.service';
import { AuthenticatedLayoutComponent } from '../../../shared/layouts/authenticated-layout/authenticated-layout.component';
import { QuillModule } from 'ngx-quill';
import { NgxMaskDirective } from 'ngx-mask';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, AuthenticatedLayoutComponent, QuillModule, NgxMaskDirective],
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit {
  profileForm: FormGroup;
  isLoader = false;
  processing = false;
  roles: any[] = [];
  
  quillModules = {
    toolbar: [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote', 'code-block'],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'color': [] }, { 'background': [] }],
      ['link', 'image']
    ]
  };

  constructor(
    private profileService: ProfileService,
    private toastService: ToastService,
    private fb: FormBuilder
  ) {
    this.profileForm = this.fb.group({
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', Validators.required],
      signature: ['']
    });
  }

  ngOnInit(): void {
    this.loadProfile();
  }

  loadProfile(): void {
    this.isLoader = true;
    this.profileService.getProfile().subscribe({
      next: (res) => {
        const user = res.user;
        this.profileForm.patchValue({
          name: user.name || '',
          email: user.email || '',
          phone: user.phone || '',
          signature: user.signature || ''
        });
        this.roles = user.roles || [];
        this.isLoader = false;
      },
      error: () => {
        this.isLoader = false;
        this.toastService.error('Erro ao carregar dados do perfil');
      }
    });
  }

  submit(): void {
    if (this.profileForm.invalid) return;
    this.processing = true;
    this.profileService.saveProfile(this.profileForm.value).subscribe({
      next: () => {
        this.toastService.success('Perfil salvo com sucesso');
        this.processing = false;
      },
      error: () => {
        this.processing = false;
        this.toastService.error('Erro ao salvar perfil');
      }
    });
  }
}
