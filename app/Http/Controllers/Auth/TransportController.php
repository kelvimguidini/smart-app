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
            'count' => 'required|integer'
        ]);

        try {

            if ($request->id > 0) {
                $history = StatusHistory::with('user')->where('table', 'event_transports')
                    ->where('table_id', $request->event_transport_id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($history && ($history->status == "prescribed_by_manager" || $history->status == "sented_to_customer" || $history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'warning']);
                }

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
                $opt->compare_trivago = $request->compare_trivago;
                $opt->compare_website_htl = $request->compare_website_htl;
                $opt->compare_omnibess = $request->compare_omnibess;
                $opt->count = $request->count;

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
    public function eventTransportDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('transport_operator')) {
            abort(403);
        }
        try {

            $history = StatusHistory::with('user')->where('table', 'event_transports')
                ->where('table_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($history && ($history->status == "prescribed_by_manager" || $history->status == "sented_to_customer" || $history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'warning']);
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

            $r = EventTransportOpt::find($request->id);

            $history = StatusHistory::with('user')->where('table', 'event_transports')
                ->where('table_id', $r->event_transport_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($history && ($history->status == "prescribed_by_manager" || $history->status == "sented_to_customer" || $history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'warning']);
            }

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    public function proposalPdf(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator')) {
            abort(403);
        }

        $provider = $request->provider_id;
        $event = $request->event_id;

        $eventBank = Event::with([
            'customer',
            'event_hotels.hotel' => function ($query) use ($provider) {
                $query->where('id', '=', $provider);
            },
            'event_abs.ab' => function ($query) use ($provider) {
                $query->where('id', '=', $provider);
            },
            'event_halls.hall' => function ($query) use ($provider) {
                $query->where('id', '=', $provider);
            },
            'event_adds.add' => function ($query) use ($provider) {
                $query->where('id', '=', $provider);
            },

            'event_hotels.eventHotelsOpt' => function ($query) use ($provider) {
                $query->whereHas('event_hotel', function ($query) use ($provider) {
                    $query->where('hotel_id', '=', $provider);
                });
            }, 'event_hotels.eventHotelsOpt.regime', 'event_hotels.eventHotelsOpt.apto_hotel', 'event_hotels.eventHotelsOpt.category_hotel', 'event_hotels.currency',
            'event_abs.eventAbOpts' => function ($query) use ($provider) {
                $query->whereHas('event_ab', function ($query) use ($provider) {
                    $query->where('ab_id', '=', $provider);
                });
            }, 'event_abs.eventAbOpts.Local', 'event_abs.eventAbOpts.service_type', 'event_abs.currency',
            'event_halls.eventHallOpts' => function ($query) use ($provider) {
                $query->whereHas('event_hall', function ($query) use ($provider) {
                    $query->where('hall_id', '=', $provider);
                });
            }, 'event_halls.eventHallOpts.purpose', 'event_halls.currency',
            'event_adds.eventAddOpts' => function ($query) use ($provider) {
                $query->whereHas('event_add', function ($query) use ($provider) {
                    $query->where('add_id', '=', $provider);
                });
            }, 'event_adds.eventAddOpts.service', 'event_adds.eventAddOpts.measure', 'event_adds.eventAddOpts.frequency', 'event_adds.currency',
        ])->find($event);

        $providers = collect();

        if ($eventBank->event_hotels->isNotEmpty()) {
            $providers = $providers->concat($eventBank->event_hotels->pluck('hotel'));
        }

        if ($eventBank->event_abs->isNotEmpty()) {
            $providers = $providers->concat($eventBank->event_abs->pluck('ab'));
        }

        if ($eventBank->event_halls->isNotEmpty()) {
            $providers = $providers->concat($eventBank->event_halls->pluck('hall'));
        }

        if ($eventBank->event_adds->isNotEmpty()) {
            $providers = $providers->concat($eventBank->event_adds->pluck('add'));
        }

        $providerBank = $providers->filter()->unique()->values()->first();

        $arr = array(
            "providerBank" => $providerBank,
            "eventBank" => $eventBank
        );

        $pdf = $this->createPDF($arr, 1);
        // return $pdf;
        // Renderize o HTML como PDF
        $pdf->render();
        // Retorna o PDF como um arquivo de download
        if ($request->download == "true") {
            return $pdf->stream('Proposta-hotel.pdf');
        } else {

            $user = User::find(Auth::user()->id);
            $data = [
                'body' => $request->message != null ? urldecode($request->message) : "",
                'hasAttachment' => true,
                'signature' => $user->signature != null ? $user->signature : "",
                'subject' => "Proposta de hotel"
            ];
            $send = Mail::to(explode(";", $request->emails));

            if ($request->copyMe == "true") {
                $send->cc($user->email);
            }

            $send->send(new PdfEmail($pdf->output(), 'Proposta-hotel.pdf', $data, "Proposta de hotel"));

            DB::table('email_log')->insert(
                array(
                    'event_id' => $event,
                    'provider_id' => $provider,
                    'sender_id' => $user->id,
                    'body' => urldecode($request->message),
                    'attachment' => $pdf->output(),
                    'to' => explode(";", $request->emails),
                    'type' => 'proposal'
                )
            );

            // Encontre o registro existente com base no event_id e provider_id
            $eventHotel = EventHotel::where('event_id', $event)->where('hotel_id', $provider)->first();

            if ($eventHotel) {
                // Atualize o valor sended_email para true
                $eventHotel->sended_mail = true;
                $eventHotel->update();
            }
            // Encontre o registro existente com base no event_id e provider_id
            $eventAb = EventAB::where('event_id', $event)->where('ab_id', $provider)->first();

            if ($eventAb) {
                // Atualize o valor sended_email para true
                $eventAb->sended_mail = true;
                $eventAb->update();
            }
            // Encontre o registro existente com base no event_id e provider_id
            $eventHall = EventHall::where('event_id', $event)->where('hall_id', $provider)->first();

            if ($eventHall) {
                // Atualize o valor sended_email para true
                $eventHall->sended_mail = true;
                $eventHall->update();
            }
            // Encontre o registro existente com base no event_id e provider_id
            $eventAdd = EventAdd::where('event_id', $event)->where('add_id', $provider)->first();

            if ($eventAdd) {
                // Atualize o valor sended_email para true
                $eventAdd->sended_mail = true;
                $eventAdd->update();
            }

            return redirect()->back()->with('flash', ['message' => 'E-mail enviado com sucesso!', 'type' => 'success']);
        }
    }

    private function createPDF(array $paramters, int $type)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $path = base_path('public');

        $options->set('chroot', $path);

        $pdf = new Dompdf($options);

        $html = "";
        switch ($type) {
            case 1:
                $html = view('proposalPdf', [
                    'event' => $paramters['eventBank'],
                    'provider' => $paramters['providerBank']
                ])->render();

                break;
            default:
                $html = "<div class=\"text-truncate\">Sem Conteudo a ser apresentado</div>";
                break;
        }
        // return $html;
        // Carregue o HTML no Dompdf
        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }
}
