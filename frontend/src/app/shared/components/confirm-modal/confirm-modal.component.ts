import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalComponent } from '../modal/modal.component';

@Component({
  selector: 'app-confirm-modal',
  standalone: true,
  imports: [CommonModule, ModalComponent],
  templateUrl: './confirm-modal.component.html',
  styleUrls: ['./confirm-modal.component.scss']
})
export class ConfirmModalComponent {
  @Input() modalTitle: string = 'Atenção';
  @Input() message: string = 'Tem certeza que deseja apagar esse registro?';
  @Input() okButtonLabel: string = 'Confirmar';
  @Input() cancelButtonLabel: string = 'Fechar';
  @Input() btnClass: string = 'btn btn-primary';
  @Input() btnDisabled: boolean = false;
  @Input() contentBig: boolean = false;
  @Input() tooltip: string = '';

  @Output() confirm = new EventEmitter<void>();

  showModal: boolean = false;

  constructor() {}

  openModal(event: Event) {
    event.preventDefault();
    this.showModal = true;
  }

  closeModal() {
    this.showModal = false;
  }

  onConfirm() {
    this.confirm.emit();
    this.closeModal();
  }
}
