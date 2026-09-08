import {
  Directive,
  ElementRef,
  EventEmitter,
  forwardRef,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  Output,
  SimpleChanges
} from '@angular/core';
import { ControlValueAccessor, NG_VALUE_ACCESSOR } from '@angular/forms';
import flatpickr from 'flatpickr';
import { Instance } from 'flatpickr/dist/types/instance';

@Directive({
  selector: '[appFlatpickr]',
  standalone: true,
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => FlatpickrDirective),
      multi: true
    }
  ]
})
export class FlatpickrDirective implements OnInit, OnChanges, OnDestroy, ControlValueAccessor {
  @Input() mode: 'single' | 'range' | 'multiple' = 'single';
  @Input() altFormat: string = 'd/m/Y';
  @Input() dateFormat: string = 'Y-m-d';
  @Input() minDate?: string | Date;
  @Input() maxDate?: string | Date;
  @Input() enableTime: boolean = false;

  @Output() dateChange = new EventEmitter<any>();

  private fpInstance: Instance | null = null;
  private onChange: (value: any) => void = () => {};
  private onTouched: () => void = () => {};
  private innerValue: any = null;

  constructor(private el: ElementRef) {}

  ngOnInit() {
    this.initFlatpickr();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (this.fpInstance) {
      if (changes['minDate'] && this.minDate !== undefined) {
        this.fpInstance.set('minDate', this.minDate);
      }
      if (changes['maxDate'] && this.maxDate !== undefined) {
        this.fpInstance.set('maxDate', this.maxDate);
      }
    }
  }

  ngOnDestroy() {
    if (this.fpInstance) {
      this.fpInstance.destroy();
      this.fpInstance = null;
    }
  }

  private initFlatpickr() {
    this.fpInstance = flatpickr(this.el.nativeElement, {
      mode: this.mode,
      dateFormat: this.dateFormat,
      altInput: true,
      altFormat: this.altFormat,
      altInputClass: this.el.nativeElement.className,
      disableMobile: true,
      locale: {
        firstDayOfWeek: 1,
        weekdays: {
          shorthand: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
          longhand: [
            'Domingo',
            'Segunda-feira',
            'Terça-feira',
            'Quarta-feira',
            'Quinta-feira',
            'Sexta-feira',
            'Sábado'
          ]
        },
        months: {
          shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
          longhand: [
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
          ]
        },
        rangeSeparator: ' até '
      },
      onChange: (selectedDates, dateStr) => {
        let valueToEmit: any = dateStr;
        if (this.mode === 'single') {
          valueToEmit = selectedDates.length ? this.formatDate(selectedDates[0]) : '';
        }
        this.innerValue = valueToEmit;
        this.onChange(valueToEmit);
        this.dateChange.emit(valueToEmit);
      },
      onClose: () => {
        this.onTouched();
      }
    });

    if (this.innerValue) {
      this.fpInstance.setDate(this.innerValue, false);
    }
  }

  private formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  writeValue(value: any): void {
    this.innerValue = value;
    if (this.fpInstance) {
      if (value) {
        this.fpInstance.setDate(value, false);
      } else {
        this.fpInstance.clear();
      }
    }
  }

  registerOnChange(fn: any): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: any): void {
    this.onTouched = fn;
  }

  setDisabledState?(isDisabled: boolean): void {
    if (this.fpInstance) {
      if (isDisabled) {
        this.fpInstance.input.setAttribute('disabled', 'disabled');
        if (this.fpInstance.altInput) {
          this.fpInstance.altInput.setAttribute('disabled', 'disabled');
        }
      } else {
        this.fpInstance.input.removeAttribute('disabled');
        if (this.fpInstance.altInput) {
          this.fpInstance.altInput.removeAttribute('disabled');
        }
      }
    }
  }
}
