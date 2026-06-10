<?php

namespace Tests\Unit\Domains\Auth\Services;

use Tests\TestCase;
use App\Domains\Auth\Services\DefaultAuthApiService;
use App\Domains\Auth\Repositories\AuthApiRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Mockery;

class DefaultAuthApiServiceTest extends TestCase
{
    protected $authApiRepositoryMock;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authApiRepositoryMock = Mockery::mock(AuthApiRepositoryInterface::class);
        $this->service = new DefaultAuthApiService($this->authApiRepositoryMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testAuthenticateFailsWhenCredentialsMissing()
    {
        $result = $this->service->authenticate(['email' => 'test@test.com']);
        $this->assertNull($result);

        $result2 = $this->service->authenticate(['password' => 'secret']);
        $this->assertNull($result2);
    }

    public function testAuthenticateFailsWhenUserNotFound()
    {
        $credentials = ['email' => 'test@test.com', 'password' => 'secret'];

        $this->authApiRepositoryMock
            ->shouldReceive('findApiUserByEmail')
            ->with('test@test.com')
            ->once()
            ->andReturn(null);

        $result = $this->service->authenticate($credentials);
        
        $this->assertNull($result);
    }
}
