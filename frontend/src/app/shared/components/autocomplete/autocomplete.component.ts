import { Component, Input, Output, EventEmitter, forwardRef, OnInit, OnDestroy, OnChanges, SimpleChanges, ElementRef, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, NG_VALUE_ACCESSOR, ControlValueAccessor } from '@angular/forms';
import { Subject, Subscription, Observable } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap, finalize } from 'rxjs/operators';

@Component({
  selector: 'app-autocomplete',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './autocomplete.component.html',
  styleUrls: ['./autocomplete.component.scss'],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => AutocompleteComponent),
      multi: true
    }
  ]
})
export class AutocompleteComponent implements ControlValueAccessor, OnInit, OnDestroy, OnChanges {
  @Input() label: string = '';
  @Input() id: string = 'autocomplete';
  @Input() placeholder: string = 'Digite para buscar...';
  @Input() required: boolean = false;
  @Input() searchFn!: (term: string) => Observable<any[]>;
  @Input() displayFn!: (item: any) => string;
  @Input() valueField: string = 'id';
  @Input() initialText: string = '';
  @Input() errors: any[] = [];
  @Input() disabled: boolean = false;

  inputText: string = '';
  searchResults: any[] = [];
  isSearching: boolean = false;
  showDropdown: boolean = false;
  
  private searchSubject = new Subject<string>();
  private searchSubscription!: Subscription;
  private selectedItem: any = null;

  // ControlValueAccessor
  private innerValue: any = null;
  onChange: any = () => {};
  onTouched: any = () => {};

  constructor(private eRef: ElementRef) {}

  ngOnInit() {
    this.inputText = this.initialText;

    this.searchSubscription = this.searchSubject.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      switchMap((term: string) => {
        this.isSearching = true;
        return this.searchFn(term).pipe(
          finalize(() => this.isSearching = false)
        );
      })
    ).subscribe({
      next: (results: any[]) => {
        this.searchResults = results;
        this.showDropdown = true;
      },
      error: () => {
        this.searchResults = [];
        this.showDropdown = true;
      }
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['initialText'] && changes['initialText'].currentValue !== undefined) {
       this.inputText = changes['initialText'].currentValue;
    }
  }

  ngOnDestroy() {
    if (this.searchSubscription) {
      this.searchSubscription.unsubscribe();
    }
  }

  onInput(event: any) {
    const term = event.target.value;
    this.inputText = term;
    
    // Clear selection state when typing
    if (this.selectedItem && this.displayFn(this.selectedItem) !== term) {
        this.selectedItem = null;
        this.innerValue = null;
        this.onChange(this.innerValue);
    }

    if (term.length >= 2) {
      this.searchSubject.next(term);
    } else {
      this.showDropdown = false;
      this.searchResults = [];
    }
  }

  selectItem(item: any, event?: MouseEvent) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    this.selectedItem = item;
    const displayText = this.displayFn ? this.displayFn(item) : (item.name || item);
    this.inputText = displayText;
    this.innerValue = this.valueField ? (item[this.valueField] !== undefined ? item[this.valueField] : displayText) : displayText;
    this.showDropdown = false;
    this.onChange(this.innerValue);
  }

  onBlur() {
    this.onTouched();
    setTimeout(() => {
      this.showDropdown = false;
      // Validation rule: if text doesn't match selected item, clear text
      if (!this.selectedItem) {
          if (this.innerValue && (this.inputText === this.initialText || this.inputText === this.innerValue)) {
              return; // valid untouched edit state
          }
          if (!this.inputText) {
              this.innerValue = null;
              this.onChange(this.innerValue);
          }
      } else if (this.displayFn && this.displayFn(this.selectedItem) !== this.inputText) {
          if (this.innerValue && this.inputText === this.innerValue) {
              return;
          }
          this.inputText = '';
          this.selectedItem = null;
          this.innerValue = null;
          this.onChange(this.innerValue);
      }
    }, 200);
  }

  @HostListener('document:click', ['$event'])
  clickout(event: Event) {
    if (!this.eRef.nativeElement.contains(event.target)) {
      this.showDropdown = false;
    }
  }

  // ControlValueAccessor methods
  writeValue(value: any): void {
    this.innerValue = value;
    if (!value) {
        this.inputText = '';
        this.selectedItem = null;
        this.initialText = '';
    } else if (typeof value === 'string' && (!this.inputText || this.inputText !== value)) {
        this.inputText = value;
    }
  }

  registerOnChange(fn: any): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: any): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }
}
