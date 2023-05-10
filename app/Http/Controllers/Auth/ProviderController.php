<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Models\Apto;
use App\Models\Category;
use App\Models\EventAB;
use App\Models\EventAdd;
use App\Models\EventHall;
use App\Models\EventHotel;
use App\Models\Provider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

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
        if (Gate::allows('event_admin')) {
            $hotels = Provider::get();
        } else if (Gate::allows('hotel_operator')) {
            $hotels = Provider::with('aptos')->with('categories')->with(['hotel_operator' => function ($query) use ($userId) {
                $query->where('id', '=', $userId);
            }]);
        } else {
            abort(403);
        }

        return Inertia::render('Auth/Auxiliaries/Hotel', [
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
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
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
                    case 'add':
                        $provider = EventAdd::find($request->id);
                        $provider->add_id = $request->provider_id;
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
                            'customer_observation' => $request->customer_observation
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
                            'customer_observation' => $request->customer_observation
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
                            'customer_observation' => $request->customer_observation
                        ]);
                        break;
                    case 'add':
                        $provider = EventAdd::create([
                            'add_id' => $request->provider_id,
                            'event_id' => $request->event_id,
                            'iss_percent' => $request->iss_percent,
                            'service_percent' => $request->service_percent,
                            'iva_percent' => $request->iva_percent,
                            'currency_id' => $request->currency,
                            'invoice' => $request->invoice,
                            'internal_observation' => $request->internal_observation,
                            'customer_observation' => $request->customer_observation
                        ]);
                        break;
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
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
            case 'add':
                $tab = 4;
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
}
