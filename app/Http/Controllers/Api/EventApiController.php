<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Domains\Events\Services\EventApiServiceInterface;

class EventApiController extends BaseApiController
{
    protected EventApiServiceInterface $eventApiService;

    public function __construct(EventApiServiceInterface $eventApiService)
    {
        $this->eventApiService = $eventApiService;
    }

    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Consulta eventos em XML",
     *     description="Retorna um XML com os eventos cadastrados no sistema, filtrando por data inicial e final.",
     *     tags={"Eventos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         description="Data inicial no formato YYYY-MM-DD",
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         description="Data final no formato YYYY-MM-DD",
     *         @OA\Schema(type="string", format="date", example="2025-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="XML com os eventos",
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(type="string", format="xml")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autorizado",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *     )
     * )
     */
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $xmlString = $this->eventApiService->generateXmlPayload($request->start_date, $request->end_date);

        return response($xmlString, 200)
            ->header('Content-Type', 'application/xml');
    }
}
