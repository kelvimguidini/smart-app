<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CRD;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Hotel;
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
            $e = Event::with("crd")
                ->with('customer')
                ->with('hotel_operator')
                ->with('air_operator')
                ->with('land_operator')
                ->get();
        } else if (Gate::allows('hotel_operator') || Gate::allows('land_operator') || Gate::allows('air_operator')) {
            $e = Event::with("crd")
                ->with('customer')
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

        $crds = CRD::all();
        $customers = Customer::all();
        $users = User::all();
        $hotels = Hotel::where('event_id', $event != null ? $event->id : 0)->get();

        return Inertia::render('Auth/Event/EventCreate', [
            'crds' => $crds,
            'customers' => $customers,
            'users' => $users,
            'event' => $event,
            'hotels' => $hotels,
            'tab' => $request->tab
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
            'customer' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'requester' => 'required|string|max:255',
            'sector' => 'required|string|max:255',
            'paxBase' => 'required|string|max:255',
            'cc' => 'required|string|max:255',
            'date' => 'required|string|max:30',
            'crd_id' => 'required|string',
            'hotel_operator' => 'required|string',
            'air_operator' => 'required|string',
            'land_operator' => 'required|string'
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
                $event->crd_id = $request->crd_id;
                $event->hotel_operator = $request->hotel_operator;
                $event->air_operator = $request->air_operator;
                $event->land_operator = $request->land_operator;

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
                    'crd_id' => $request->crd_id,
                    'hotel_operator' => $request->hotel_operator,
                    'air_operator' => $request->air_operator,
                    'land_operator' => $request->land_operator
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
