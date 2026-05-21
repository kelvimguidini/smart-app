<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_redirects_to_login()
    {
        $response = $this->get('/');

        // Como a home é protegida por auth, deve redirecionar para login
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
