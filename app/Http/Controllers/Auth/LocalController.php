<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;

class LocalController extends Controller
{
    protected $lookupRepository;

    public function __construct(LookupRepositoryInterface $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('local_admin')) abort(403);
        $this->lookupRepository->activateLocal($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('local_admin')) abort(403);
        $this->lookupRepository->deactivateLocal($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('local_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/Local', [
            'locals' => $this->lookupRepository->getLocalsWithInactive()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('local_admin')) abort(403);

        $request->validate(['name' => 'required|string|max:255']);

        try {
            $this->lookupRepository->saveLocal(['name' => $request->name], $request->id > 0 ? $request->id : null);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('local')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('local_admin')) abort(403);
        $this->lookupRepository->deleteLocal($request->id);
        return redirect()->route('local')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
