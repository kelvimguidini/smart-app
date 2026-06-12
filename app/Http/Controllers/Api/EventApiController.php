<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Constants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Events\Services\EventServiceInterface;
use App\Domains\Events\Services\EventApiServiceInterface;
use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Domains\Hotels\Repositories\EventHotelRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EventABRepositoryInterface;
use App\Domains\Halls\Repositories\EventHallRepositoryInterface;
use App\Domains\Additions\Repositories\EventAddRepositoryInterface;
use App\Domains\Transports\Repositories\EventTransportRepositoryInterface;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Domains\Providers\Repositories\ProviderRepositoryInterface;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Http\Middleware\Constants as MiddlewareConstants;

class EventApiController extends Controller
{
    protected $eventService;
    protected $eventRepository;
    protected $eventHotelRepository;
    protected $eventABRepository;
    protected $eventHallRepository;
    protected $eventAddRepository;
    protected $eventTransportRepository;
    protected $customerRepository;
    protected $providerRepository;
    protected $lookupRepository;
    protected $userRepository;
    protected $eventApiService;

    public function __construct(
        EventServiceInterface $eventService,
        EventRepositoryInterface $eventRepository,
        EventHotelRepositoryInterface $eventHotelRepository,
        EventABRepositoryInterface $eventABRepository,
        EventHallRepositoryInterface $eventHallRepository,
        EventAddRepositoryInterface $eventAddRepository,
        EventTransportRepositoryInterface $eventTransportRepository,
        CustomerRepositoryInterface $customerRepository,
        ProviderRepositoryInterface $providerRepository,
        LookupRepositoryInterface $lookupRepository,
        UserRepositoryInterface $userRepository,
        EventApiServiceInterface $eventApiService
    ) {
        $this->eventService = $eventService;
        $this->eventRepository = $eventRepository;
        $this->eventHotelRepository = $eventHotelRepository;
        $this->eventABRepository = $eventABRepository;
        $this->eventHallRepository = $eventHallRepository;
        $this->eventAddRepository = $eventAddRepository;
        $this->eventTransportRepository = $eventTransportRepository;
        $this->customerRepository = $customerRepository;
        $this->providerRepository = $providerRepository;
        $this->lookupRepository = $lookupRepository;
        $this->userRepository = $userRepository;
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

    /**
     * GET /api/events
     * Paginated & filtered list of events for the Angular UI
     */
    public function listEvents(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->input('per_page', 10);
        $events = $this->eventRepository->list($request, $perPage);

        return response()->json($events);
    }

    /**
     * GET /api/events/{id}/edit-data
     * Gathers all necessary dropdown lookups and the event details (if id > 0)
     * in a single performant payload.
     */
    public function getEditData(Request $request, $id = 0)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = null;
        $eventHotels = [];
        $eventABs = [];
        $eventHalls = [];
        $eventAdds = [];
        $eventTransports = [];
        $selectedHotel = null;
        $selectedAB = null;
        $selectedHall = null;
        $selectedAdd = null;
        $selectedTransport = null;

        if ($id > 0) {
            $event = $this->eventRepository->findWithLocals($id);
            if (!$event) {
                return response()->json(['message' => 'Evento não encontrado.'], 404);
            }
            $eventHotels = $this->eventHotelRepository->getByEvent($id);
            $eventABs = $this->eventABRepository->getByEvent($id);
            $eventHalls = $this->eventHallRepository->getByEvent($id);
            $eventAdds = $this->eventAddRepository->getByEvent($id);
            $eventTransports = $this->eventTransportRepository->getByEvent($id);

            // Tab detail requests (ehotel/tab query params)
            if ($request->has('ehotel') && $request->has('tab')) {
                $tab = (int)$request->tab;
                $ehotelId = (int)$request->ehotel;
                switch ($tab) {
                    case 1:
                        $selectedHotel = $this->eventHotelRepository->findWithDetails($ehotelId);
                        break;
                    case 2:
                        $selectedAB = $this->eventABRepository->findWithDetails($ehotelId);
                        break;
                    case 3:
                        $selectedHall = $this->eventHallRepository->findWithDetails($ehotelId);
                        break;
                    case 4:
                        $selectedAdd = $this->eventAddRepository->findWithDetails($ehotelId);
                        break;
                    case 5:
                        $selectedTransport = $this->eventTransportRepository->findWithDetails($ehotelId);
                        break;
                }
            }
        }

        $payload = [
            'event' => $event,
            'customers' => $this->customerRepository->all(),
            'crds' => $this->customerRepository->allCrdsWithCustomer(),
            'users' => $this->userRepository->allNonApi(),
            'providers' => $this->providerRepository->allWithCity(),
            'providersService' => $this->providerRepository->allServicesWithCity(),
            'providersTransport' => $this->providerRepository->allTransportWithCity(),
            'brokers' => $this->lookupRepository->getAllBrokers(),
            'currencies' => $this->lookupRepository->getAllCurrencies(),
            'regimes' => $this->lookupRepository->getAllRegimes(),
            'purposes' => $this->lookupRepository->getAllPurposes(),
            'services' => $this->lookupRepository->getAllServices(),
            'servicesType' => $this->lookupRepository->getAllServiceTypes(),
            'locals' => $this->lookupRepository->getAllLocals(),
            'allStatus' => MiddlewareConstants::STATUS,
            'customerMetadata' => \App\Models\CustomerMetadata::active()->get(),
            'eventHotels' => $eventHotels,
            'eventABs' => $eventABs,
            'eventHalls' => $eventHalls,
            'eventAdds' => $eventAdds,
            'eventTransports' => $eventTransports,
            'catsHotel' => $this->lookupRepository->getAllCategories(),
            'aptosHotel' => $this->lookupRepository->getAllAptos(),
            'brokersT' => $this->lookupRepository->getAllBrokerTransports(),
            'vehicles' => $this->lookupRepository->getAllVehicles(),
            'models' => $this->lookupRepository->getAllCarModels(),
            'servicesT' => $this->lookupRepository->getAllTransportServices(),
            'brands' => $this->lookupRepository->getAllBrands(),
            'servicesHall' => $this->lookupRepository->getAllServiceHalls(),
            'purposesHall' => $this->lookupRepository->getAllPurposeHalls(),
            'servicesAdd' => $this->lookupRepository->getAllServiceAdds(),
            'frequencies' => $this->lookupRepository->getAllFrequencies(),
            'measures' => $this->lookupRepository->getAllMeasures(),

            // Sub-elements requested
            'selectedHotel' => $selectedHotel,
            'selectedAB' => $selectedAB,
            'selectedHall' => $selectedHall,
            'selectedAdd' => $selectedAdd,
            'selectedTransport' => $selectedTransport,
        ];

        return response()->json($payload);
    }

