<?php

namespace Tests\Unit\Domains\Events\Services;

use Tests\TestCase;
use App\Domains\Events\Services\DefaultEventApiService;
use App\Domains\Events\Repositories\EventApiRepositoryInterface;
use Mockery;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\Event;

class DefaultEventApiServiceTest extends TestCase
{
    protected $eventApiRepositoryMock;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eventApiRepositoryMock = Mockery::mock(EventApiRepositoryInterface::class);
        $this->service = new DefaultEventApiService($this->eventApiRepositoryMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateXmlPayloadEmpty()
    {
        $startDate = '2025-01-01';
        $endDate = '2025-01-31';

        $start_carbon = Carbon::parse($startDate, 'America/Sao_Paulo')->startOfDay()->timezone('UTC');
        $end_carbon = Carbon::parse($endDate, 'America/Sao_Paulo')->endOfDay()->timezone('UTC');

        $this->eventApiRepositoryMock
            ->shouldReceive('getEventsForExport')
            ->with(Mockery::on(function ($arg) use ($start_carbon) {
                return $arg == $start_carbon;
            }), Mockery::on(function ($arg) use ($end_carbon) {
                return $arg == $end_carbon;
            }))
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([]));

        $xml = $this->service->generateXmlPayload($startDate, $endDate);
        
        $this->assertStringContainsString('<intagi>', $xml);
        $this->assertStringContainsString('<versaolayout>V4.4</versaolayout>', $xml);
        $this->assertStringContainsString('<clientes/>', $xml);
        $this->assertStringContainsString('<fornecedores/>', $xml);
        $this->assertStringContainsString('<vendas/>', $xml);
    }

