<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Models\Apto;
use App\Models\Category;
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

        return Inertia::render('Auth/Hotel', [
            'hotels' => $hotels,
            'cities' =>  Constants::CITIES,
            'categories' => Category::all(),
            'aptos' => Apto::all()
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
}
