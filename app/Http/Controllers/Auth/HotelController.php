<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Models\Apto;
use App\Models\Category;
use App\Models\EventHotel;
use App\Models\EventHotelOpt;
use App\Models\Hotel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class HotelController extends Controller
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
            $hotels = Hotel::with('aptos')->with('categories')->get();
        } else if (Gate::allows('hotel_operator')) {
            $hotels = Hotel::with('aptos')->with('categories')->with(['hotel_operator' => function ($query) use ($userId) {
                $query->where('id', '=', $userId);
            }]);
        } else {
            abort(403);
        }

        return Inertia::render('Auth/Auxiliaries/Hotel', [
            'hotels' => $hotels,
            'cities' =>  Constants::CITIES,
            'categories' => Category::all(),
            'aptos' => Apto::all(),
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
                $hotel = Hotel::find($request->id);

                $hotel->name = $request->name;
                $hotel->city = $request->city;
                $hotel->contact = $request->contact;
                $hotel->phone = $request->phone;
                $hotel->email = $request->email;
                $hotel->national = $request->national;

                $hotel->save();

                DB::table('apto_hotel')->where([
                    ['hotel_id', '=', $request->id]
                ])->delete();

                DB::table('category_hotel')->where([
                    ['hotel_id', '=', $request->id]
                ])->delete();
            } else {

                $hotel = Hotel::create([
                    'name' => $request->name,
                    'city' => $request->city,
                    'contact' => $request->contact,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'national' => $request->national
                ]);
            }

            foreach ($request->aptos as $apto) {
                DB::table('apto_hotel')->insert(
                    array(
                        'apto_id' => $apto,
                        'hotel_id' => $hotel->id
                    )
                );
            }

            foreach ($request->categories as $category) {

                DB::table('category_hotel')->insert(
                    array(
                        'category_id' => $category,
                        'hotel_id' => $hotel->id
                    )
                );
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
    public function storeEventHotel(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            abort(403);
        }

        $request->validate([
            'hotel_id' => 'required|integer',
            'event_id' => 'required|integer',
        ]);

        try {

            if ($request->id > 0) {
                $hotel = EventHotel::find($request->id);

                $hotel->hotel_id = $request->hotel_id;
                $hotel->event_id = $request->event_id;
                $hotel->iss_percent = $request->iss_percent;
                $hotel->service_percent = $request->service_percent;
                $hotel->iva_percent = $request->iva_percent;
                $hotel->currency_id = $request->currency;
                $hotel->invoice = $request->invoice;
                $hotel->internal_observation = $request->internal_observation;
                $hotel->customer_observation = $request->customer_observation;

                $hotel->save();
            } else {

                $hotel = EventHotel::create([
                    'hotel_id' => $request->hotel_id,
                    'event_id' => $request->event_id,
                    'iss_percent' => $request->iss_percent,
                    'service_percent' => $request->service_percent,
                    'iva_percent' => $request->iva_percent,
                    'currency_id' => $request->currency,
                    'invoice' => $request->invoice,
                    'internal_observation' => $request->internal_observation,
                    'customer_observation' => $request->customer_observation
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 1, 'ehotel' => $hotel->id])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

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

            $apto_hotel_id = DB::table('apto_hotel')->where([
                ['hotel_id', '=', $request->hotel_id],
                ['apto_id', '=', $request->apto_id]
            ])->select('id')->first()->id;

            $cat_hotel_id = DB::table('category_hotel')->where([
                ['category_id', '=', $request->category_id],
                ['hotel_id', '=', $request->hotel_id]
            ])->select('id')->first()->id;


            if ($request->id > 0) {
                $opt = EventHotelOpt::find($request->id);

                $opt->event_hotel_id = $request->event_hotel_id;
                $opt->broker_id = $request->broker;
                $opt->regime_id = $request->regime;
                $opt->purpose_id = $request->purpose;
                $opt->category_hotel_id = $cat_hotel_id;
                $opt->apto_hotel_id = $apto_hotel_id;
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
                    'category_hotel_id' => $cat_hotel_id,
                    'apto_hotel_id' => $apto_hotel_id,
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

            $r = Hotel::find($request->id);

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
}
