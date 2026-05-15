<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $pEvent = Permission::create(['name' => 'event_admin']);
        $role = Role::create(['name' => 'Admin']);
        $role->permissions()->attach($pEvent->id);
        
        $this->user = User::factory()->create();
        $this->user->roles()->attach($role->id);
    }

    /**
     * CT-025 – Alteração de moeda
     * CT-026 – Inclusão de câmbio no faturamento
     */
    public function test_exchange_rate_persistence()
    {
        $event = Event::create(['name' => 'Evento Câmbio']);

        $exchangeData = [
            'event_id' => $event->id,
            'exchange_rates' => [
                'USD' => 5.20,
                'EUR' => 5.60
            ]
        ];

        $this->actingAs($this->user)
            ->post(route('event-save-exchange-rate'), $exchangeData);

        $event->refresh();
        $this->assertEquals(5.20, $event->exchange_rates['USD']);
        $this->assertEquals(5.60, $event->exchange_rates['EUR']);
    }

    /**
     * CT-021 – Campo N° TKT no faturamento
     * (Ajustar campos conforme a tabela real de faturamento/event_hotels se necessário)
     */
    public function test_billing_value_persistence()
    {
        $event = Event::create(['name' => 'Evento Faturamento']);

        $this->actingAs($this->user)
            ->post(route('event-save-valor-faturamento'), [
                'event_id' => $event->id,
                'vl_faturamento' => 15000.50
            ]);

        $this->assertEquals(15000.50, $event->fresh()->valor_faturamento);
    }
}
