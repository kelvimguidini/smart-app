<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Mail\PdfEmail;
use App\Models\City;
use App\Models\Event;
use App\Models\EventTransport;
use App\Models\ProviderTransport;
use App\Models\StatusHistory;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class ProviderTransportController extends Controller
{

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if (Gate::allows('admin_provider_transport')) {
            $hotels = ProviderTransport::with('city')->get();
        } else {
            abort(403);
        }

        return Inertia::render('Auth/Auxiliaries/ProviderTransport', [
            'hotels' => $hotels,
            'cities' => City::all()
        ]);
    }


    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        if (!Gate::allows('admin_provider_transport')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {

            if ($request->id > 0) {
                $hotel = ProviderTransport::find($request->id);

                $hotel->name = $request->name;
                $hotel->city_id = $request->city;
                $hotel->contact = $request->contact;
                $hotel->phone = $request->phone;
                $hotel->email = $request->email;
                $hotel->national = $request->national;
                $hotel->iss_percent = $request->iss_percent;
                $hotel->service_percent = $request->service_percent;
                $hotel->iva_percent = $request->iva_percent;

                $hotel->save();
            } else {

                $hotel = ProviderTransport::create([
                    'name' => $request->name,
                    'city_id' => $request->city,
                    'contact' => $request->contact,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'national' => $request->national,
                    'iss_percent' => $request->iss_percent,
                    'service_percent' => $request->service_percent,
                    'iva_percent' => $request->iva_percent
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }


    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeEventProvider(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }

        $request->validate([
            'provider_id' => 'required|integer',
            'event_id' => 'required|integer',
            'currency' => 'required|integer',
        ]);

        try {

            if ($request->id > 0) {

                $history = StatusHistory::with('user')->where('table', "event_transports")
                    ->where('table_id', $request->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($history && ($history->status == "prescribed_by_manager" || $history->status == "sented_to_customer" || $history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'warning']);
                }

                $provider = EventTransport::find($request->id);
                $provider->transport_id = $request->provider_id;

                $provider->event_id = $request->event_id;
                $provider->iss_percent = $request->iss_percent;
                $provider->service_percent = $request->service_percent;
                $provider->iva_percent = $request->iva_percent;
                $provider->currency_id = $request->currency;
                $provider->invoice = $request->invoice;
                $provider->internal_observation = $request->internal_observation;
                $provider->customer_observation = $request->customer_observation;
                $provider->deadline_date = $request->deadline;

                $provider->iof = $request->iof;
                $provider->service_charge = $request->service_charge;

                $provider->save();
            } else {

                $provider = EventTransport::create([
                    'transport_id' => $request->provider_id,
                    'event_id' => $request->event_id,
                    'iss_percent' => $request->iss_percent,
                    'service_percent' => $request->service_percent,
                    'iva_percent' => $request->iva_percent,
                    'currency_id' => $request->currency,
                    'invoice' => $request->invoice,
                    'internal_observation' => $request->internal_observation,
                    'customer_observation' => $request->customer_observation,
                    'iof' => $request->iof,
                    'service_charge' => $request->service_charge,
                    'deadline_date' => $request->deadline
                ]);


                $status = StatusHistory::create([
                    'status' => "created",
                    'user_id' => Auth::user()->id,
                    'table' => "event_transports",
                    'table_id' => $provider->id
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 5, 'ehotel' => $provider->id])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('admin_provider_transport')) {
            abort(403);
        }
        try {
            $history = StatusHistory::with('user')->where('table', "event_transports")
                ->where('table_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($history && ($history->status == "prescribed_by_manager" || $history->status == "sented_to_customer" || $history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'warning']);
            }

            $r = ProviderTransport::find($request->id);

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

        $eventDataBase = Event::with([
            'customer',
            'event_transports.transport' => function ($query) use ($provider) {
                $query->where('id', '=', $provider);
            },
            'event_transports.eventTransportOpts' => function ($query) use ($provider) {
                $query->whereHas('event_transport', function ($query) use ($provider) {
                    $query->where('transport_id', '=', $provider);
                });
            }, 'event_transports.eventTransportOpts.broker', 'event_transports.eventTransportOpts.vehicle', 'event_transports.eventTransportOpts.model', 'event_transports.eventTransportOpts.service', 'event_transports.eventTransportOpts.brand', 'event_transports.currency',

        ])->find($event);

        $providers = collect();

        if ($eventDataBase->event_transports->isNotEmpty()) {
            $providers = $providers->concat($eventDataBase->event_transports->pluck('transport'));
        }

        $providerDataBase = $providers->filter()->unique()->values()->first();

        $arr = array(
            "providerDataBase" => $providerDataBase,
            "eventDataBase" => $eventDataBase
        );

        $pdf = $this->createPDF($arr, 1);
        //return $pdf;
        // Renderize o HTML como PDF
        $pdf->render();
        // Retorna o PDF como um arquivo de download
        if ($request->download == "true") {
            return $pdf->stream('Proposta.pdf');
        } else {

            $sub = "Proposta para transporte terrestre";
            $user = User::find(Auth::user()->id);
            $data = [
                'body' => $request->message != null ? urldecode($request->message) : "",
                'hasAttachment' => true,
                'signature' => $user->signature != null ? $user->signature : "",
                'subject' => $sub
            ];
            $send = Mail::to(explode(";", $request->emails));

            if ($request->copyMe == "true") {
                $send->cc($user->email);
            }

            $send->send(new PdfEmail($pdf->output(), 'Proposta-hotel.pdf', $data, $sub));

            DB::table('email_log')->insert(
                array(
                    'event_id' => $event,
                    'provider_id' => $provider,
                    'sender_id' => $user->id,
                    'body' => urldecode($request->message),
                    'attachment' => $pdf->output(),
                    'to' => $request->emails,
                    'type' => 'proposal'
                )
            );
            // Encontre o registro existente com base no event_id e provider_id
            $eventTrans = EventTransport::where('event_id', $event)->where('transport_id', $provider)->first();

            if ($eventTrans) {
                // Atualize o valor sended_email para true
                $eventTrans->sended_mail = true;
                $eventTrans->update();
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
                    'event' => $paramters['eventDataBase'],
                    'provider' => $paramters['providerDataBase']
                ])->render();

                break;
            default:
                $html = "<div class=\"text-truncate\">Sem Conteudo a ser apresentado</div>";
                break;
        }
        //return $html;
        // Carregue o HTML no Dompdf
        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }
}
