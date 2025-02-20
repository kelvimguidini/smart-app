<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EventAdd;
use App\Models\EventAddOpt;
use App\Models\StatusHistory;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class AddController extends Controller
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
            'frequency' => 'required|integer',
            'measure' => 'required|integer',
            'service' => 'required|integer',
            'in' => 'required|date',
            'out' => 'required|date|after_or_equal:in',
            'received_proposal' => 'required|numeric',
            'received_proposal_percent' => 'required|numeric',
            'kickback' => 'required|numeric',
            'count' => 'required|integer',
        ]);

        try {
            $history = StatusHistory::with('user')->where('table', 'event_adds')
                ->where('table_id', $request->event_add_id)
                ->where('table', 'event_adds')
                ->latest('created_at')
                ->first();

            if ($history && ($history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
            }

            if ($request->id > 0) {

                $opt = EventAddOpt::find($request->id);

                $opt->event_add_id = $request->event_add_id;
                $opt->frequency_id = $request->frequency;
                $opt->measure_id = $request->measure;
                $opt->service_id = $request->service;
                $opt->unit = $request->unit;
                $opt->pax = $request->pax;
                $opt->in = $request->in;
                $opt->out = $request->out;
                $opt->received_proposal_percent = $request->received_proposal_percent;
                $opt->received_proposal = $request->received_proposal;
                $opt->kickback = $request->kickback;
                $opt->count = $request->count;

                $opt->save();
            } else {

                $opt = EventAddOpt::create([
                    'event_add_id' => $request->event_add_id,
                    'frequency_id' => $request->frequency,
                    'measure_id' => $request->measure,
                    'service_id' => $request->service,
                    'unit' => $request->unit,
                    'pax' => $request->pax,
                    'in' => $request->in,
                    'out' => $request->out,
                    'received_proposal_percent' => $request->received_proposal_percent,
                    'received_proposal' => $request->received_proposal,
                    'kickback' => $request->kickback,
                    'count' => $request->count,
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
    public function eventAddDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }
        try {
            $history = StatusHistory::with('user')->where('table', 'event_adds')
                ->where('table_id', $request->id)
                ->where('table', 'event_adds')
                ->latest('created_at')
                ->first();

            if ($history && ($history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
            }


            $r = EventAdd::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 4])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
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



            $opt = EventAddOpt::with('event_add')->find($request->id);
            $eventHotel = $opt->event_add()->first();

            if (!$eventHotel) {
                return redirect()->back()->with('flash', [
                    'message' => 'Erro: Registro não associado a um hotel válido!',
                    'type' => 'danger'
                ]);
            }

            // Buscar o status mais recente do EventHotel
            $history = StatusHistory::where('table', 'event_add')
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
            // Excluir o registro do Opt
            $opt->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
