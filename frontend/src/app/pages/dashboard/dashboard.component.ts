import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Chart, registerables } from 'chart.js';
import 'chartjs-adapter-date-fns';
import { environment } from '../../../environments/environment';

Chart.register(...registerables);

import { AuthenticatedLayoutComponent } from '../../shared/layouts/authenticated-layout/authenticated-layout.component';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, AuthenticatedLayoutComponent],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  pendingValidate = { loading: true, data: 0, error: false };
  linksApproved = { loading: true, data: 0, error: false };
  eventStatus: any = { loading: true, data: [], error: false };
  waitApproval = { loading: true, data: { hotels: 0, transports: 0 }, error: false };
  events = { loading: true, data: [] as any[], error: false };
  groups = { loading: true, data: [] as any[], error: false };

  ngOnInit(): void {
    this.fetchDashboardData();
  }

  fetchDashboardData() {
    this.http.get(`${this.apiUrl}/dashboard-data`).subscribe({
      next: (response: any) => {
        const data = response;
        try { this.renderWaitApproval(data.waitApproval); } catch(e) { this.waitApproval.error = true; }
        try { this.renderEventStatus(data.eventStatus); } catch(e) { this.eventStatus.error = true; }
        try { this.renderPendingValidate(data.pendingValidate); } catch(e) { this.pendingValidate.error = true; }
        try { this.renderLinksApproved(data.linksApproved); } catch(e) { this.linksApproved.error = true; }
        try { this.renderEventMonth(data.byMonths); } catch(e) { this.events.error = true; }
        try { this.renderUserGroups(data.userGroups); } catch(e) { this.groups.error = true; }
      },
      error: (err: HttpErrorResponse) => {
        console.error(err);
        this.waitApproval.loading = false; this.waitApproval.error = true;
        this.eventStatus.loading = false; this.eventStatus.error = true;
        this.pendingValidate.loading = false; this.pendingValidate.error = true;
        this.linksApproved.loading = false; this.linksApproved.error = true;
        this.events.loading = false; this.events.error = true;
        this.groups.loading = false; this.groups.error = true;
      }
    });
  }

  renderWaitApproval(response: any) {
    this.waitApproval.data = response.original || response;
    this.waitApproval.loading = false;
  }

  renderPendingValidate(response: any) {
    this.pendingValidate.data = response;
    this.pendingValidate.loading = false;
  }

  renderLinksApproved(response: any) {
    this.linksApproved.data = response ?? 0;
    this.linksApproved.loading = false;
  }

  renderUserGroups(response: any) {
    const data = response.original || response;
    this.groups.data = data.map((group: any) => ({
      ...group,
      color: this.generateColor()
    }));
    this.groups.loading = false;
  }

  renderEventStatus(response: any) {
    const data = response.original || response;
    this.pieHotelStatus(data);
    this.eventStatus.loading = false;
  }

  pieHotelStatus(hotel: any) {
    const chartData = Object.keys(hotel).map((key) => ({
      label: key,
      value: hotel[key],
      backgroundColor: this.generateColor(),
    }));

    this.eventStatus.data.hotel = chartData;

    setTimeout(() => {
      const canvas = document.getElementById('pie-hotel-status') as HTMLCanvasElement;
      if (canvas) {
        new Chart(canvas, {
          type: 'doughnut',
          data: {
            datasets: [{
              data: chartData.map((item) => item.value),
              backgroundColor: chartData.map((item) => item.backgroundColor),
              hoverBackgroundColor: chartData.map((item) => item.backgroundColor),
              hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
          },
          options: {
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false }
            },
            cutout: '80%',
          },
        });
      }
    }, 100);
  }

  renderEventMonth(response: any) {
    const data = response.original || response;
    this.events.data = data;

    setTimeout(() => {
      const canvas = document.getElementById('area-event') as HTMLCanvasElement;
      if (canvas) {
        new Chart(canvas, {
          type: 'line',
          data: {
            labels: data.map((item: any) => item.month),
            datasets: [
              {
                label: "Data Do Evento",
                tension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                pointHitRadius: 10,
                borderWidth: 2,
                data: data.map((item: any) => item.event_count),
              },
              {
                label: "Data do Registro",
                tension: 0.3,
                backgroundColor: "rgba(115, 223, 78, 0.05)",
                borderColor: "rgba(115, 223, 78, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(115, 223, 78, 1)",
                pointBorderColor: "rgba(115, 223, 78, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(115, 223, 78, 1)",
                pointHoverBorderColor: "rgba(115, 223, 78, 1)",
                pointHitRadius: 10,
                borderWidth: 2,
                data: data.map((item: any) => item.register_count),
              }
            ],
          },
          options: {
            maintainAspectRatio: false,
            plugins: {
              legend: { display: true }
            },
            scales: {
              x: {
                grid: { display: false }
              },
              y: {
                grid: {
                  color: "rgb(234, 236, 244)",
                }
              }
            }
          }
        });
      }
    }, 100);
    this.events.loading = false;
  }

  generateColor() {
    const red = Math.floor(Math.random() * 256);
    const green = Math.floor(Math.random() * 256);
    const blue = Math.floor(Math.random() * 256);
    return "#" + this.componentToHex(red) + this.componentToHex(green) + this.componentToHex(blue);
  }

  componentToHex(c: number) {
    const hex = c.toString(16);
    return hex.length == 1 ? "0" + hex : hex;
  }
}
