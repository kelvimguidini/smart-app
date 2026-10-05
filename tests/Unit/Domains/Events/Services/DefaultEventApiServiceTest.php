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
}
