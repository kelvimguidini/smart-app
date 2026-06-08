import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class EventService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  // 1. EVENT BASIC CRUD & DATA FETCHING
  getEvents(params: any = {}): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/api/events/list`, { params });
  }

  getEditData(id: number, params: any = {}): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/api/events/${id}/edit-data`, { params });
  }

  saveEvent(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/events`, data);
  }

  deleteEvent(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/events/${id}`);
  }

  saveExchangeRate(eventId: number, exchangeRates: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/events/save-exchange-rate`, { event_id: eventId, exchange_rates: exchangeRates });
  }

  saveValorFaturamento(eventId: number, value: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/events/save-vl-faturamento`, { event_id: eventId, vl_faturamento: value });
  }

  // 2. HOTEL ENDPOINTS
  saveEventHotel(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-hotels`, data);
  }

  deleteEventHotel(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-hotels/${id}`);
  }

  saveHotelOpt(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-hotels/opts`, data);
  }

  deleteHotelOpt(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-hotels/opts/${id}`);
  }

  // 3. A&B (Food & Beverage) ENDPOINTS
  saveEventAB(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-abs`, data);
  }

  deleteEventAB(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-abs/${id}`);
  }

  saveABOpt(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-abs/opts`, data);
  }

  deleteABOpt(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-abs/opts/${id}`);
  }

  // 4. HALL (Rooms) ENDPOINTS
  saveEventHall(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-halls`, data);
  }

  deleteEventHall(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-halls/${id}`);
  }

  saveHallOpt(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-halls/opts`, data);
  }

  deleteHallOpt(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-halls/opts/${id}`);
  }

  // 5. ADDITIONAL SERVICES ENDPOINTS
  saveEventAdd(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-adds`, data);
  }

  deleteEventAdd(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-adds/${id}`);
  }

  saveAddOpt(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-adds/opts`, data);
  }

  deleteAddOpt(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-adds/opts/${id}`);
  }

  // 6. TRANSPORT ENDPOINTS
  saveEventTransport(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-transports`, data);
  }

  deleteEventTransport(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-transports/${id}`);
  }

  saveTransportOpt(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/event-transports/opts`, data);
  }

  deleteTransportOpt(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/api/event-transports/opts/${id}`);
  }

  // 7. EVENT HISTORY ENDPOINTS
  getHistory(eventId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/api/events/history/${eventId}`);
  }

  restoreHistory(tableName: string, recordId: number, historyId: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/api/events/history/restore`, { table_name: tableName, record_id: recordId, history_id: historyId });
  }

  // --- QUICK PROVIDER ACTIONS ---
  getStatusHistory(table: string, tableId: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/status-history/${table}/${tableId}`);
  }

  saveEventStatus(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/event-status-save`, data);
  }

  sendEventStatusEmail(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/send-mail`, data);
  }

  sendBudgetLinkEmail(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/create-link-email`, data);
  }

  sendProposalEmail(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/proposal-hotel-email`, data);
  }

  sendProposalWithoutValuesEmail(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/proposal-hotel-email-without-values`, data);
  }

  sendInvoiceEmail(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/invoice-email`, data);
  }

  proveBudget(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/budget-prove`, data);
  }

  getApiUrl(): string {
    return this.apiUrl;
  }
}
