<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EventAB;
use App\Models\EventABOpt;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ABController extends Controller
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
            'local_id' => 'required|integer',
            'service_id' => 'required|integer',
            'service_type_id' => 'required|integer',
            'in' => 'required|date',
            'out' => 'required|date|after_or_equal:in',
            'received_proposal' => 'required|numeric',
            'received_proposal_percent' => 'required|numeric',
            'kickback' => 'required|numeric',
            'count' => 'required|integer',
        ]);

        try {

            if ($request->id > 0) {
                $opt = EventABOpt::find($request->id);

                $opt->event_ab_id = $request->event_ab_id;
                $opt->broker_id = $request->broker;
                $opt->local_id = $request->local_id;
                $opt->service_id = $request->service_id;
                $opt->service_type_id = $request->service_type_id;
                $opt->in = $request->in;
                $opt->out = $request->out;
                $opt->received_proposal_percent = $request->received_proposal_percent;
                $opt->received_proposal = $request->received_proposal;
                $opt->kickback = $request->kickback;
                $opt->count = $request->count;

                $opt->save();
            } else {

                $opt = EventABOpt::create([
                    'event_ab_id' => $request->event_ab_id,
                    'broker_id' => $request->broker,
                    'local_id' => $request->local_id,
                    'service_id' => $request->service_id,
                    'service_type_id' => $request->service_type_id,
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
    public function eventABDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }
        try {

            $r = EventAB::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 2])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
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

            $r = EventABOpt::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
