<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Providers\Services\ProviderServiceInterface;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;
use App\Domains\Transports\Repositories\EventTransportRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

class ProviderTransportController extends Controller
{
    protected $providerService;
    protected $lookupRepository;
    protected $eventTransportRepository;
    protected $statusHistoryRepository;
    protected $userRepository;

    public function __construct(
        ProviderServiceInterface $providerService,
        LookupRepositoryInterface $lookupRepository,
        EventTransportRepositoryInterface $eventTransportRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->providerService = $providerService;
        $this->lookupRepository = $lookupRepository;
        $this->eventTransportRepository = $eventTransportRepository;
        $this->statusHistoryRepository = $statusHistoryRepository;
        $this->userRepository = $userRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('admin_provider_transport')) abort(403);
        $this->providerService->bulkActivate([$id], 'transport');
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('admin_provider_transport')) abort(403);
        $this->providerService->bulkDeactivate([$id], 'transport');
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function bulkAction(Request $request)
    {
        if (!Gate::allows('admin_provider_transport')) abort(403);
        
        $ids = $request->input('ids', []);
        $action = $request->input('action'); 
        
        if (empty($ids)) return redirect()->back()->with('flash', ['message' => 'Nenhum registro selecionado.', 'type' => 'warning']);

        if ($action === 'activate') {
            $this->providerService->bulkActivate($ids, 'transport');
        } else {
            $this->providerService->bulkDeactivate($ids, 'transport');
        }

        return redirect()->back()->with('flash', ['message' => 'Ação em massa executada com sucesso!', 'type' => 'success']);
    }

    public function create(Request $request)
    {
        if (!Gate::allows('admin_provider_transport')) abort(403);

        return Inertia::render('Auth/Auxiliaries/ProviderTransport', [
            'hotels' => $this->lookupRepository->getProviderTransportsWithInactive(),
            'cities' => $this->lookupRepository->getAllCities()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('admin_provider_transport')) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:255'
        ]);

        try {
            $data = $request->only([
                'name', 'city_id', 'contact', 'phone', 'email', 
                'national', 'iss_percent', 'service_percent', 'iva_percent', 'payment_method'
            ]);
            $data['city_id'] = $request->city;
            $this->lookupRepository->saveProviderTransport($data, $request->id > 0 ? $request->id : null);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function storeEventProvider(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) abort(403);

        $request->validate([
            'provider_id' => 'required|integer',
            'event_id' => 'required|integer',
            'currency' => 'required|integer',
            'taxa_4bts' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            $isStatusLevel2 = $user->getPermissions()->contains('name', 'status_level_2');

            if ($request->id > 0) {
                if (!$isStatusLevel2) {
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_transports', $request->id)) {
                        return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'transport')) {
                        return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                    }
                }
            } else {
                if (!$isStatusLevel2 && $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'transport')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $data = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'currency_id', 'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date'
            ]);
            $data['transport_id'] = $request->provider_id;
            $data['currency_id'] = $request->currency;

            $provider = $this->eventTransportRepository->saveEventTransport($data, $request->id > 0 ? $request->id : null);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create(['status' => "created", 'user_id' => Auth::user()->id, 'table' => "event_transports", 'table_id' => $provider->id]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('event-edit', ['id' => $request->event_id, 'tab' => 5, 'ehotel' => $provider->id])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('admin_provider_transport')) abort(403);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            $isStatusLevel2 = $user->getPermissions()->contains('name', 'status_level_2');

            if (!$isStatusLevel2 && $this->statusHistoryRepository->isBlockedTableRecord('event_transports', $request->id)) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
            }

            $this->lookupRepository->deleteProviderTransport($request->id);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
