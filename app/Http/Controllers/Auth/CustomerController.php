<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if (!Gate::allows('customer_admin')) {
            abort(403);
        }

        $t = Customer::all();
        return Inertia::render('Auth/Customer', [
            'customers' => $t
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
        if (!Gate::allows('customer_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        try {
            $url = false;
            if ($request->file('logo')) {
                $path = Storage::putFile('public/logos', $request->file('logo'));
                $url = Storage::url($path);
            }

            if ($request->id > 0) {

                $customer = Customer::find($request->id);

                $customer->name = $request->name;
                $customer->logo = $url ?  $url : $request->logo;
                $customer->save();
            } else {

                $customer = Customer::create([
                    'name' => $request->name,
                    'logo' => $url,
                ]);
            }
        } catch (Exception $e) {
            Storage::delete($path);
            throw $e;
        }
        return redirect()->route('customer')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('customer_admin')) {
            abort(403);
        }
        try {

            $r = Customer::find($request->id);

            $r->delete();

            Storage::delete($r->logo);
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('customer')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}