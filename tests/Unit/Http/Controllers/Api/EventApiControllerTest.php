<?php

namespace Tests\Unit\Http\Controllers\Api;

use Tests\TestCase;
use App\Http\Controllers\Api\EventApiController;
use App\Domains\Events\Services\EventApiServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Illuminate\Http\Response;

class EventApiControllerTest extends TestCase
{
    protected $eventApiServiceMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eventApiServiceMock = Mockery::mock(EventApiServiceInterface::class);
        $this->app->instance(EventApiServiceInterface::class, $this->eventApiServiceMock);
        $this->controller = $this->app->make(EventApiController::class);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexSuccess()
    {
        $request = Request::create('/api/events', 'GET', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31'
        ]);

        $xmlPayload = '<intagi><versaolayout>V4.4</versaolayout></intagi>';

        $this->eventApiServiceMock
            ->shouldReceive('generateXmlPayload')
            ->once()
            ->with('2025-01-01', '2025-01-31')
            ->andReturn($xmlPayload);

        $response = $this->controller->index($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->headers->get('Content-Type'));
        $this->assertEquals($xmlPayload, $response->getContent());
    }
}
