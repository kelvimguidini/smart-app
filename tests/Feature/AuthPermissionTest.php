<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $consultant;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar Permissões
        $pAdmin = Permission::create(['name' => 'user_admin']);
        $pHistory = Permission::create(['name' => 'history_view']);
        
        // Criar Roles
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleConsultant = Role::create(['name' => 'Consultant']);

        // Vincular Permissões
        $roleAdmin->permissions()->attach([$pAdmin->id, $pHistory->id]);

        // Criar Usuários
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($roleAdmin->id);

        $this->consultant = User::factory()->create();
        $this->consultant->roles()->attach($roleConsultant->id);
    }

    /**
     * CT-006 – Histórico visível apenas para administração
     */
    public function test_only_admin_can_view_history()
    {
        // Admin deve acessar
        $this->actingAs($this->admin)
            ->get(route('history-list')) // Assumindo que a rota existe
            ->assertStatus(200);

        // Consultor deve ser bloqueado
        $this->actingAs($this->consultant)
            ->get(route('history-list'))
            ->assertStatus(403);
    }

    /**
     * CT-007 – Consultor sem permissão após aprovação
     * CT-008 – Administrador pode editar capa da proposta aprovada
     */
    public function test_editing_rules_after_approval()
    {
        $event = Event::create([
            'name' => 'Evento Teste',
            'status' => 'approved'
        ]);

        // Consultor tenta editar (deve falhar ou redirecionar dependendo da lógica do controller)
        // Aqui assumimos que o controller verifica o status antes de salvar
        $response = $this->actingAs($this->consultant)
            ->post(route('event-store'), [
                'id' => $event->id,
                'name' => 'Nome Alterado'
            ]);
        
        // Verificar se o nome NÃO mudou
        $this->assertEquals('Evento Teste', $event->fresh()->name);

        // Admin tenta editar
        $this->actingAs($this->admin)
            ->post(route('event-store'), [
                'id' => $event->id,
                'name' => 'Nome Alterado pelo Admin'
            ]);

        $this->assertEquals('Nome Alterado pelo Admin', $event->fresh()->name);
    }
}
