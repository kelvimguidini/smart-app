<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventHotel;
use App\Models\EventHotelOpt;
use App\Models\Provider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\ResponseFactory;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

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


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function budget(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator')) {
            abort(403);
        }
        $provider = $request->provider_id;
        $event = $request->event_id;

        $eventBank = Event::with('customer')->with([
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
            },
            'event_abs.eventAbOpts' => function ($query) use ($provider) {
                $query->whereHas('event_ab', function ($query) use ($provider) {
                    $query->where('ab_id', '=', $provider);
                });
            },
            'event_halls.eventHallOpts' => function ($query) use ($provider) {
                $query->whereHas('event_hall', function ($query) use ($provider) {
                    $query->where('hall_id', '=', $provider);
                });
            },
            'event_adds.eventAddOpts' => function ($query) use ($provider) {
                $query->whereHas('event_add', function ($query) use ($provider) {
                    $query->where('add_id', '=', $provider);
                });
            },
        ])->find($event);

        $providers = collect();

        if ($event->event_hotels->isNotEmpty()) {
            $providers = $providers->concat($event->event_hotels->pluck('hotel'));
        }

        if ($event->event_abs->isNotEmpty()) {
            $providers = $providers->concat($event->event_abs->pluck('ab'));
        }

        if ($event->event_halls->isNotEmpty()) {
            $providers = $providers->concat($event->event_halls->pluck('hall'));
        }

        if ($event->event_adds->isNotEmpty()) {
            $providers = $providers->concat($event->event_adds->pluck('add'));
        }

        $providerBank = $providers->filter()->unique()->values()->first();

        $parameters = compact('event', 'provider');
        $parametersCripts = Crypt::encryptString(json_encode($parameters));
        $url = route('new-event', ['params' => $parametersCripts]);


        return Inertia::render('Auth/Event/Budget', [
            'event' => $eventBank,
            'provider' => $providerBank,
            'link' => $url
        ]);
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
            }, 'event_hotels.eventHotelsOpt.regime', 'event_hotels.eventHotelsOpt.apto_hotel', 'event_hotels.eventHotelsOpt.category_hotel',
            'event_abs.eventAbOpts' => function ($query) use ($provider) {
                $query->whereHas('event_ab', function ($query) use ($provider) {
                    $query->where('ab_id', '=', $provider);
                });
            },
            'event_halls.eventHallOpts' => function ($query) use ($provider) {
                $query->whereHas('event_hall', function ($query) use ($provider) {
                    $query->where('hall_id', '=', $provider);
                });
            },
            'event_adds.eventAddOpts' => function ($query) use ($provider) {
                $query->whereHas('event_add', function ($query) use ($provider) {
                    $query->where('add_id', '=', $provider);
                });
            },
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


        // $html = (new Response($response->content()))->getContent();
        return Inertia::render('Auth/Event/ProposalPdf', [
            'event' => $eventBank,
            'provider' => $providerBank
        ]);


        // $pdf = PDF::loadView('hotel-budget', null);

        // return $pdf->download('orcamento-hotel.pdf');
    }
}
