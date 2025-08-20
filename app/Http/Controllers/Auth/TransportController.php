<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PdfEmail;
use App\Models\Event;
use App\Models\EventAB;
use App\Models\EventAdd;
use App\Models\EventHall;
use App\Models\EventHotel;
use App\Models\EventTransport;
use App\Models\EventTransportOpt;
use App\Models\StatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TransportController extends Controller
{

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }

        $request->validate([
            'broker' => 'required|integer',
            'vehicle' => 'required|integer',
            'model' => 'required|integer',
            'service' => 'required|integer',
            'brand' => 'required|integer',
            'in' => 'required|date',
            'out' => 'required|date|after_or_equal:in',
            'received_proposal' => 'required|numeric',
            'received_proposal_percent' => 'required|numeric',
            'kickback' => 'required|numeric',
            'count' => 'required|numeric'
        ]);

        try {

            $user = User::find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                $history = StatusHistory::with('user')->where('table', 'event_transports')
                    ->where('table_id', $request->event_transport_id)
                    ->where('table', 'event_transports')
                    ->latest('created_at')
                    ->first();

                if ($history && ($history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                }
            }

            if ($request->id > 0) {

                $opt = EventTransportOpt::find($request->id);

                $opt->event_transport_id = $request->event_transport_id;
                $opt->broker_id = $request->broker;
                $opt->vehicle_id = $request->vehicle;
                $opt->model_id = $request->model;
                $opt->service_id = $request->service;
                $opt->brand_id = $request->brand;
                $opt->observation = $request->observation;
                $opt->in = $request->in;
                $opt->out = $request->out;
                $opt->received_proposal_percent = $request->received_proposal_percent;
                $opt->received_proposal = $request->received_proposal;
                $opt->kickback = $request->kickback;
                $opt->count = $request->count;
                $opt->order = $request->order;

                $opt->save();
            } else {

                $opt = EventTransportOpt::create([
                    'event_transport_id' => $request->event_transport_id,
                    'broker_id' => $request->broker,
                    'model_id' => $request->model,
                    'service_id' => $request->service,
                    'brand_id' => $request->brand,
                    'vehicle_id' => $request->vehicle,
                    'observation' => $request->observation,
                    'in' => $request->in,
                    'out' => $request->out,
                    'received_proposal_percent' => $request->received_proposal_percent,
                    'received_proposal' => $request->received_proposal,
                    'kickback' => $request->kickback,
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
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function eventTransportDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('transport_operator')) {
            abort(403);
        }
        try {

            $user = User::find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                $history = StatusHistory::with('user')->where('table', 'event_transports')
                    ->where('table_id', $request->id)
                    ->where('table', 'event_transports')
                    ->latest('created_at')
                    ->first();

                if ($history && ($history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
                }
            }

            $r = EventTransport::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 5])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function optDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }
        try {


            $opt = EventTransportOpt::with('event_transport')->find($request->id);
            $eventHotel = $opt->event_transport()->first();

            if (!$eventHotel) {
                return redirect()->back()->with('flash', [
                    'message' => 'Erro: Registro não associado a um hotel válido!',
                    'type' => 'danger'
                ]);
            }


            $user = User::find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                // Buscar o status mais recente do EventHotel
                $history = StatusHistory::where('table', 'event_transports')
                    ->where('table_id', $eventHotel->id)
                    ->latest('created_at')
                    ->first();

                // Verifica se o status atual impede a exclusão
                if ($history && in_array($history->status, ['dating_with_customer', 'Cancelled'])) {
                    return redirect()->back()->with('flash', [
                        'message' => 'Esse registro não pode ser apagado devido ao status atual!',
                        'type' => 'danger'
                    ]);
                }
            }
            // Excluir o registro do Opt
            $opt->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
