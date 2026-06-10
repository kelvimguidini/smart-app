<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthPermissionTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $consultant;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar Permissões Reais do Sistema
        $pAdmin = Permission::create(['name' => 'event_admin']);
        $pStatus = Permission::create(['name' => 'status_level_1']);
        
        // Criar Roles
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleConsultant = Role::create(['name' => 'Consultant']);

        // Vincular Permissões
        $roleAdmin->permissions()->attach([$pAdmin->id, $pStatus->id]);

        // Criar Usuários
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($roleAdmin->id);

        $this->consultant = User::factory()->create();
        $this->consultant->roles()->attach($roleConsultant->id);

        // Registrar Gates Manualmente para o ambiente de teste (Simulando AuthServiceProvider)
        foreach (Permission::all() as $permission) {
            Gate::define($permission->name, function ($user) use ($permission) {
                return $user->roles()->whereHas('permissions', function ($q) use ($permission) {
                    $q->where('name', $permission->name);
                })->exists();
            });
        }
    }

    /**
     * CT-006 – Histórico de status visível apenas para quem tem permissão de status
     */
    public function test_only_authorized_can_view_status_history()
    {
        // Admin (com status_level_1) deve acessar
        $this->actingAs($this->admin)
            ->get(route('status-history', ['table' => 'event_hotels', 'table_id' => 1]))
            ->assertStatus(200);

        // Consultor (sem status_level_1) deve ser bloqueado
        $this->actingAs($this->consultant)
            ->get(route('status-history', ['table' => 'event_hotels', 'table_id' => 1]))
            ->assertStatus(403);
    }

    /**
     * CT-007 – Permissões de edição de evento
     */
    public function test_editing_rules_for_event()
    {
        $event = Event::create([
            'name' => 'Evento Teste',
            'status' => 'created'
        ]);

        // Consultor tenta editar (sem event_admin)
        $response = $this->actingAs($this->consultant)
            ->post(route('event-save'), [
                'id' => $event->id,
                'name' => 'Nome Alterado'
            ]);
        
        $response->assertStatus(403);

        // Admin tenta editar (com event_admin)
        $this->actingAs($this->admin)
            ->post(route('event-save'), [
                'id' => $event->id,
                'name' => 'Nome Alterado pelo Admin'
            ]);

        $this->assertEquals('Nome Alterado pelo Admin', $event->fresh()->name);
    }
}
