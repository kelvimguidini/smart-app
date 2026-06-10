<?php

namespace Tests\Unit\Http\Controllers\Api;

use Tests\TestCase;
use App\Http\Controllers\Api\AuthController;
use App\Domains\Auth\Services\AuthApiServiceInterface;
use Illuminate\Http\Request;
use Mockery;
use Illuminate\Http\JsonResponse;

class AuthControllerTest extends TestCase
{
    protected $authApiServiceMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authApiServiceMock = Mockery::mock(AuthApiServiceInterface::class);
        $this->controller = new AuthController($this->authApiServiceMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLoginSuccess()
    {
        $request = Request::create('/api/login', 'POST', [
            'email' => 'test@test.com',
            'password' => 'secret'
        ]);

        $tokenData = [
            'access_token' => 'dummy-token',
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];

        $this->authApiServiceMock
            ->shouldReceive('authenticate')
            ->once()
            ->with(['email' => 'test@test.com', 'password' => 'secret'])
            ->andReturn($tokenData);

        $response = $this->controller->login($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('dummy-token', $response->getData()->access_token);
    }
    
    public function testLoginFails()
    {
        $request = Request::create('/api/login', 'POST', [
            'email' => 'wrong@test.com',
            'password' => 'secret'
        ]);

        $this->authApiServiceMock
            ->shouldReceive('authenticate')
            ->once()
            ->with(['email' => 'wrong@test.com', 'password' => 'secret'])
            ->andReturn(null);

        $response = $this->controller->login($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Unauthorized', $response->getData()->message);
    }
}
