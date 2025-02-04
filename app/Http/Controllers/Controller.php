<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function activate($id, $model)
    {
        $record = $model::withoutGlobalScope('active')->findOrFail($id);
        $record->activate();

        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivate($id, $model)
    {
        $record = $model::withoutGlobalScope('active')->findOrFail($id);
        $record->deactivate();

        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }
}
