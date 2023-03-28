<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Apto;
use App\Models\Broker;
use App\Models\Category;
use App\Models\CRD;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventAB;
use App\Models\EventAdd;
use App\Models\EventHall;
use App\Models\EventHotel;
use App\Models\Frequency;
use App\Models\Local;
use App\Models\Measure;
use App\Models\Provider;
use App\Models\Purpose;
use App\Models\PurposeHall;
use App\Models\Regime;
use App\Models\Service;
use App\Models\ServiceAdd;
use App\Models\ServiceHall;
use App\Models\ServiceType;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function list()
    {
        $userId =  Auth::user()->id;
        if (Gate::allows('event_admin')) {
            $e = Event::with(['crd', 'customer', 'event_hotels.hotel', 'event_abs.ab', 'event_halls.hall', 'event_adds.add'])
                ->with('hotel_operator')
                ->with('air_operator')
                ->with('land_operator')
                ->get();
        } else if (Gate::allows('hotel_operator') || Gate::allows('land_operator') || Gate::allows('air_operator')) {
            $e = Event::with(['crd', 'customer', 'event_hotels.hotel', 'event_abs.ab', 'event_halls.hall', 'event_adds.add'])
                ->with(['hotel_operator' => function ($query) use ($userId) {
                    $query->where('id', '=', $userId);
                }])
                ->with(['air_operator' => function ($query) use ($userId) {
                    $query->where('id', '=', $userId);
                }])
                ->with(['land_operator' => function ($query) use ($userId) {
                    $query->where('id', '=', $userId);
                }])
                ->get();
        } else {
            abort(403);
        }

        return Inertia::render('Auth/Event/EventList', [
            'events' => $e
        ]);
    }


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator') && !Gate::allows('air_operator')) {
            abort(403);
        }

        $event = Event::find($request->id);

        $crds = CRD::with("customer")->get();
        $customers = Customer::all();
        $users = User::all();
        $providers = Provider::get();

        $brokers = Broker::all();
        $currencies = Currency::all();
        $regimes = Regime::all();
        $purposes = Purpose::all();

        $services = Service::all();
        $servicesType = ServiceType::all();
        $locals = Local::all();


        $servicesHall = ServiceHall::all();
        $purposesHall = PurposeHall::all();

        $servicesAdd = ServiceAdd::all();
        $frequencies = Frequency::all();
        $measures = Measure::all();

        $users = User::all();

        $eventHotel = $request->tab == 1 ? EventHotel::with(['eventHotelsOpt', 'hotel', 'currency', 'event'])->find($request->ehotel) : null;
        $eventHotels = EventHotel::with(['eventHotelsOpt.broker', 'eventHotelsOpt.regime', 'eventHotelsOpt.purpose', 'eventHotelsOpt.apto_hotel', 'eventHotelsOpt.category_hotel', 'hotel', 'currency', 'event'])->where('event_id', '=', $request->id)->get();

        $eventAB = $request->tab == 2 ? EventAB::with(['eventAbOpts', 'ab', 'currency', 'event'])->find($request->ehotel) : null;
        $eventABs = EventAB::with(['eventAbOpts.broker', 'eventAbOpts.service', 'eventAbOpts.service_type', 'eventAbOpts.local', 'ab', 'currency', 'event'])->where('event_id', '=', $request->id)->get();

        $eventHall = $request->tab == 3 ? EventHall::with(['eventHallOpts', 'hall', 'currency', 'event'])->find($request->ehotel) : null;
        $eventHalls = EventHall::with(['eventHallOpts.broker', 'eventHallOpts.service', 'eventHallOpts.purpose', 'hall', 'currency', 'event'])->where('event_id', '=', $request->id)->get();

        $eventAdd = $request->tab == 4 ? EventAdd::with(['eventAddOpts', 'add', 'currency', 'event'])->find($request->ehotel) : null;
        $eventAdds = EventAdd::with(['eventAddOpts', 'eventAddOpts.frequency', 'eventAddOpts.measure', 'eventAddOpts.service', 'add', 'currency', 'event'])->where('event_id', '=', $request->id)->get();

        return Inertia::render('Auth/Event/EventCreate', [
            'crds' => $crds,
            'customers' => $customers,
            'users' => $users,
            'event' => $event,
            'providers' => $providers,
            'tab' => $request->tab,
            'brokers' => $brokers,
            'currencies' => $currencies,
            'regimes' => $regimes,
            'purposes' => $purposes,
            'eventHotels' => $eventHotels,
            'eventHotel' => $eventHotel,
            'eventAB' => $eventAB,
            'eventABs' => $eventABs,
            'services' => $services,
            'servicesType' => $servicesType,
            'locals' => $locals,
            'servicesHall' => $servicesHall,
            'purposesHall' => $purposesHall,
            'eventHall' => $eventHall,
            'eventHalls' => $eventHalls,
            'servicesAdd' => $servicesAdd,
            'frequencies' => $frequencies,
            'measures' => $measures,
            'eventAdd' => $eventAdd,
            'eventAdds' => $eventAdds,
            'catsHotel' => Category::all(),
            'aptosHotel' => Apto::all(),
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
        if (!Gate::allows('event_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'customer' => 'required|numeric',
            'code' => 'required|string|max:255',
            'requester' => 'required|string|max:255',
            'sector' => 'required|string|max:255',
            'paxBase' => 'required|string|max:255',
            'cc' => 'required|string|max:255',
            'date' => 'required|date',
            'date_final' => 'required|date|after_or_equal:in',
            'crd_id' => 'required|numeric',
            'hotel_operator' => 'required|numeric',
            'air_operator' => 'required|numeric',
            'land_operator' => 'required|numeric'
        ]);

        try {

            if ($request->id > 0) {

                $event = Event::find($request->id);

                $event->name = $request->name;
                $event->customer_id = $request->customer;
                $event->code = $request->code;
                $event->requester = $request->requester;
                $event->sector = $request->sector;
                $event->pax_base = $request->paxBase;
                $event->cost_center = $request->cc;
                $event->date = $request->date;
                $event->date_final = $request->date_final;
                $event->crd_id = $request->crd_id;
                $event->hotel_operator = $request->hotel_operator;
                $event->air_operator = $request->air_operator;
                $event->land_operator = $request->land_operator;
                $event->iof = $request->iof;
                $event->service_charge = $request->service_charge;

                $event->save();
            } else {

                $event = Event::create([
                    'name' => $request->name,
                    'customer_id' => $request->customer,
                    'code' => $request->code,
                    'requester' => $request->requester,
                    'sector' => $request->sector,
                    'pax_base' => $request->paxBase,
                    'cost_center' => $request->cc,
                    'date' => $request->date,
                    'date_final' => $request->date_final,
                    'crd_id' => $request->crd_id,
                    'hotel_operator' => $request->hotel_operator,
                    'air_operator' => $request->air_operator,
                    'land_operator' => $request->land_operator,
                    'iof' => $request->iof,
                    'service_charge' => $request->service_charge
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $event->id, 'tab' => 1])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('event_admin')) {
            abort(403);
        }
        try {

            $r = Event::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