    public function testGenerateXmlPayloadHotelsDiscriminatesRoomsAndDays()
    {
        $startDate = '2026-10-01';
        $endDate = '2026-10-31';

        $event = new Event();
        $event->name = 'Evento Convenção 2026';
        $event->exchange_rate = 1.0;

        $currency = new \App\Models\Currency();
        $currency->sigla = 'BRL';

        $hotelProvider = new \App\Models\Provider();
        $hotelProvider->id = 101;
        $hotelProvider->name = 'Hotel Copacabana Palace';

        $aptoDbl = new \App\Models\Apto();
        $aptoDbl->name = 'DBL';

        $aptoSgl = new \App\Models\Apto();
        $aptoSgl->name = 'SGL';

        $category = new \App\Models\Category();
        $category->name = 'Superior';

        $regime = new \App\Models\Regime();
        $regime->name = 'Café da Manhã';

        // 2 DBL por 5 dias (01/10/2026 a 06/10/2026)
        $optDbl = new \App\Models\EventHotelOpt();
        $optDbl->in = '2026-10-01';
        $optDbl->out = '2026-10-06';
        $optDbl->count = 2;
        $optDbl->received_proposal = 300.00;
        $optDbl->received_proposal_percent = 0;
        $optDbl->kickback = 10;
        $optDbl->setRelation('apto_hotel', $aptoDbl);
        $optDbl->setRelation('category_hotel', $category);
        $optDbl->setRelation('regime', $regime);

        // 3 SGL por 7 dias (01/10/2026 a 08/10/2026)
        $optSgl = new \App\Models\EventHotelOpt();
        $optSgl->in = '2026-10-01';
        $optSgl->out = '2026-10-08';
        $optSgl->count = 3;
        $optSgl->received_proposal = 200.00;
        $optSgl->received_proposal_percent = 0;
        $optSgl->kickback = 10;
        $optSgl->setRelation('apto_hotel', $aptoSgl);
        $optSgl->setRelation('category_hotel', $category);
        $optSgl->setRelation('regime', $regime);

        $eventHotel = new \App\Models\EventHotel();
        $eventHotel->id = 1;
        $eventHotel->iss_percent = 5;
        $eventHotel->service_percent = 10;
        $eventHotel->iva_percent = 0;
        $eventHotel->service_charge = 0;
        $eventHotel->taxa_4bts = 0;
        $eventHotel->internal_observation = 'Obs interna';
        $eventHotel->customer_observation = 'Obs cliente';
        $eventHotel->setRelation('hotel', $hotelProvider);
        $eventHotel->setRelation('currency', $currency);
        $eventHotel->setRelation('status_his', new \Illuminate\Database\Eloquent\Collection([]));
        $eventHotel->setRelation('eventHotelsOpt', new \Illuminate\Database\Eloquent\Collection([$optDbl, $optSgl]));

        $event->setRelation('event_hotels', new \Illuminate\Database\Eloquent\Collection([$eventHotel]));
        $event->setRelation('event_abs', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_halls', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_transports', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_adds', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_airfares', new \Illuminate\Database\Eloquent\Collection([]));

        $this->eventApiRepositoryMock
            ->shouldReceive('getEventsForExport')
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$event]));

        $xmlString = $this->service->generateXmlPayload($startDate, $endDate);
        $xml = simplexml_load_string($xmlString);

        $hotelariaNodes = $xml->vendas->venda->movimentos->hotelaria;
        $this->assertCount(2, $hotelariaNodes, 'Deveria gerar 2 nós de hotelaria, um para cada período/opção');

        // Primeiro movimento: 2 DBL por 5 dias
        $movDbl = $hotelariaNodes[0];
        $this->assertEquals('01/10/2026', (string) $movDbl->checkin);
        $this->assertEquals('06/10/2026', (string) $movDbl->checkout);
        $this->assertCount(2, $movDbl->aptos->apto, 'Deveria discriminar exatamente 2 quartos DBL');
        foreach ($movDbl->aptos->apto as $apto) {
            $this->assertEquals('DBL', (string) $apto->tipoapto);
            $this->assertEquals('5', (string) $apto->qtddiaria, 'Cada quarto deve ter 5 diárias discriminadas');
        }

        // Segundo movimento: 3 SGL por 7 dias
        $movSgl = $hotelariaNodes[1];
        $this->assertEquals('01/10/2026', (string) $movSgl->checkin);
        $this->assertEquals('08/10/2026', (string) $movSgl->checkout);
        $this->assertCount(3, $movSgl->aptos->apto, 'Deveria discriminar exatamente 3 quartos SGL');
        foreach ($movSgl->aptos->apto as $apto) {
            $this->assertEquals('SGL', (string) $apto->tipoapto);
            $this->assertEquals('7', (string) $apto->qtddiaria, 'Cada quarto deve ter 7 diárias discriminadas');
        }
    }

    public function testGenerateXmlPayloadHotelsChunksAboveFiveRooms()
    {
        $startDate = '2026-10-01';
        $endDate = '2026-10-31';

        $event = new Event();
        $event->name = 'Evento Grande 7 Quartos';

        $currency = new \App\Models\Currency();
        $currency->sigla = 'BRL';

        $hotelProvider = new \App\Models\Provider();
        $hotelProvider->id = 102;
        $hotelProvider->name = 'Hotel Grande';

        $aptoDbl = new \App\Models\Apto();
        $aptoDbl->name = 'DBL';

        // 7 DBL por 4 dias (01/10 a 05/10)
        $optDbl = new \App\Models\EventHotelOpt();
        $optDbl->in = '2026-10-01';
        $optDbl->out = '2026-10-05';
        $optDbl->count = 7;
        $optDbl->received_proposal = 250.00;
        $optDbl->received_proposal_percent = 0;
        $optDbl->kickback = 10;
        $optDbl->setRelation('apto_hotel', $aptoDbl);

        $eventHotel = new \App\Models\EventHotel();
        $eventHotel->id = 2;
        $eventHotel->iss_percent = 0;
        $eventHotel->service_percent = 0;
        $eventHotel->iva_percent = 0;
        $eventHotel->service_charge = 0;
        $eventHotel->taxa_4bts = 0;
        $eventHotel->setRelation('hotel', $hotelProvider);
        $eventHotel->setRelation('currency', $currency);
        $eventHotel->setRelation('status_his', new \Illuminate\Database\Eloquent\Collection([]));
        $eventHotel->setRelation('eventHotelsOpt', new \Illuminate\Database\Eloquent\Collection([$optDbl]));

        $event->setRelation('event_hotels', new \Illuminate\Database\Eloquent\Collection([$eventHotel]));
        $event->setRelation('event_abs', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_halls', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_transports', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_adds', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_airfares', new \Illuminate\Database\Eloquent\Collection([]));

        $this->eventApiRepositoryMock
            ->shouldReceive('getEventsForExport')
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$event]));

        $xmlString = $this->service->generateXmlPayload($startDate, $endDate);
        $xml = simplexml_load_string($xmlString);

        $hotelariaNodes = $xml->vendas->venda->movimentos->hotelaria;
        // Deve particionar em 2 nós de hotelaria: primeiro com 5 aptos, segundo com 2 aptos (máximo 5 por XSD)
        $this->assertCount(2, $hotelariaNodes, '7 quartos devem ser particionados em 2 movimentos para respeitar o limite de 5 aptos por hotelaria');
        $this->assertCount(5, $hotelariaNodes[0]->aptos->apto);
        $this->assertCount(2, $hotelariaNodes[1]->aptos->apto);

        foreach ($hotelariaNodes[0]->aptos->apto as $apto) {
            $this->assertEquals('4', (string) $apto->qtddiaria);
        }
        foreach ($hotelariaNodes[1]->aptos->apto as $apto) {
            $this->assertEquals('4', (string) $apto->qtddiaria);
        }
    }

    public function testGenerateXmlPayloadUsesCodesturForCustomerAndProvider()
    {
        $startDate = '2026-10-01';
        $endDate = '2026-10-31';

        $customer = new \App\Models\Customer();
        $customer->id = 55;
        $customer->codestur = 'STUR-CLI-999';

        $event = new Event();
        $event->id = 1;
        $event->code = 'EVT-001';
        $event->date = '2026-10-01';
        $event->setRelation('customer', $customer);

        $currency = new \App\Models\Currency();
        $currency->sigla = 'BRL';

        $hotelProvider = new \App\Models\Provider();
        $hotelProvider->id = 77;
        $hotelProvider->codestur = 'STUR-HOT-888';

        $opt = new \App\Models\EventHotelOpt();
        $opt->in = '2026-10-01';
        $opt->out = '2026-10-02';
        $opt->count = 1;
        $opt->received_proposal = 100.00;
        $opt->received_proposal_percent = 0;
        $opt->kickback = 0;

        $eventHotel = new \App\Models\EventHotel();
        $eventHotel->id = 1;
        $eventHotel->setRelation('hotel', $hotelProvider);
        $eventHotel->setRelation('currency', $currency);
        $eventHotel->setRelation('status_his', new \Illuminate\Database\Eloquent\Collection([]));
        $eventHotel->setRelation('eventHotelsOpt', new \Illuminate\Database\Eloquent\Collection([$opt]));

        $event->setRelation('event_hotels', new \Illuminate\Database\Eloquent\Collection([$eventHotel]));
        $event->setRelation('event_abs', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_halls', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_transports', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_adds', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_airfares', new \Illuminate\Database\Eloquent\Collection([]));

        $this->eventApiRepositoryMock
            ->shouldReceive('getEventsForExport')
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$event]));

        $xmlString = $this->service->generateXmlPayload($startDate, $endDate);
        $xml = simplexml_load_string($xmlString);

        $this->assertEquals('STUR-CLI-999', (string) $xml->vendas->venda->idcliente);
        $this->assertEquals('STUR-HOT-888', (string) $xml->vendas->venda->idfornecedor);
    }

    public function testGenerateXmlPayloadAirfareNationalRouteSetsEticketNacional()
    {
        $startDate = '2026-10-01';
        $endDate = '2026-10-31';

        $event = new Event();
        $event->id = 502;
        $event->name = 'TESTE AÉREO THAMIRES';
        $event->code = '12345';
        $event->date = '2026-10-13';
        $event->exchange_rate = 1.0;

        $currency = new \App\Models\Currency();
        $currency->sigla = 'BRL';

        $airline = new \App\Models\AirfareAirline();
        $airline->id = 1;
        $airline->name = 'GOL';

        $optIda = new \App\Models\EventAirfareOpt();
        $optIda->id = 1;
        $optIda->outbound_origin = 'GRU';
        $optIda->outbound_destination = 'SDU';
        $optIda->outbound_flight_number = 'G3-1234';
        $optIda->outbound_date = '2026-10-13';
        $optIda->outbound_departure_time = '08:00';
        $optIda->outbound_arrival_time = '09:00';
        $optIda->setRelation('outbound_airline', $airline);

        $eventAirfare = new \App\Models\EventAirfare();
        $eventAirfare->id = 10;
        $eventAirfare->total_net_sem_4bts = 1500.00;
        $eventAirfare->markup = 10;
        $eventAirfare->taxa_embarque_unit = 50.00;
        $eventAirfare->setRelation('airline', $airline);
        $eventAirfare->setRelation('currency', $currency);
        $eventAirfare->setRelation('status_his', new \Illuminate\Database\Eloquent\Collection([]));
        $eventAirfare->setRelation('eventAirfareOpts', new \Illuminate\Database\Eloquent\Collection([$optIda]));

        $event->setRelation('event_hotels', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_abs', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_halls', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_transports', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_adds', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_airfares', new \Illuminate\Database\Eloquent\Collection([$eventAirfare]));

        $this->eventApiRepositoryMock
            ->shouldReceive('getEventsForExport')
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$event]));

        $xmlString = $this->service->generateXmlPayload($startDate, $endDate);
        $xml = simplexml_load_string($xmlString);

        $venda = $xml->vendas->venda;
        $this->assertEquals('AEREO', (string) $venda->tipoproduto);
        $this->assertEquals('180', (string) $venda->idproduto);
        $this->assertEquals('NACIONAL', (string) $venda->clasproduto);
        $this->assertEquals('01/0018', (string) $venda->tipoctarec);
        $this->assertEquals('01/0001', (string) $venda->tipoctapag);
        $this->assertEquals('4BTS', (string) $venda->idpromotor);

        // Movimento deve ser <aereo> e não <passagem>
        $this->assertNotEmpty($venda->movimentos->aereo);
        $this->assertEmpty($venda->movimentos->passagem);

        $aereo = $venda->movimentos->aereo;
        $this->assertEquals('TESTE AÉREO THAMIRES', (string) $aereo->pax);
        $this->assertEquals('BRL', (string) $aereo->moeda);
        $this->assertEquals('50.00', (string) $aereo->taxaembarque);
        $this->assertCount(1, $aereo->trechos->segmento);
        $this->assertEquals('GRU', (string) $aereo->trechos->segmento[0]->de);
        $this->assertEquals('SDU', (string) $aereo->trechos->segmento[0]->para);
        $this->assertEquals('G3', (string) $aereo->trechos->segmento[0]->ciaaerea);
    }

    public function testGenerateXmlPayloadAirfareInternationalRouteSetsEticketInternacional()
    {
        $startDate = '2026-10-01';
        $endDate = '2026-10-31';

        $event = new Event();
        $event->id = 503;
        $event->name = 'TESTE AÉREO INTERNACIONAL';
        $event->code = '54321';
        $event->date = '2026-10-15';
        $event->exchange_rate = 5.5;

        $currency = new \App\Models\Currency();
        $currency->sigla = 'USD';

        $airline = new \App\Models\AirfareAirline();
        $airline->id = 2;
        $airline->name = 'AMERICAN AIRLINES';

        $opt = new \App\Models\EventAirfareOpt();
        $opt->id = 2;
        $opt->outbound_origin = 'GRU';
        $opt->outbound_destination = 'MIA';
        $opt->outbound_flight_number = 'AA-906';
        $opt->outbound_date = '2026-10-15';
        $opt->setRelation('outbound_airline', $airline);

        $eventAirfare = new \App\Models\EventAirfare();
        $eventAirfare->id = 11;
        $eventAirfare->total_net_sem_4bts = 800.00;
        $eventAirfare->markup = 0;
        $eventAirfare->taxa_embarque_unit = 80.00;
        $eventAirfare->setRelation('airline', $airline);
        $eventAirfare->setRelation('currency', $currency);
        $eventAirfare->setRelation('status_his', new \Illuminate\Database\Eloquent\Collection([]));
        $eventAirfare->setRelation('eventAirfareOpts', new \Illuminate\Database\Eloquent\Collection([$opt]));

        $event->setRelation('event_hotels', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_abs', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_halls', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_transports', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_adds', new \Illuminate\Database\Eloquent\Collection([]));
        $event->setRelation('event_airfares', new \Illuminate\Database\Eloquent\Collection([$eventAirfare]));

        $this->eventApiRepositoryMock
            ->shouldReceive('getEventsForExport')
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([$event]));

        $xmlString = $this->service->generateXmlPayload($startDate, $endDate);
        $xml = simplexml_load_string($xmlString);

        $venda = $xml->vendas->venda;
        $this->assertEquals('AEREO', (string) $venda->tipoproduto);
        $this->assertEquals('190', (string) $venda->idproduto);
        $this->assertEquals('INTERNACIONAL', (string) $venda->clasproduto);
        $this->assertEquals('01/0018', (string) $venda->tipoctarec);
        $this->assertEquals('01/0001', (string) $venda->tipoctapag);
        $this->assertEquals('4BTS', (string) $venda->idpromotor);

        $aereo = $venda->movimentos->aereo;
        $this->assertEquals('GRU', (string) $aereo->trechos->segmento[0]->de);
        $this->assertEquals('MIA', (string) $aereo->trechos->segmento[0]->para);
        $this->assertEquals('AA', (string) $aereo->trechos->segmento[0]->ciaaerea);
    }
}
