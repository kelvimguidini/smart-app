import { Component, Input, Output, EventEmitter, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EventService } from '../../../../services/event.service';
import { AuthService } from '../../../../services/auth.service';
import { ToastService } from '../../../../services/toast.service';
import { ModalComponent } from '../../../../shared/components/modal/modal.component';

@Component({
  selector: 'app-provider-actions',
  standalone: true,
  imports: [CommonModule, FormsModule, ModalComponent],
  templateUrl: './provider-actions.component.html',
  styleUrls: []
})
export class ProviderActionsComponent {
  private readonly eventService = inject(EventService);
  private readonly authService = inject(AuthService);
  private readonly toastService = inject(ToastService);

  @Input() event: any;
  @Input() prov: any;
  @Input() allStatus: any = {};

  @Output() refresh = new EventEmitter<void>();

  isLoader = false;

  // Modals Visibility
  showFollowUpModal = false;
  showBudgetLinkModal = false;
  showEvaluationModal = false;
  showProposalModal = false;
  showProposalWithoutValuesModal = false;
  showInvoiceModal = false;

  // Follow UP Form State
  formStatus = {
    table: '',
    table_id: 0,
    event_id: 0,
    status_hotel: '',
    observation_hotel: '',
    notify: false,
    emailsLink: '',
    messageLink: '',
    copyMeLink: false
  };
  statusOptions: any[] = [];
  history: any[] = [];

  // Budget Link Form State
  emailsLink = '';
  sendEmailLink = true;
  messageLink = '';
  copyMeLink = false;
  attachmentLink = false;
  linkEmail = true;
  generatedToken = '';

  // Proposal Form State
  emails = '';
  sendEmail = true;
  message = '';
  copyMe = false;

  // Proposal Without Values Form State
  emailsWithoutValues = '';
  sendEmailWithoutValues = true;
  messageWithoutValues = '';
  copyMeWithoutValues = false;

  // Invoice Form State
  emailsInvoice = '';
  sendEmailInvoice = true;
  messageInvoice = '';
  copyMeInvoice = false;

  hasPermission(role: string): boolean {
    return this.authService.user()?.permissions?.some((p: any) => p.name === role) || false;
  }

  getStatusLabel(status: string): string {
    return this.allStatus[status]?.label || 'Solicitado';
  }

