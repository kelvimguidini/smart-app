<?php

namespace Tests\Unit\Domains\Events\Services;

use Tests\TestCase;
use App\Domains\Events\Services\DefaultEventApiService;
use App\Domains\Events\Repositories\EventApiRepositoryInterface;
use Mockery;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\Event;

class DefaultEventApiServiceTest extends TestCase
{
    protected $eventApiRepositoryMock;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->eventApiRepositoryMock = Mockery::mock(EventApiRepositoryInterface::class);
        $this->service = new DefaultEventApiService($this->eventApiRepositoryMock);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateXmlPayloadEmpty()
    {
        $startDate = '2025-01-01';
        $endDate = '2025-01-31';

        $start_carbon = Carbon::parse($startDate, 'America/Sao_Paulo')->startOfDay()->timezone('UTC');
        $end_carbon = Carbon::parse($endDate, 'America/Sao_Paulo')->endOfDay()->timezone('UTC');

        $this->eventApiRepositoryMock
            ->shouldReceive('getEventsForExport')
            ->with(Mockery::on(function ($arg) use ($start_carbon) {
                return $arg == $start_carbon;
            }), Mockery::on(function ($arg) use ($end_carbon) {
                return $arg == $end_carbon;
            }))
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection([]));

        $xml = $this->service->generateXmlPayload($startDate, $endDate);
        
        $this->assertStringContainsString('<intagi>', $xml);
        $this->assertStringContainsString('<versaolayout>V4.4</versaolayout>', $xml);
        $this->assertStringContainsString('<clientes/>', $xml);
        $this->assertStringContainsString('<fornecedores/>', $xml);
        $this->assertStringContainsString('<vendas/>', $xml);
    }
}
