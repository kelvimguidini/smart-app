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

            if ($request->id > 0) {
                $opt = EventHotelOpt::find($request->id);

                $opt->event_hotel_id = $request->event_hotel_id;
                $opt->broker_id = $request->broker;
                $opt->regime_id = $request->regime;
                $opt->purpose_id = $request->purpose;
                $opt->category_hotel_id = $request->hotel_id;
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
                    'category_hotel_id' => $request->hotel_id,
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

            $r = EventHotelOpt::find($request->id);

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