  generateUUID(): string {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  // 1. FOLLOW UP ACTIONS
  editStatus() {
    this.isLoader = true;
    this.eventService.getStatusHistory(this.prov.table, this.prov.table_id).subscribe({
      next: (res) => {
        this.history = res || [];
        const currentStatus = this.history[0]?.status || 'created';
        const currentStatusInfo = this.allStatus[currentStatus] || {};
        const allowedFlows = currentStatusInfo.flow || [];

        const userPermissions = this.authService.user()?.permissions || [];
        const hasLevel1 = userPermissions.some((p: any) => p.name === 'status_level_1' || p.name === 'status_level_2');
        const hasLevel2 = userPermissions.some((p: any) => p.name === 'status_level_2');

        this.statusOptions = Object.entries(this.allStatus)
          .filter(([key, status]: [string, any]) => {
            const isAllowedFlow = allowedFlows.includes(key);
            const matchesLevel = (status.level === 1 && hasLevel1) || (status.level === 2 && hasLevel2);
            return isAllowedFlow && matchesLevel;
          })
          .map(([key, status]: [string, any]) => ({ key, label: status.label }));

        this.formStatus.table = this.prov.table;
        this.formStatus.table_id = this.prov.table_id;
        this.formStatus.event_id = this.event.id;
        this.formStatus.status_hotel = '';
        this.formStatus.observation_hotel = '';
        this.formStatus.notify = false;
        this.formStatus.emailsLink = this.prov.email || '';
        this.formStatus.messageLink = '';
        this.formStatus.copyMeLink = false;

        this.showFollowUpModal = true;
        this.isLoader = false;
      },
      error: (err) => {
        console.error('Erro ao obter o histórico de status:', err);
        this.isLoader = false;
      }
    });
  }

  saveStatus() {
    if (!this.formStatus.status_hotel) {
      alert("Por favor, selecione um status de hotel válido.");
      return;
    }
    this.isLoader = true;

    const msg = (this.formStatus.messageLink || '') + `<br><br>
      <p><b>Evento:</b> <span style="color: #333;">${this.event.name}</span></p>
      <p><b>Código:</b> <span style="color: #333;">${this.event.code}</span></p>
      <p><b>CRD:</b> <span style="color: #333;">${this.event.crd ? this.event.crd.name : ''}</span></p>
      <p><b>CC:</b> <span style="color: #333;">${this.event.cost_center || ''}</span></p>
      <p><b>Solicitante:</b> <span style="color: #333;">${this.event.requester || ''}</span></p>
      <p><b>Base de pax:</b> <span style="color: #333;">${this.event.pax_base || ''}</span></p>
      <p><b>Fornecedor:</b> <span style="color: #333;">${this.prov.name}</span></p>`;

    const payload = {
      ...this.formStatus,
      messageLink: msg
    };

    this.eventService.saveEventStatus(payload).subscribe({
      next: () => {
        this.isLoader = false;
        this.showFollowUpModal = false;
        this.refresh.emit();
      },
      error: (err) => {
        console.error('Erro ao tramitar status:', err);
        this.isLoader = false;
      }
    });
  }

  sendEmailNoChangeStatus() {
    this.isLoader = true;

    const msg = (this.formStatus.messageLink || '') + `<br><br>
      <p><b>Evento:</b> <span style="color: #333;">${this.event.name}</span></p>
      <p><b>Código:</b> <span style="color: #333;">${this.event.code}</span></p>
      <p><b>CRD:</b> <span style="color: #333;">${this.event.crd ? this.event.crd.name : ''}</span></p>
      <p><b>CC:</b> <span style="color: #333;">${this.event.cost_center || ''}</span></p>
      <p><b>Solicitante:</b> <span style="color: #333;">${this.event.requester || ''}</span></p>
      <p><b>Base de pax:</b> <span style="color: #333;">${this.event.pax_base || ''}</span></p>
      <p><b>Fornecedor:</b> <span style="color: #333;">${this.prov.name}</span></p>`;

    const payload = {
      ...this.formStatus,
      messageLink: msg
    };

    this.eventService.sendEventStatusEmail(payload).subscribe({
      next: () => {
        this.isLoader = false;
        this.showFollowUpModal = false;
        this.refresh.emit();
      },
      error: (err) => {
        console.error('Erro ao enviar e-mail sem tramitar:', err);
        this.isLoader = false;
      }
    });
  }

  // 2. BUDGET LINK ACTIONS
  openBudgetLinkModal() {
    this.emailsLink = this.prov.email || '';
    this.sendEmailLink = true;
    this.messageLink = '';
    this.copyMeLink = false;
    this.attachmentLink = false;
    this.linkEmail = true;
    this.generatedToken = this.generateUUID();
    this.showBudgetLinkModal = true;
  }

  getBudgetUrl(token: string): string {
    return `${window.location.origin}/budget?token=${token}`;
  }

  baixarPdfAssincrono(downloadUrl: string, filename: string, shouldRefresh: boolean = false) {
    this.toastService.info('Gerando o PDF do documento, por favor aguarde...');
    this.isLoader = true;

    this.eventService.downloadPdf(downloadUrl).subscribe({
      next: (blob) => {
        const a = document.createElement('a');
        const objectUrl = URL.createObjectURL(blob);
        a.href = objectUrl;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(objectUrl);
        
        this.isLoader = false;
        this.toastService.success('Download concluído com sucesso!');
        if (shouldRefresh) {
          this.refresh.emit();
        }
      },
      error: (err) => {
        console.error('Erro ao baixar PDF:', err);
        this.isLoader = false;
        this.toastService.error('Erro ao gerar o PDF. Tente novamente.');
      }
    });
  }

  createLink() {
    const token = this.prov.token_budget || this.generatedToken;

    if (!this.sendEmailLink) {
      // Direct PDF Download (Async)
      const downloadUrl = `${this.eventService.getApiUrl()}/create-link/true/${this.prov.id}/${this.event.id}/${token}/${this.prov.table}`;
      const filename = `Link_Orcamento_ID${this.event.id}_${this.prov.name || 'Fornecedor'}.pdf`;
      this.showBudgetLinkModal = false;
      this.baixarPdfAssincrono(downloadUrl, filename, true);
    } else {
      // Send Email
      this.isLoader = true;
      const payload = {
        download: false,
        provider_id: this.prov.id,
        event_id: this.event.id,
        message: this.messageLink || '!',
        emails: this.emailsLink,
        copyMe: this.copyMeLink,
        attachment: this.attachmentLink,
        link: token,
        linkEmail: this.linkEmail,
        type: this.prov.table
      };

      this.eventService.sendBudgetLinkEmail(payload).subscribe({
        next: () => {
          this.isLoader = false;
          this.showBudgetLinkModal = false;
          alert(`Link gerado com sucesso! Link: ${this.getBudgetUrl(token)}`);
          this.refresh.emit();
        },
        error: (err) => {
          console.error('Erro ao enviar link por e-mail:', err);
          this.isLoader = false;
        }
      });
    }
  }

  // 3. BUDGET EVALUATION ACTIONS (Aprovar / Recusar)
  getEvaluationUrl(prove: boolean): string {
    const userParam = prove ? `&user=${this.authService.user()?.id || 0}` : '';
    return `${this.eventService.getApiUrl()}/budget?token=${this.prov.token_budget}&prove=true${userParam}`;
  }

  openEvaluationLink() {
    const url = this.getEvaluationUrl(true);
    window.open(url, '_blank');
  }

  openVerLink() {
    const url = this.getEvaluationUrl(false);
    window.open(url, '_blank');
  }

  openEvaluationDetailsModal() {
    this.showEvaluationModal = true;
  }

  // 4. PROPOSAL ACTIONS
  openProposalModal() {
    this.emails = this.event.customer?.email || '';
    this.sendEmail = true;
    this.message = '';
    this.copyMe = false;
    this.showProposalModal = true;
  }

  sendProposal() {
    if (!this.sendEmail) {
      // Direct Download (Async)
      const downloadUrl = `${this.eventService.getApiUrl()}/proposal-hotel/true/${this.prov.id}/${this.event.id}/${this.prov.table}`;
      const filename = `Proposta_ID${this.event.id}_${this.prov.name || 'Fornecedor'}.pdf`;
      this.showProposalModal = false;
      this.baixarPdfAssincrono(downloadUrl, filename, false);
    } else {
      this.isLoader = true;
      const payload = {
        download: false,
        provider_id: this.prov.id,
        event_id: this.event.id,
        message: this.message,
        emails: this.emails,
        copyMe: this.copyMe,
        type: this.prov.table
      };

      this.eventService.sendProposalEmail(payload).subscribe({
        next: () => {
          this.isLoader = false;
          this.showProposalModal = false;
          this.refresh.emit();
        },
        error: (err) => {
          console.error('Erro ao enviar proposta por e-mail:', err);
          this.isLoader = false;
        }
      });
    }
  }

  // 5. PROPOSAL WITHOUT VALUES ACTIONS
  openProposalWithoutValuesModal() {
    this.emailsWithoutValues = this.event.customer?.email || '';
    this.sendEmailWithoutValues = true;
    this.messageWithoutValues = '';
    this.copyMeWithoutValues = false;
    this.showProposalWithoutValuesModal = true;
  }

  sendProposalWithoutValues() {
    if (!this.sendEmailWithoutValues) {
      // Direct Download (Async)
      const downloadUrl = `${this.eventService.getApiUrl()}/proposal-hotel-without-values/true/${this.prov.id}/${this.event.id}/${this.prov.table}`;
      const filename = `Proposta_Sem_Valores_ID${this.event.id}_${this.prov.name || 'Fornecedor'}.pdf`;
      this.showProposalWithoutValuesModal = false;
      this.baixarPdfAssincrono(downloadUrl, filename, false);
    } else {
      this.isLoader = true;
      const payload = {
        download: false,
        provider_id: this.prov.id,
        event_id: this.event.id,
        message: this.messageWithoutValues,
        emails: this.emailsWithoutValues,
        copyMe: this.copyMeWithoutValues,
        type: this.prov.table
      };

      this.eventService.sendProposalWithoutValuesEmail(payload).subscribe({
        next: () => {
          this.isLoader = false;
          this.showProposalWithoutValuesModal = false;
          this.refresh.emit();
        },
        error: (err) => {
          console.error('Erro ao enviar proposta sem valores por e-mail:', err);
          this.isLoader = false;
        }
      });
    }
  }

  // 6. INVOICE/FATURAMENTO ACTIONS
  openInvoiceModal() {
    this.emailsInvoice = '';
    this.sendEmailInvoice = false;
    this.messageInvoice = '';
    this.copyMeInvoice = false;
    this.showInvoiceModal = true;
  }

  sendInvoice() {
    if (!this.sendEmailInvoice) {
      // Direct Download (Async)
      const downloadUrl = `${this.eventService.getApiUrl()}/invoice/true/${this.prov.id}/${this.event.id}/${this.prov.table}`;
      const filename = `Faturamento_ID${this.event.id}_${this.prov.name || 'Fornecedor'}.pdf`;
      this.showInvoiceModal = false;
      this.baixarPdfAssincrono(downloadUrl, filename, false);
    } else {
      this.isLoader = true;
      const payload = {
        download: false,
        provider_id: this.prov.id,
        event_id: this.event.id,
        message: this.messageInvoice,
        emails: this.emailsInvoice,
        copyMe: this.copyMeInvoice,
        type: this.prov.table
      };

      this.eventService.sendInvoiceEmail(payload).subscribe({
        next: () => {
          this.isLoader = false;
          this.showInvoiceModal = false;
          this.refresh.emit();
        },
        error: (err) => {
          console.error('Erro ao enviar faturamento por e-mail:', err);
          this.isLoader = false;
        }
      });
    }
  }
}
