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
use App\Domains\Additions\Repositories\EventAddRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

class ProviderServicesController extends Controller
{
    protected $providerService;
    protected $lookupRepository;
    protected $eventAddRepository;
    protected $statusHistoryRepository;
    protected $userRepository;

    public function __construct(
        ProviderServiceInterface $providerService,
        LookupRepositoryInterface $lookupRepository,
        EventAddRepositoryInterface $eventAddRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->providerService = $providerService;
        $this->lookupRepository = $lookupRepository;
        $this->eventAddRepository = $eventAddRepository;
        $this->statusHistoryRepository = $statusHistoryRepository;
        $this->userRepository = $userRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('admin_provider_service')) abort(403);
        $this->providerService->bulkActivate([$id], 'service');
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('admin_provider_service')) abort(403);
        $this->providerService->bulkDeactivate([$id], 'service');
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function bulkAction(Request $request)
    {
        if (!Gate::allows('admin_provider_service')) abort(403);
        
        $ids = $request->input('ids', []);
        $action = $request->input('action'); 
        
        if (empty($ids)) return redirect()->back()->with('flash', ['message' => 'Nenhum registro selecionado.', 'type' => 'warning']);

        if ($action === 'activate') {
            $this->providerService->bulkActivate($ids, 'service');
        } else {
            $this->providerService->bulkDeactivate($ids, 'service');
        }

        return redirect()->back()->with('flash', ['message' => 'Ação em massa executada com sucesso!', 'type' => 'success']);
    }

    public function create(Request $request)
    {
        if (!Gate::allows('event_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/ProviderServices', [
            'hotels' => $this->lookupRepository->getProviderServicesWithInactive(),
            'cities' => $this->lookupRepository->getAllCities()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('admin_provider_service')) abort(403);

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
            $this->lookupRepository->saveProviderService($data, $request->id > 0 ? $request->id : null);
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
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_adds', $request->id)) {
                        return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'add')) {
                        return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                    }
                }
            } else {
                if (!$isStatusLevel2 && $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'add')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $data = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'currency_id', 'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date'
            ]);
            $data['add_id'] = $request->provider_id;
            $data['currency_id'] = $request->currency;

            $provider = $this->eventAddRepository->saveEventAdd($data, $request->id > 0 ? $request->id : null);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create(['status' => "created", 'user_id' => Auth::user()->id, 'table' => "event_adds", 'table_id' => $provider->id]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('event-edit', ['id' => $request->event_id, 'tab' => 4, 'ehotel' => $provider->id])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('admin_provider_service')) abort(403);
        $this->lookupRepository->deleteProviderService($request->id);
        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
