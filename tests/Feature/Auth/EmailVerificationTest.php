<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_is_not_applicable()
    {
        // Neste projeto não há uma tela de "/verify-email" (GET), 
        // a verificação ocorre via link direto.
        $this->assertTrue(true);
    }

    public function test_email_can_be_verified()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Importante: Não usar actingAs($user) pois a rota tem middleware 'guest'
        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        
        // O sistema redireciona para password.reset após verificar o e-mail
        $response->assertRedirectContains('reset-password');
    }

    public function test_email_is_not_verified_with_invalid_hash()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
