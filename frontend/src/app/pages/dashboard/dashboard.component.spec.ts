import { describe, it, expect, beforeEach, vi } from 'vitest';
import { DashboardComponent } from './dashboard.component';
import { of } from 'rxjs';

describe('DashboardComponent', () => {
  let component: DashboardComponent;
  let mockHttp: any;

  beforeEach(() => {
    // Mock do HttpClient
    mockHttp = {
      get: vi.fn().mockReturnValue(of({}))
    };

    // Instancia o componente manualmente (já que é standalone e usa inject)
    // Nota: Em testes reais de integração usaríamos TestBed, 
    // mas para teste de unidade de lógica podemos injetar o mock.
    component = new DashboardComponent();
    (component as any).http = mockHttp;
  });

  it('deve criar o componente', () => {
    expect(component).toBeTruthy();
  });

  it('deve processar grupos de usuários e atribuir cores', () => {
    const mockGroups = [
      { Name: 'Admin', CountUsers: 5, Percentage: 50 },
      { Name: 'Editor', CountUsers: 5, Percentage: 50 }
    ];

    component.renderUserGroups(mockGroups);

    expect(component.groups.loading).toBe(false);
    expect(component.groups.data.length).toBe(2);
    expect(component.groups.data[0].color).toBeDefined();
    expect(component.groups.data[0].Name).toBe('Admin');
  });

  it('deve formatar corretamente os dados de status do hotel', () => {
    const mockStatus = {
      'Confirmado': 10,
      'Pendente': 5
    };

    // Espionamos o método de gerar gráfico para não dar erro de Canvas no ambiente de teste puro
    vi.spyOn(component as any, 'pieHotelStatus').mockImplementation(() => {});

    component.renderEventStatus(mockStatus);

    expect(component.eventStatus.loading).toBe(false);
    // Verificamos se a lógica de tratamento de dados (pieHotelStatus foi chamada com os dados certos)
    expect((component as any).pieHotelStatus).toHaveBeenCalledWith(mockStatus);
  });

  it('deve gerar uma cor hexadecimal válida', () => {
    const color = component.generateColor();
    expect(color).toMatch(/^#[0-9A-F]{6}$/i);
  });
});
