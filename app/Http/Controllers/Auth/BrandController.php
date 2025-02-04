<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class BrandController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('brand_admin')) {
            abort(403);
        }
        return $this->activate($id, Brand::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('brand_admin')) {
            abort(403);
        }
        return $this->deactivate($id, Brand::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('brand_admin')) {
            abort(403);
        }

        $t = Brand::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/Brand', [
            'brands' => $t
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
        if (!Gate::allows('brand_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $brand = Brand::withoutGlobalScope('active')->find($request->id);

                $brand->name = $request->name;
                $brand->save();
            } else {

                $brand = Brand::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('brand')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('brand_admin')) {
            abort(403);
        }
        try {

            $r = Brand::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('brand')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
