<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('category_admin')) {
            abort(403);
        }

        return $this->activate($id, Category::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('category_admin')) {
            abort(403);
        }

        return $this->deactivate($id, Category::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('category_admin')) {
            abort(403);
        }

        $t = Category::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/Category', [
            'categories' => $t
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
        if (!Gate::allows('category_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $Category = Category::withoutGlobalScope('active')->find($request->id);

                $Category->name = $request->name;
                $Category->save();
            } else {

                $Category = Category::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('category')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('category_admin')) {
            abort(403);
        }
        try {

            $r = Category::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('category')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
