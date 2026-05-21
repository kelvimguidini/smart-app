import { Component, EventEmitter, Input, Output, ElementRef, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DragDropModule } from '@angular/cdk/drag-drop';

@Component({
  selector: 'app-confirm-modal',
  standalone: true,
  imports: [CommonModule, DragDropModule],
  templateUrl: './confirm-modal.component.html',
  styleUrls: ['./confirm-modal.component.scss']
})
export class ConfirmModalComponent implements OnInit, OnDestroy {
  @Input() modalTitle: string = 'Atenção';
  @Input() message: string = 'Tem certeza que deseja apagar esse registro?';
  @Input() okButtonLabel: string = 'Confirmar';
  @Input() cancelButtonLabel: string = 'Fechar';
  @Input() btnClass: string = 'btn btn-primary';
  @Input() btnDisabled: boolean = false;
  @Input() contentBig: boolean = false;

  @Output() confirm = new EventEmitter<void>();

  @ViewChild('modalContainer', { static: true }) modalContainer!: ElementRef;

  modalId: string = 'modal-' + Math.floor(Date.now() * Math.random()).toString(36);
  showModal: boolean = false;

  constructor() {}

  ngOnInit() {
    // Adiciona apenas a camada do modal e backdrop ao final do body 
    // preservando o botão acionador no local original da tela
    if (this.modalContainer && this.modalContainer.nativeElement) {
      document.body.appendChild(this.modalContainer.nativeElement);
    }
  }

  ngOnDestroy() {
    // Limpa a camada do body ao destruir o componente
    if (this.modalContainer && this.modalContainer.nativeElement && this.modalContainer.nativeElement.parentNode === document.body) {
      document.body.removeChild(this.modalContainer.nativeElement);
    }
  }

  openModal(event: Event) {
    event.preventDefault();
    this.showModal = true;
    document.body.classList.add('modal-open');
  }

  closeModal() {
    this.showModal = false;
    document.body.classList.remove('modal-open');
  }

  onConfirm() {
    this.confirm.emit();
    this.closeModal();
  }
}
