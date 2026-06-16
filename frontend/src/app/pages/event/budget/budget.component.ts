import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxMaskDirective } from 'ngx-mask';
import { BudgetService } from '../../../services/budget.service';

@Component({
  selector: 'app-budget',
  standalone: true,
  imports: [CommonModule, FormsModule, NgxMaskDirective],
  templateUrl: './budget.component.html',
  styleUrls: ['./budget.component.scss']
})
export class BudgetComponent implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly budgetService = inject(BudgetService);

  // Core properties
  tokenValid = false;
  isLoading = true;
  isProcessing = false;
  isProcessingProve = false;
  
  event: any = {};
  providerCity = '';
  providerName = '';
  eventHotel: any = null;
  eventAb: any = null;
  eventHall: any = null;
  eventAdd: any = null;
  budget: any = {};
  prove = false;
  user = 0;
  token = '';
  tokenEvaluated = false;
  symbol = 'R$';

  // Flash message
  flash = {
    show: false,
    type: 'success',
    message: ''
  };

  // Form arrays and states
  comissionsHotel: number[] = [];
  valuesHotel: number[] = [];
  commentsHotel: string[] = [];
  idsOptHotel: number[] = [];
  eventHotelId: number | null = null;
  hostingFeeHotel = 0;
  ISSFeeHotel = 0;
  IVAFeeHotel = 0;
  commentHotel = '';
  itemIdsHotel: (number | null)[] = [];

  comissionsAb: number[] = [];
  valuesAb: number[] = [];
  commentsAb: string[] = [];
  idsOptAb: number[] = [];
  eventAbId: number | null = null;
  hostingFeeAb = 0;
  ISSFeeAb = 0;
  IVAFeeAb = 0;
  commentAb = '';
  itemIdsAb: (number | null)[] = [];

  comissionsAdd: number[] = [];
  valuesAdd: number[] = [];
  commentsAdd: string[] = [];
  idsOptAdd: number[] = [];
  eventAddId: number | null = null;
  hostingFeeAdd = 0;
  ISSFeeAdd = 0;
  IVAFeeAdd = 0;
  commentAdd = '';
  itemIdsAdd: (number | null)[] = [];

  comissionsHall: number[] = [];
  valuesHall: number[] = [];
  commentsHall: string[] = [];
  idsOptHall: number[] = [];
  eventHallId: number | null = null;
  hostingFeeHall = 0;
  ISSFeeHall = 0;
  IVAFeeHall = 0;
  commentHall = '';
  itemIdsHall: (number | null)[] = [];

  ngOnInit(): void {
    const tokenFromPath = this.route.snapshot.paramMap.get('token');
    const proveFromPath = this.route.snapshot.paramMap.get('prove');
    const userFromPath = this.route.snapshot.paramMap.get('user');

    this.route.queryParams.subscribe(queryParams => {
      const token = tokenFromPath || queryParams['token'] || '';
      const prove = (proveFromPath === 'true' || queryParams['prove'] === 'true' || proveFromPath === '1' || queryParams['prove'] === '1');
      const user = userFromPath ? parseInt(userFromPath, 10) : (queryParams['user'] ? parseInt(queryParams['user'], 10) : 0);

      this.token = token;
      this.prove = prove;
      this.user = user;

      if (token) {
        this.loadBudgetData(token, prove, user);
      } else {
        this.isLoading = false;
        this.tokenValid = false;
      }
    });
  }

  loadBudgetData(token: string, prove: boolean, user: number) {
    this.isLoading = true;
    this.budgetService.getBudget(token, prove, user).subscribe({
      next: (res) => {
        this.isLoading = false;
        this.tokenValid = res.tokenValid;
        if (this.tokenValid) {
          this.event = res.event;
          this.providerCity = res.providerCity;
          this.providerName = res.providerName;
          this.eventHotel = res.eventHotel;
          this.eventAb = res.eventAb;
          this.eventHall = res.eventHall;
          this.eventAdd = res.eventAdd;
          this.budget = res.budget;
          this.prove = res.prove;
          this.user = res.user;
          this.token = res.token;
          this.tokenEvaluated = res.tokenEvaluated;

          const currency = this.eventHotel?.currency ?? this.eventAb?.currency ?? this.eventHall?.currency ?? this.eventAdd?.currency;
          this.symbol = currency ? currency.symbol : 'R$';

          this.initializeForm();
        }
      },
      error: (err) => {
        this.isLoading = false;
        this.tokenValid = false;
        console.error('Error loading budget:', err);
      }
    });
  }

  initializeForm() {
    // Hotel
    if (this.eventHotel && this.eventHotel.event_hotels_opt) {
      this.eventHotelId = this.eventHotel.id;
      this.hostingFeeHotel = this.budget?.hosting_fee_hotel ?? 0;
      this.ISSFeeHotel = this.budget?.iss_fee_hotel ?? 0;
      this.IVAFeeHotel = this.budget?.iva_fee_hotel ?? 0;
      this.commentHotel = this.budget?.comment_hotel ?? '';

      this.eventHotel.event_hotels_opt.forEach((opt: any, index: number) => {
        const budgetItem = this.budget?.provider_budget_items?.find((i: any) => i.event_hotel_opt_id === opt.id);
        this.comissionsHotel[index] = budgetItem ? budgetItem.comission : 0;
        this.valuesHotel[index] = budgetItem ? budgetItem.value : 0;
        this.commentsHotel[index] = budgetItem ? budgetItem.comment : '';
        this.idsOptHotel[index] = opt.id;
        this.itemIdsHotel[index] = budgetItem ? budgetItem.id : null;
      });
    }

    // A&B
    if (this.eventAb && this.eventAb.event_ab_opts) {
      this.eventAbId = this.eventAb.id;
      this.hostingFeeAb = this.budget?.hosting_fee_ab ?? 0;
      this.ISSFeeAb = this.budget?.iss_fee_ab ?? 0;
      this.IVAFeeAb = this.budget?.iva_fee_ab ?? 0;
      this.commentAb = this.budget?.comment_ab ?? '';

      this.eventAb.event_ab_opts.forEach((opt: any, index: number) => {
        const budgetItem = this.budget?.provider_budget_items?.find((i: any) => i.event_ab_opt_id === opt.id);
        this.comissionsAb[index] = budgetItem ? budgetItem.comission : 0;
        this.valuesAb[index] = budgetItem ? budgetItem.value : 0;
        this.commentsAb[index] = budgetItem ? budgetItem.comment : '';
        this.idsOptAb[index] = opt.id;
        this.itemIdsAb[index] = budgetItem ? budgetItem.id : null;
      });
    }

    // Salão
    if (this.eventHall && this.eventHall.event_hall_opts) {
      this.eventHallId = this.eventHall.id;
      this.hostingFeeHall = this.budget?.hosting_fee_hall ?? 0;
      this.ISSFeeHall = this.budget?.iss_fee_hall ?? 0;
      this.IVAFeeHall = this.budget?.iva_fee_hall ?? 0;
      this.commentHall = this.budget?.comment_hall ?? '';

      this.eventHall.event_hall_opts.forEach((opt: any, index: number) => {
        const budgetItem = this.budget?.provider_budget_items?.find((i: any) => i.event_hall_opt_id === opt.id);
        this.comissionsHall[index] = budgetItem ? budgetItem.comission : 0;
        this.valuesHall[index] = budgetItem ? budgetItem.value : 0;
        this.commentsHall[index] = budgetItem ? budgetItem.comment : '';
        this.idsOptHall[index] = opt.id;
        this.itemIdsHall[index] = budgetItem ? budgetItem.id : null;
      });
    }

    // Adicionais
    if (this.eventAdd && this.eventAdd.event_add_opts) {
      this.eventAddId = this.eventAdd.id;
      this.hostingFeeAdd = this.budget?.hosting_fee_add ?? 0;
      this.ISSFeeAdd = this.budget?.iss_fee_add ?? 0;
      this.IVAFeeAdd = this.budget?.iva_fee_add ?? 0;
      this.commentAdd = this.budget?.comment_add ?? '';

      this.eventAdd.event_add_opts.forEach((opt: any, index: number) => {
        const budgetItem = this.budget?.provider_budget_items?.find((i: any) => i.event_add_opt_id === opt.id);
        this.comissionsAdd[index] = budgetItem ? budgetItem.comission : 0;
        this.valuesAdd[index] = budgetItem ? budgetItem.value : 0;
        this.commentsAdd[index] = budgetItem ? budgetItem.comment : '';
        this.idsOptAdd[index] = opt.id;
        this.itemIdsAdd[index] = budgetItem ? budgetItem.id : null;
      });
    }
  }

  daysBetween(date1: any, date2: any): number {
    const truncateToDate = (d: any) => {
      const date = new Date(d);
      date.setHours(0, 0, 0, 0);
      return date;
    };

    const one = truncateToDate(date1).getTime();
    const two = truncateToDate(date2).getTime();
    const difference = Math.abs(one - two);
    const days = Math.ceil(difference / (1000 * 60 * 60 * 24));
    return days === 0 ? 1 : days;
  }

  daysBetween1(date1: any, date2: any): number {
    const truncateToDate = (d: any) => {
      const date = new Date(d);
      date.setHours(0, 0, 0, 0);
      return date;
    };

    const one = truncateToDate(date1).getTime();
    const two = truncateToDate(date2).getTime();
    const difference = Math.abs(one - two);
    const result = Math.ceil(difference / (1000 * 60 * 60 * 24)) + 1;
    return result === 0 ? 1 : result;
  }

  submit() {
    this.isProcessing = true;

    const payload = {
      comissionsHotel: this.comissionsHotel,
      valuesHotel: this.valuesHotel,
      commentsHotel: this.commentsHotel,
      idsOptHotel: this.idsOptHotel,
      eventHotelId: this.eventHotelId,
      hostingFeeHotel: this.hostingFeeHotel,
      ISSFeeHotel: this.ISSFeeHotel,
      IVAFeeHotel: this.IVAFeeHotel,
      commentHotel: this.commentHotel,
      itemIdsHotel: this.itemIdsHotel,

      comissionsAb: this.comissionsAb,
      valuesAb: this.valuesAb,
      commentsAb: this.commentsAb,
      idsOptAb: this.idsOptAb,
      eventAbId: this.eventAbId,
      hostingFeeAb: this.hostingFeeAb,
      ISSFeeAb: this.ISSFeeAb,
      IVAFeeAb: this.IVAFeeAb,
      commentAb: this.commentAb,
      itemIdsAb: this.itemIdsAb,

      comissionsAdd: this.comissionsAdd,
      valuesAdd: this.valuesAdd,
      commentsAdd: this.commentsAdd,
      idsOptAdd: this.idsOptAdd,
      eventAddId: this.eventAddId,
      hostingFeeAdd: this.hostingFeeAdd,
      ISSFeeAdd: this.ISSFeeAdd,
      IVAFeeAdd: this.IVAFeeAdd,
      commentAdd: this.commentAdd,
      itemIdsAdd: this.itemIdsAdd,

      comissionsHall: this.comissionsHall,
      valuesHall: this.valuesHall,
      commentsHall: this.commentsHall,
      idsOptHall: this.idsOptHall,
      eventHallId: this.eventHallId,
      hostingFeeHall: this.hostingFeeHall,
      ISSFeeHall: this.ISSFeeHall,
      IVAFeeHall: this.IVAFeeHall,
      commentHall: this.commentHall,
      itemIdsHall: this.itemIdsHall,

      id: this.budget?.id || 0,
      eventId: this.event.id
    };

    this.budgetService.saveBudget(payload).subscribe({
      next: (res) => {
        this.isProcessing = false;
        this.flash = {
          show: true,
          type: res.type || 'success',
          message: res.message
        };
        this.loadBudgetData(this.token, this.prove, this.user);
      },
      error: (err) => {
        this.isProcessing = false;
        this.flash = {
          show: true,
          type: 'error',
          message: err.error?.message || 'Erro ao salvar o orçamento.'
        };
        console.error('Error saving budget:', err);
      }
    });
  }

  submitProve(decision: number) {
    this.isProcessingProve = true;
    const payload = {
      id: this.budget.id,
      decision: decision,
      user: this.user,
      token: this.token
    };

    this.budgetService.proveBudget(payload).subscribe({
      next: (res) => {
        this.isProcessingProve = false;
        this.flash = {
          show: true,
          type: res.type || 'success',
          message: res.message
        };
        this.loadBudgetData(this.token, this.prove, this.user);
      },
      error: (err) => {
        this.isProcessingProve = false;
        this.flash = {
          show: true,
          type: 'error',
          message: err.error?.message || 'Erro ao avaliar o orçamento.'
        };
        console.error('Error proving budget:', err);
      }
    });
  }
}
