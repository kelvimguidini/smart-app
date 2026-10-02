<?php

namespace Tests\Unit\Domains\Events;

use Tests\TestCase;
use App\Models\Event;
use App\Models\EventHotel;
use App\Models\EventAB;
use App\Models\EventHall;
use App\Models\Provider;
use App\Models\Currency;
use App\Models\User;
use App\Models\StatusHistory;
use App\Domains\Events\Repositories\EloquentEventRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProposalStatusFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dating_with_customer_proposal_only_includes_services_with_same_status(): void
    {
        $repository = app(EloquentEventRepository::class);

        $currency = Currency::first() ?? Currency::create([
            'name' => 'Real',
            'symbol' => 'R$',
            'sigla' => 'BRL',
            'active' => 1
        ]);

        $user = User::first() ?? User::create([
            'name' => 'Admin Teste',
            'email' => 'admin_teste_' . uniqid() . '@teste.com',
            'password' => bcrypt('password123')
        ]);

        // 1. Cria um Evento
        $event = Event::create([
            'name' => 'Evento Teste Filtro Proposta',
            'status' => 'created'
        ]);

        // 2. Cria um Fornecedor
        $hotel = Provider::create([
            'name' => 'Hotel Teste Status',
            'active' => 1
        ]);

        // 3. Cria Hospedagem vinculada com status 'dating_with_customer'
        $eventHotel = EventHotel::create([
            'event_id' => $event->id,
            'hotel_id' => $hotel->id,
            'currency_id' => $currency->id,
        ]);
        StatusHistory::create([
            'event_id' => $event->id,
            'table' => 'event_hotels',
            'table_id' => $eventHotel->id,
            'status' => 'dating_with_customer',
            'user_id' => $user->id,
        ]);

        // 4. Cria A&B vinculado ao mesmo fornecedor mas com status 'sent_to_customer'
        $eventAB = EventAB::create([
            'event_id' => $event->id,
            'ab_id' => $hotel->id,
            'currency_id' => $currency->id,
        ]);
        StatusHistory::create([
            'event_id' => $event->id,
            'table' => 'event_abs',
            'table_id' => $eventAB->id,
            'status' => 'sent_to_customer',
            'user_id' => $user->id,
        ]);

        // 5. Cria Salão vinculado ao mesmo fornecedor com status 'dating_with_customer'
        $eventHall = EventHall::create([
            'event_id' => $event->id,
            'hall_id' => $hotel->id,
            'currency_id' => $currency->id,
        ]);
        StatusHistory::create([
            'event_id' => $event->id,
            'table' => 'event_halls',
            'table_id' => $eventHall->id,
            'status' => 'dating_with_customer',
            'user_id' => $user->id,
        ]);

        // Executa getProposalData simulando o download a partir de event_hotels com status 'dating_with_customer'
        $data = $repository->getProposalData($event->id, $hotel->id, 'event_hotels', 'dating_with_customer');

        $this->assertEquals('dating_with_customer', $data['targetStatus']);
        
        $returnedEvent = $data['eventDataBase'];
        // Hotel deve estar presente (está como dating_with_customer)
        $this->assertCount(1, $returnedEvent->event_hotels);
        $this->assertEquals($eventHotel->id, $returnedEvent->event_hotels->first()->id);

        // A&B NÃO deve estar presente (está como sent_to_customer)
        $this->assertCount(0, $returnedEvent->event_abs);

        // Salão DEVE estar presente (está como dating_with_customer)
        $this->assertCount(1, $returnedEvent->event_halls);
        $this->assertEquals($eventHall->id, $returnedEvent->event_halls->first()->id);
    }

    public function test_other_status_proposal_keeps_all_active_services(): void
    {
        $repository = app(EloquentEventRepository::class);

        $currency = Currency::first() ?? Currency::create([
            'name' => 'Real',
            'symbol' => 'R$',
            'sigla' => 'BRL',
            'active' => 1
        ]);

        $user = User::first() ?? User::create([
            'name' => 'Admin Teste',
            'email' => 'admin_teste_' . uniqid() . '@teste.com',
            'password' => bcrypt('password123')
        ]);

        $event = Event::create([
            'name' => 'Evento Teste Outro Status',
            'status' => 'created'
        ]);

        $hotel = Provider::create([
            'name' => 'Hotel Teste Normal',
            'active' => 1
        ]);

        $eventHotel = EventHotel::create([
            'event_id' => $event->id,
            'hotel_id' => $hotel->id,
            'currency_id' => $currency->id,
        ]);
        StatusHistory::create([
            'event_id' => $event->id,
            'table' => 'event_hotels',
            'table_id' => $eventHotel->id,
            'status' => 'sent_to_customer',
            'user_id' => $user->id,
        ]);

        $eventAB = EventAB::create([
            'event_id' => $event->id,
            'ab_id' => $hotel->id,
            'currency_id' => $currency->id,
        ]);
        StatusHistory::create([
            'event_id' => $event->id,
            'table' => 'event_abs',
            'table_id' => $eventAB->id,
            'status' => 'sent_to_customer',
            'user_id' => $user->id,
        ]);

        // Executa getProposalData com status 'sent_to_customer'
        $data = $repository->getProposalData($event->id, $hotel->id, 'event_hotels', 'sent_to_customer');

        $this->assertEquals('sent_to_customer', $data['targetStatus']);
        
        $returnedEvent = $data['eventDataBase'];
        // Ambos devem estar presentes pois não é dating_with_customer
        $this->assertCount(1, $returnedEvent->event_hotels);
        $this->assertCount(1, $returnedEvent->event_abs);
    }
}
