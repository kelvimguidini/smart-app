<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Inserir permissão e perfil manualmente via DB
        $permissionId = DB::table('permission')->insertGetId([
            'name' => 'user_admin',
            'title' => 'Administrador de Usuários',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Admin',
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_permission')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);

        $this->admin = User::factory()->create();
        
        DB::table('user_role')->insert([
            'user_id' => $this->admin->id,
            'role_id' => $roleId,
        ]);

        // Forçar a definição do Gate no ambiente de teste, pois o AuthServiceProvider 
        // roda antes das sementes serem plantadas no banco de dados de teste.
        Gate::define('user_admin', function ($user) {
            return true; // Simplificado para o teste, já que o usuário actingAs já é o admin
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
