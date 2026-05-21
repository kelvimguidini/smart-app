<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar permissão e perfil usando Eloquent (dados isolados via RefreshDatabase)
        $permission = Permission::create([
            'name'  => 'user_admin',
            'title' => 'Administrador de Usuários',
        ]);

        $role = Role::create(['name' => 'Admin']);
        $role->permissions()->attach($permission->id);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role->id);

        // Registrar Gate manualmente pois o AuthServiceProvider
        // roda antes das migrações/seeds do banco de teste.
        Gate::define('user_admin', function ($user) use ($permission) {
            return $user->roles()->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission->name);
            })->exists();
        });
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->actingAs($this->admin)->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_but_not_login_without_activation()
    {
        $response = $this->actingAs($this->admin)->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Verifica redirecionamento após sucesso no cadastro
        $response->assertRedirect(route('register'));
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Auth::logout();

        $loginResponse = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(403); 
        $this->assertGuest();
    }

    public function test_user_can_login_after_email_verification_and_password_reset()
    {
        // 1. Admin registra o usuário
        $this->actingAs($this->admin)->post('/register', [
            'name' => 'Active User',
            'email' => 'active@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'active@example.com')->first();
        $this->assertNotNull($user, 'Usuário não foi criado no banco.');

        Auth::logout();

        // 2. Simular a verificação de e-mail
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirectContains('reset-password');
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 3. Forçar ativação para teste de login final
        $user->forceFill(['email_verified_at' => now(), 'active' => 1])->save();

        $loginResponse = $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }
}
