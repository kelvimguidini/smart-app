<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\RoleServiceInterface;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;

class RoleApiController extends Controller
{
    protected RoleServiceInterface $roleService;

    public function __construct(RoleServiceInterface $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Obter lista de roles e permissões disponíveis (formato JSON paginado)
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (!Gate::allows('role_admin')) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $params = $request->only(['page', 'per_page', 'search', 'sort_column', 'sort_direction', 'active']);
        
        $roles = $this->roleService->getPaginatedRoles($params, $params['per_page'] ?? 10);
        $permissionList = Permission::all();

        return response()->json([
            'roles' => $roles,
            'permissionList' => $permissionList,
        ], 200);
    }

    /**
     * Salvar nova role ou atualizar existente
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        if (!Gate::allows('role_admin')) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|exists:permission,name',
            'id' => 'integer|min:0',
            'active' => 'boolean'
        ]);

        try {
            $role = $this->roleService->saveRole($validated);

            return response()->json([
                'message' => 'Grupo de acesso salvo com sucesso',
                'role' => $role
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao salvar grupo de acesso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar role
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        if (!Gate::allows('role_admin')) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'id' => 'required|integer|exists:roles,id'
        ]);

        try {
            $this->roleService->deleteRole($validated['id']);

            return response()->json([
                'message' => 'Grupo de acesso deletado com sucesso'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar grupo de acesso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover permissão de uma role
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removePermission(Request $request): JsonResponse
    {
        if (!Gate::allows('role_admin')) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'permission_id' => 'required|integer|exists:permission,id'
        ]);

        try {
            $this->roleService->removePermission($validated['role_id'], $validated['permission_id']);

            return response()->json([
                'message' => 'Permissão removida com sucesso'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao remover permissão',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
