<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Constants;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Events\Services\EventServiceInterface;
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

class EventController extends Controller
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
        UserRepositoryInterface $userRepository
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
    }

    public function list(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) abort(403);

        $perPage = $request->input('per_page', 10);
        $events = $this->eventRepository->list($request, $perPage);
        $users = $this->userRepository->allNonApi();

        return Inertia::render('Auth/Event/EventList', [
            'events' => $events,
            'filters' => $request->only(['name', 'status', 'consultant', 'client']),
            'customers' => $this->customerRepository->all(),
            'users' => $users,
            'allStatus' => Constants::STATUS
        ]);
    }

    public function create(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) abort(403);

        $event = $this->eventRepository->findWithLocals($request->id);
        
        return Inertia::render('Auth/Event/EventCreate', array_merge([
            'event' => $event,
            'customers' => $this->customerRepository->all(),
            'crds' => $this->customerRepository->allCrdsWithCustomer(),
            'users' => $this->userRepository->allNonApi(),
            'providers' => $this->providerRepository->allWithCity(),
            'providersService' => $this->providerRepository->allServicesWithCity(),
            'providersTranport' => $this->providerRepository->allTransportWithCity(),
            'brokers' => $this->lookupRepository->getAllBrokers(),
            'currencies' => $this->lookupRepository->getAllCurrencies(),
            'regimes' => $this->lookupRepository->getAllRegimes(),
            'purposes' => $this->lookupRepository->getAllPurposes(),
            'services' => $this->lookupRepository->getAllServices(),
            'servicesType' => $this->lookupRepository->getAllServiceTypes(),
            'locals' => $this->lookupRepository->getAllLocals(),
            'allStatus' => Constants::STATUS,
            'eventHotels' => $this->eventHotelRepository->getByEvent($request->id),
            'eventABs' => $this->eventABRepository->getByEvent($request->id),
            'eventHalls' => $this->eventHallRepository->getByEvent($request->id),
            'eventAdds' => $this->eventAddRepository->getByEvent($request->id),
            'eventTransports' => $this->eventTransportRepository->getByEvent($request->id),
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
        ], $this->getTabDetails($request)));
    }

    protected function getTabDetails(Request $request)
    {
        $details = ['eventHotel' => null, 'eventAB' => null, 'eventHall' => null, 'eventAdd' => null, 'eventTransport' => null];
        if (!$request->ehotel) return $details;

        switch ($request->tab) {
            case 1: $details['eventHotel'] = $this->eventHotelRepository->findWithDetails($request->ehotel); break;
            case 2: $details['eventAB'] = $this->eventABRepository->findWithDetails($request->ehotel); break;
            case 3: $details['eventHall'] = $this->eventHallRepository->findWithDetails($request->ehotel); break;
            case 4: $details['eventAdd'] = $this->eventAddRepository->findWithDetails($request->ehotel); break;
            case 5: $details['eventTransport'] = $this->eventTransportRepository->findWithDetails($request->ehotel); break;
        }
        return $details;
    }

    public function store(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) abort(403);

        try {
            $event = $this->eventService->store($request->all(), $request->id > 0 ? $request->id : null);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('event-edit', ['id' => $event->id])->with('flash', ['message' => 'Evento salvo com sucesso!', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) abort(403);

        try {
            $this->eventService->delete($request->id);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('event-list')->with('flash', ['message' => 'Evento apagado com sucesso!', 'type' => 'success']);
    }

    public function saveExchangeRate(Request $request)
    {
        $request->validate(['event_id' => 'required|integer', 'exchange_rates' => 'required|array']);
        $this->eventRepository->update($request->event_id, ['exchange_rates' => $request->exchange_rates], []);
        return redirect()->back()->with('flash', ['message' => 'Taxas de câmbio salvas com sucesso!', 'type' => 'success']);
    }

    public function saveValorFaturamento(Request $request)
    {
        $request->validate(['event_id' => 'required|integer', 'vl_faturamento' => 'required|numeric']);
        $this->eventRepository->update($request->event_id, ['valor_faturamento' => $request->vl_faturamento], []);
        return redirect()->back()->with('flash', ['message' => 'Valor de faturamento salvo com sucesso!', 'type' => 'success']);
    }
}
