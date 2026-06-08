import { Component, EventEmitter, Input, Output, ElementRef, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DragDropModule } from '@angular/cdk/drag-drop';

@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [CommonModule, DragDropModule],
  templateUrl: './modal.component.html',
  styleUrls: ['./modal.component.scss']
})
export class ModalComponent implements OnInit, OnDestroy {
  @Input() show: boolean = false;
  @Input() title: string = '';
  @Input() icon: string = ''; // e.g. 'fa-hotel' or 'fas fa-hotel'
  @Input() size: 'sm' | 'md' | 'lg' | 'xl' | '' = '';

  @Output() showChange = new EventEmitter<boolean>();
  @Output() close = new EventEmitter<void>();

  @ViewChild('modalContainer', { static: true }) modalContainer!: ElementRef;

  constructor() {}

  ngOnInit() {
    if (this.modalContainer && this.modalContainer.nativeElement) {
      document.body.appendChild(this.modalContainer.nativeElement);
    }
  }

  ngOnDestroy() {
    if (this.modalContainer && this.modalContainer.nativeElement && this.modalContainer.nativeElement.parentNode === document.body) {
      document.body.removeChild(this.modalContainer.nativeElement);
    }
  }

  closeModal() {
    this.show = false;
    this.showChange.emit(false);
    this.close.emit();
  }
}