    /**
     * POST /api/events
     * Handles creating and updating event details.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'date' => $request->has('id') && $request->id > 0 ? 'sometimes|date' : 'required|date',
            'date_final' => $request->has('id') && $request->id > 0 ? 'sometimes|date|after_or_equal:date' : 'required|date|after_or_equal:date',
        ]);

        try {
            $eventId = $request->id > 0 ? $request->id : null;
            $data = $request->all();

            // Map paxBase to pax_base if provided in frontend camelCase
            if ($request->has('paxBase')) {
                $data['pax_base'] = $request->paxBase;
            }
            if ($request->has('cc')) {
                $data['cost_center'] = $request->cc;
            }
            if ($request->has('customer')) {
                $data['customer_id'] = $request->customer;
            }

            $event = $this->eventService->store($data, $eventId);

            // Dynamically save event locations (countries/cities) sent by the Angular app
            if ($request->has('countries')) {
                $event->eventLocals()->delete();
                foreach ($request->countries as $country) {
                    if (!empty($country['pais']) || !empty($country['cidade'])) {
                        $event->eventLocals()->create([
                            'pais' => $country['pais'] ?? '',
                            'cidade' => $country['cidade'] ?? '',
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Evento salvo com sucesso!',
                'event' => $event
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar evento.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/events/{id}
     * Deletes an event and its related tables.
     */
    public function destroy($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->eventService->delete($id);
            return response()->json(['message' => 'Evento apagado com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao apagar evento.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/events/save-exchange-rate
     * Saves exchange rates on an event.
     */
    public function saveExchangeRate(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer',
            'exchange_rates' => 'required|array'
        ]);

        try {
            $this->eventRepository->update($request->event_id, ['exchange_rates' => $request->exchange_rates], []);
            return response()->json(['message' => 'Taxas de câmbio salvas com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar taxas.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/events/save-vl-faturamento
     * Saves value of billing/faturamento.
     */
    public function saveValorFaturamento(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer',
            'vl_faturamento' => 'required|numeric'
        ]);

        try {
            $this->eventRepository->update($request->event_id, ['valor_faturamento' => $request->vl_faturamento], []);
            return response()->json(['message' => 'Valor de faturamento salvo com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar faturamento.', 'error' => $e->getMessage()], 500);
        }
    }
}
