import { Component, EventEmitter, Input, OnInit, Output, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Subject, Subscription } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';

@Component({
  selector: 'app-datatable',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './datatable.component.html',
  styleUrls: ['./datatable.component.scss']
})
export class DatatableComponent implements OnInit, OnDestroy {
  @Input() pagination: any = {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
    from: 0,
    to: 0
  };
  @Input() loading: boolean = false;
  @Input() perPageOptions: number[] = [10, 20, 50, 100];
  
  @Output() pageChange = new EventEmitter<number>();
  @Output() search = new EventEmitter<string>();
  @Output() perPageChange = new EventEmitter<number>();

  searchQuery: string = '';
  private searchSubject = new Subject<string>();
  private searchSubscription?: Subscription;

  ngOnInit(): void {
    this.searchSubscription = this.searchSubject.pipe(
      debounceTime(500),
      distinctUntilChanged()
    ).subscribe(value => {
      this.search.emit(value);
    });
  }

  ngOnDestroy(): void {
    this.searchSubscription?.unsubscribe();
  }

  onSearchInput(): void {
    this.searchSubject.next(this.searchQuery);
  }

  onPageClick(page: number | string): void {
    if (page !== '...' && page !== this.pagination.current_page) {
      this.pageChange.emit(Number(page));
    }
  }

  onPerPageSelect(): void {
    this.perPageChange.emit(this.pagination.per_page);
  }

  /**
   * Retorna os números das páginas visíveis (máximo 6 slots com '...')
   */
  getVisiblePages(): (number | string)[] {
    const total = this.pagination.last_page;
    const current = this.pagination.current_page;
    const pages: (number | string)[] = [];

    if (total <= 6) {
      for (let i = 1; i <= total; i++) pages.push(i);
      return pages;
    }

    pages.push(1);

    if (current <= 3) {
      pages.push(2, 3, 4, '...', total);
    } else if (current >= total - 2) {
      pages.push('...', total - 3, total - 2, total - 1, total);
    } else {
      pages.push('...', current, current + 1, '...', total);
    }

    return pages;
  }
}
