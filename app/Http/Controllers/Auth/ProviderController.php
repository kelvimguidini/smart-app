<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Providers\Services\ProviderServiceInterface;
use App\Domains\Providers\Repositories\ProviderRepositoryInterface;
use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Domains\Shared\Repositories\CityRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Domains\Hotels\Repositories\EventHotelRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EventABRepositoryInterface;
use App\Domains\Halls\Repositories\EventHallRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

class ProviderController extends Controller
{
    protected $providerService;
    protected $providerRepository;
    protected $eventRepository;
    protected $cityRepository;
    protected $statusHistoryRepository;
    protected $eventHotelRepository;
    protected $eventABRepository;
    protected $eventHallRepository;
    protected $userRepository;

    public function __construct(
        ProviderServiceInterface $providerService,
        ProviderRepositoryInterface $providerRepository,
        EventRepositoryInterface $eventRepository,
        CityRepositoryInterface $cityRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository,
        EventHotelRepositoryInterface $eventHotelRepository,
        EventABRepositoryInterface $eventABRepository,
        EventHallRepositoryInterface $eventHallRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->providerService = $providerService;
        $this->providerRepository = $providerRepository;
        $this->eventRepository = $eventRepository;
        $this->cityRepository = $cityRepository;
        $this->statusHistoryRepository = $statusHistoryRepository;
        $this->eventHotelRepository = $eventHotelRepository;
        $this->eventABRepository = $eventABRepository;
        $this->eventHallRepository = $eventHallRepository;
        $this->userRepository = $userRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('admin_provider')) abort(403);
        $this->providerService->bulkActivate([$id], 'hotel');
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('admin_provider')) abort(403);
        $this->providerService->bulkDeactivate([$id], 'hotel');
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function bulkAction(Request $request)
    {
        if (!Gate::allows('admin_provider')) abort(403);
        
        $ids = $request->input('ids', []);
        $action = $request->input('action'); // activate / deactivate
        $type = $request->input('type', 'hotel');

        if (empty($ids)) return redirect()->back()->with('flash', ['message' => 'Nenhum registro selecionado.', 'type' => 'warning']);

        if ($action === 'activate') {
            $this->providerService->bulkActivate($ids, $type);
        } else {
            $this->providerService->bulkDeactivate($ids, $type);
        }

        return redirect()->back()->with('flash', ['message' => 'Ação em massa executada com sucesso!', 'type' => 'success']);
    }

    public function create(Request $request)
    {
        if (!Gate::allows('admin_provider')) abort(403);

        return Inertia::render('Auth/Auxiliaries/Provider', [
            'hotels' => $this->providerRepository->allWithCityAdmin(),
            'cities' => $this->cityRepository->all()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('admin_provider')) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:255',
        ]);

        try {
            $this->providerService->storeProvider($request->all(), $request->id > 0 ? $request->id : null, Auth::user()->id);
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function storeEventProvider(Request $request)
    {
        // ... (Keep this logic here as it's very specific to the link between event and provider, or move to EventService later)
        // For now, I'll keep it to focus on bulk provider management
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) abort(403);

        $request->validate([
            'provider_id' => 'required|integer',
            'event_id' => 'required|integer',
            'currency' => 'required|integer',
            'taxa_4bts' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if ($request->id > 0) {
                if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                    if ($this->statusHistoryRepository->isBlockedTableRecord("event_{$request->type}s", $request->id)) {
                        return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, $request->type)) {
                        return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, $request->type)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'currency_id', 'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method'
            ]);
            $providerData['currency_id'] = $request->currency;

            if ($request->type == 'hotel') {
                $providerData = array_merge($providerData, $request->only(['checkin_time', 'checkin_time_end', 'checkout_time', 'checkout_time_end']));
            }

            $provider = null;
            switch ($request->type) {
                case 'hotel':
                    $providerData['hotel_id'] = $request->provider_id;
                    $provider = $request->id > 0 ? $this->eventHotelRepository->update($request->id, $providerData) : $this->eventHotelRepository->create($providerData);
                    $tab = 1; break;
                case 'ab':
                    $providerData['ab_id'] = $request->provider_id;
                    $provider = $request->id > 0 ? $this->eventABRepository->update($request->id, $providerData) : $this->eventABRepository->create($providerData);
                    $tab = 2; break;
                case 'hall':
                    $providerData['hall_id'] = $request->provider_id;
                    $provider = $request->id > 0 ? $this->eventHallRepository->update($request->id, $providerData) : $this->eventHallRepository->create($providerData);
                    $tab = 3; break;
            }

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create(['status' => "created", 'user_id' => Auth::user()->id, 'table' => "event_{$request->type}s", 'table_id' => $provider->id]);
            }

        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('event-edit', ['id' => $request->event_id, 'tab' => $tab, 'ehotel' => $provider->id])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('admin_provider')) abort(403);
        try {
            $this->providerRepository->delete($request->id);
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    public function proposalPdf(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator')) abort(403);
        
        if ($request->download == "true") {
            // Need to keep legacy createPDF logic for direct download or use a helper
            // For simplicity, I'll use the service for email and keep a local helper for download if needed
            // Actually, I can use the service to generate the content and then stream it
            return $this->handleDownload($request, 1);
        }

        $this->providerService->sendDocument($request->all(), Auth::user()->id, 1);
        return redirect()->back()->with('flash', ['message' => 'E-mail enviado com sucesso!', 'type' => 'success']);
    }

    public function proposalPdfWithoutValues(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator')) abort(403);
        
        if ($request->download == "true") return $this->handleDownload($request, 3);

        $this->providerService->sendDocument($request->all(), Auth::user()->id, 3);
        return redirect()->back()->with('flash', ['message' => 'E-mail enviado com sucesso!', 'type' => 'success']);
    }

    public function invoicingPdf(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator')) abort(403);
        
        if ($request->download == "true") return $this->handleDownload($request, 2);

        $this->providerService->sendDocument($request->all(), Auth::user()->id, 2);
        return redirect()->back()->with('flash', ['message' => 'E-mail enviado com sucesso!', 'type' => 'success']);
    }

    protected function handleDownload($request, $type)
    {
        $data = $this->eventRepository->getProposalData($request->event_id, $request->provider_id, $request->type);
        
        $view = 'proposalPdf';
        if ($type === 2) $view = 'invoicePDF';
        elseif ($type === 3) $view = 'proposalPdfWithoutValues';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', base_path('public'));
        $pdf = new \Dompdf\Dompdf($options);
        $html = view($view, ['event' => $data['eventDataBase'], 'provider' => $data['providerDataBase'], 'table' => $data['table']])->render();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $filename = "ID{$request->event_id} - " . ($data['providerDataBase']->name ?? '') . " - Documento.pdf";
        return $pdf->stream($filename);
    }
}
