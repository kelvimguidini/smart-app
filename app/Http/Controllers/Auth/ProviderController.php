<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Mail\PdfEmail;
use App\Models\Event;
use App\Models\EventAB;
use App\Models\EventHall;
use App\Models\EventHotel;
use App\Models\Provider;
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

class ProviderController extends Controller
{

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        $userId =  Auth::user()->id;
        if (Gate::allows('admin_provider')) {
            $hotels = Provider::get();
        } else {
            abort(403);
        }

        return Inertia::render('Auth/Auxiliaries/Provider', [
            'hotels' => $hotels,
            'cities' =>  Constants::CITIES,
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
        if (!Gate::allows('admin_provider')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|max:255|email',
        ]);

        try {

            if ($request->id > 0) {
                $hotel = Provider::find($request->id);

                $hotel->name = $request->name;
                $hotel->city = $request->city;
                $hotel->contact = $request->contact;
                $hotel->phone = $request->phone;
                $hotel->email = $request->email;
                $hotel->national = $request->national;
                $hotel->iss_percent = $request->iss_percent;
                $hotel->service_percent = $request->service_percent;
                $hotel->iva_percent = $request->iva_percent;

                $hotel->save();
            } else {

                $hotel = Provider::create([
                    'name' => $request->name,
                    'city' => $request->city,
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
                switch ($request->type) {
                    case 'hotel':
                        $provider = EventHotel::find($request->id);
                        $provider->hotel_id = $request->provider_id;
                        break;
                    case 'ab':
                        $provider = EventAB::find($request->id);
                        $provider->ab_id = $request->provider_id;
                        break;
                    case 'hall':
                        $provider = EventHall::find($request->id);
                        $provider->hall_id = $request->provider_id;
                        break;
                }

                $provider->event_id = $request->event_id;
                $provider->iss_percent = $request->iss_percent;
                $provider->service_percent = $request->service_percent;
                $provider->iva_percent = $request->iva_percent;
                $provider->currency_id = $request->currency;
                $provider->invoice = $request->invoice;
                $provider->internal_observation = $request->internal_observation;
                $provider->customer_observation = $request->customer_observation;

                $provider->iof = $request->iof;
                $provider->service_charge = $request->service_charge;

                $provider->save();
            } else {

                switch ($request->type) {
                    case 'hotel':
                        $provider = EventHotel::create([
                            'hotel_id' => $request->provider_id,
                            'event_id' => $request->event_id,
                            'iss_percent' => $request->iss_percent,
                            'service_percent' => $request->service_percent,
                            'iva_percent' => $request->iva_percent,
                            'currency_id' => $request->currency,
                            'invoice' => $request->invoice,
                            'internal_observation' => $request->internal_observation,
                            'customer_observation' => $request->customer_observation,
                            'iof' => $request->iof,
                            'service_charge' => $request->service_charge
                        ]);
                        break;
                    case 'ab':
                        $provider = EventAB::create([
                            'ab_id' => $request->provider_id,
                            'event_id' => $request->event_id,
                            'iss_percent' => $request->iss_percent,
                            'service_percent' => $request->service_percent,
                            'iva_percent' => $request->iva_percent,
                            'currency_id' => $request->currency,
                            'invoice' => $request->invoice,
                            'internal_observation' => $request->internal_observation,
                            'customer_observation' => $request->customer_observation,
                            'iof' => $request->iof,
                            'service_charge' => $request->service_charge
                        ]);
                        break;
                    case 'hall':
                        $provider = EventHall::create([
                            'hall_id' => $request->provider_id,
                            'event_id' => $request->event_id,
                            'iss_percent' => $request->iss_percent,
                            'service_percent' => $request->service_percent,
                            'iva_percent' => $request->iva_percent,
                            'currency_id' => $request->currency,
                            'invoice' => $request->invoice,
                            'internal_observation' => $request->internal_observation,
                            'customer_observation' => $request->customer_observation,
                            'iof' => $request->iof,
                            'service_charge' => $request->service_charge
                        ]);
                        break;
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
        $tab = 0;
        switch ($request->type) {
            case 'hotel':
                $tab = 1;
                break;
            case 'ab':
                $tab = 2;
                break;
            case 'hall':
                $tab = 3;
                break;
        }

        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => $tab, 'ehotel' => $provider->id])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('admin_provider')) {
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

        $providerBank = $providers->filter()->unique()->values()->first();

        $arr = array(
            "providerBank" => $providerBank,
            "eventBank" => $eventBank
        );

        $pdf = $this->createPDF($arr, 1);
        //return $pdf;
        // Renderize o HTML como PDF
        $pdf->render();
        // Retorna o PDF como um arquivo de download
        if ($request->download == "true") {
            return $pdf->stream('Proposta.pdf');
        } else {

            $sub = "Proposta para hotel";
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

            return redirect()->back()->with('flash', ['message' => 'E-mail enviado com sucesso!', 'type' => 'success']);
        }
    }

    private function createPDF(array $paramters, int $type)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $path = base_path('public');

        $options->set('chroot', $path);
        //$options->set('debugCss', true);

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
        //return $html;
        // Carregue o HTML no Dompdf
        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }
}
