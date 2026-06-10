<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\BrokerTransportServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BrokerTransportApiController extends Controller
{
    protected $brokerTransport;

    public function __construct(BrokerTransportServiceInterface $brokerTransport)
    {
        $this->brokerTransport = $brokerTransport;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('broker_trans_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $brokers = $this->brokerTransport->list($filters, $perPage);

        return response()->json($brokers);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('broker_trans_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|max:255|email',
        ]);

        $data = [
            'name' => $request->name,
            'city_id' => $request->city_id,
            'contact' => $request->contact,
            'phone' => $request->phone,
            'email' => $request->email,
            'national' => $request->national,
        ];

        try {
            if ($request->id > 0) {
                $broker = $this->brokerTransport->update($request->id, $data);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $broker]);
            } else {
                $broker = $this->brokerTransport->create($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $broker]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('broker_trans_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->brokerTransport->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('broker_trans_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->brokerTransport->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('broker_trans_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->brokerTransport->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
