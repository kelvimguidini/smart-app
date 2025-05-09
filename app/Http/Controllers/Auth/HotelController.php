<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PdfEmail;
use App\Models\Event;
use App\Models\EventAB;
use App\Models\EventAdd;
use App\Models\EventHall;
use App\Models\EventHotel;
use App\Models\EventHotelOpt;
use App\Models\Provider;
use App\Models\StatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;

class HotelController extends Controller
{

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
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
            'received_proposal' => 'required|numeric',
            'received_proposal_percent' => 'required|numeric',
            'kickback' => 'required|numeric',
            'count' => 'required|integer',
            'compare_trivago' => 'required|numeric',
            'compare_website_htl' => 'required|numeric',
            'compare_omnibess' => 'required|numeric',
        ]);

        try {

            $user = User::find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                $history = StatusHistory::with('user')->where('table', 'event_hotels')
                    ->where('table_id', $request->event_hotel_id)
                    ->where('table', 'event_hotels')
                    ->latest('created_at')
                    ->first();

                if ($history && ($history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                }
            }

            if ($request->id > 0) {

                $opt = EventHotelOpt::find($request->id);

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
    public function delete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }
        try {

            $r = Provider::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function eventHotelDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }
        try {

            $user = User::find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                $history = StatusHistory::with('user')->where('table', 'event_hotels')
                    ->where('table_id', $request->id)
                    ->where('table', 'event_hotels')
                    ->latest('created_at')
                    ->first();

                if ($history && ($history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
                }
            }
            $r = EventHotel::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 1])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function optDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }
        try {

            $opt = EventHotelOpt::with('event_hotel')->find($request->id);
            $eventHotel = $opt->event_hotel()->first();

            if (!$eventHotel) {
                return redirect()->back()->with('flash', [
                    'message' => 'Erro: Registro não associado a um hotel válido!',
                    'type' => 'danger'
                ]);
            }


            $user = User::find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                // Buscar o status mais recente do EventHotel
                $history = StatusHistory::where('table', 'event_hotels')
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
