<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\EventAdd;
use App\Models\ProviderServices;
use App\Models\StatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ProviderServicesController extends Controller // Atualize a herança
{
    public function activateM($id)
    {
        if (!Gate::allows('admin_provider_service')) {
            abort(403);
        }
        return $this->activate($id, ProviderServices::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('admin_provider_service')) {
            abort(403);
        }
        return $this->deactivate($id, ProviderServices::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if (Gate::allows('event_admin')) {
            $hotels = ProviderServices::with('city')->withoutGlobalScope('active')->get();
        } else {
            abort(403);
        }

        return Inertia::render('Auth/Auxiliaries/ProviderServices', [
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
        if (!Gate::allows('admin_provider_service')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {

            if ($request->id > 0) {
                $hotel = ProviderServices::withoutGlobalScope('active')->find($request->id);

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

                $hotel = ProviderServices::create([
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
            'taxa_4bts' => 'required|numeric|min:0|max:100',
        ]);

        try {

            if ($request->id > 0) {

                $user = User::find(Auth::user()->id);
                if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                    $history = StatusHistory::with('user')->where('table', "event_adds")
                        ->where('table_id', $request->id)
                        ->where('table', 'event_adds')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($history && ($history->status == "dating_with_customer" || $history->status == "Cancelled")) {
                        return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                    }
                }

                $provider = EventAdd::find($request->id);
                $provider->add_id = $request->provider_id;


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
                $provider->taxa_4bts = $request->taxa_4bts;

                $provider->save();
            } else {
                $provider = EventAdd::create([
                    'add_id' => $request->provider_id,
                    'event_id' => $request->event_id,
                    'iss_percent' => $request->iss_percent,
                    'service_percent' => $request->service_percent,
                    'iva_percent' => $request->iva_percent,
                    'currency_id' => $request->currency,
                    'invoice' => $request->invoice,
                    'internal_observation' => $request->internal_observation,
                    'customer_observation' => $request->customer_observation,
                    'iof' => $request->iof,
                    'taxa_4bts' => $request->taxa_4bts,
                    'service_charge' => $request->service_charge,
                    'deadline_date' => $request->deadline
                ]);

                $status = StatusHistory::create([
                    'status' => "created",
                    'user_id' => Auth::user()->id,
                    'table' => "event_adds",
                    'table_id' => $provider->id
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 4, 'ehotel' => $provider->id])->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('admin_provider_service')) {
            abort(403);
        }
        try {

            $r = ProviderServices::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
