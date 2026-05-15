<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EventHotelOpt;
use App\Models\StatusHistory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Hotels\Repositories\EventHotelRepositoryInterface;
use App\Domains\Hotels\Repositories\EventHotelOptRepositoryInterface;
use App\Domains\Providers\Repositories\ProviderRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class HotelController extends Controller
{
    protected $eventHotelRepository;
    protected $eventHotelOptRepository;
    protected $providerRepository;
    protected $userRepository;

    public function __construct(
        EventHotelRepositoryInterface $eventHotelRepository,
        EventHotelOptRepositoryInterface $eventHotelOptRepository,
        ProviderRepositoryInterface $providerRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->eventHotelRepository = $eventHotelRepository;
        $this->eventHotelOptRepository = $eventHotelOptRepository;
        $this->providerRepository = $providerRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeHotelOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }

        $request->validate([
            'broker' => 'required|integer',
            'regime' => 'required|integer',
            'purpose' => 'required|integer',
            'in' => 'required|date',
            'out' => 'required|date|after_or_equal:in',
            'received_proposal' => 'nullable|numeric',
            'received_proposal_percent' => 'nullable|numeric',
            'kickback' => 'nullable|numeric',
            'count' => 'nullable|numeric',
            'compare_trivago' => 'nullable|numeric',
            'compare_website_htl' => 'nullable|numeric',
            'compare_omnibess' => 'nullable|numeric',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if (StatusHistory::isBlockedTableRecord('event_hotels', $request->event_hotel_id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                }

                $eventHotel = $this->eventHotelRepository->find($request->event_hotel_id);
                if (StatusHistory::isProviderBlockedInEvent($eventHotel->event_id, $eventHotel->hotel_id, 'hotel')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            if ($request->id > 0) {
                $opt = $this->eventHotelOptRepository->find($request->id);

                $opt->event_hotel_id = $request->event_hotel_id;
                $opt->broker_id = $request->broker;
                $opt->regime_id = $request->regime;
                $opt->purpose_id = $request->purpose;
                $opt->category_hotel_id = $request->category_id;
                $opt->apto_hotel_id = $request->apto_id;
                $opt->in = $request->in;
                $opt->out = $request->out;
                $opt->received_proposal_percent = $request->received_proposal_percent;
                $opt->received_proposal = $request->received_proposal;
                $opt->kickback = $request->kickback;
                $opt->compare_trivago = $request->compare_trivago;
                $opt->compare_website_htl = $request->compare_website_htl;
                $opt->compare_omnibess = $request->compare_omnibess;
                $opt->count = $request->count;
                $opt->order = $request->order;

                $opt->save();
            } else {
                $opt = EventHotelOpt::create([
                    'event_hotel_id' => $request->event_hotel_id,
                    'broker_id' => $request->broker,
                    'regime_id' => $request->regime,
                    'purpose_id' => $request->purpose,
                    'category_hotel_id' => $request->category_id,
                    'apto_hotel_id' => $request->apto_id,
                    'in' => $request->in,
                    'out' => $request->out,
                    'received_proposal_percent' => $request->received_proposal_percent,
                    'received_proposal' => $request->received_proposal,
                    'kickback' => $request->kickback,
                    'compare_trivago' => $request->compare_trivago,
                    'compare_website_htl' => $request->compare_website_htl,
                    'compare_omnibess' => $request->compare_omnibess,
                    'count' => $request->count,
                    'order' => $request->order,
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Remove a provider.
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }
        try {
            $r = $this->providerRepository->find($request->id);
            $r->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    /**
     * Remove an event hotel.
     */
    public function eventHotelDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }
        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && StatusHistory::isBlockedTableRecord('event_hotels', $request->id)) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
            }
            $r = $this->eventHotelRepository->find($request->id);
            $r->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 1])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    /**
     * Remove a hotel option.
     */
    public function optDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }
        try {
            $opt = $this->eventHotelOptRepository->findWithHotel($request->id);
            $eventHotel = $opt->event_hotel;

            if (!$eventHotel) {
                return redirect()->back()->with('flash', [
                    'message' => 'Erro: Registro não associado a um hotel válido!',
                    'type' => 'danger'
                ]);
            }

            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if (StatusHistory::isBlockedTableRecord('event_hotels', $eventHotel->id)) {
                    return redirect()->back()->with('flash', [
                        'message' => 'Esse registro não pode ser apagado devido ao status atual!',
                        'type' => 'danger'
                    ]);
                }

                if (StatusHistory::isProviderBlockedInEvent($eventHotel->event_id, $eventHotel->hotel_id, 'hotel')) {
                    return redirect()->back()->with('flash', [
                        'message' => 'Esse fornecedor já possui um registro bloqueado neste evento!',
                        'type' => 'danger'
                    ]);
                }
            }
            $opt->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
