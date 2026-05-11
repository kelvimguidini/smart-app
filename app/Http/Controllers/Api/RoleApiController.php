<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;

class RoleApiController extends Controller
{
    /**
     * Obter lista de roles e permissões disponíveis (formato JSON)
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        if (!Gate::allows('role_admin')) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        try {
            $roles = Role::with('permissions')->get();
            $permissionList = Permission::all();

            return response()->json([
                'roles' => $roles,
                'permissionList' => $permissionList,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar grupos de acesso',
                'error' => $e->getMessage()
            ], 500);
        }
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

        // Validação
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|exists:permission,name',
            'id' => 'integer|min:0'
        ]);

        try {
            $roleId = $request->input('id', 0);

            if ($roleId > 0) {
                // Atualizar role existente
                $role = Role::findOrFail($roleId);
                $role->name = $validated['name'];
                $role->active = $request->input('active', true);
                $role->save();
            } else {
                // Criar nova role
                $role = Role::create([
                    'name' => $validated['name'],
                    'active' => true
                ]);
            }

            // Remover permissões antigas
            DB::table('role_permission')
                ->where('role_id', $role->id)
                ->delete();

            // Adicionar novas permissões
            foreach ($validated['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    DB::table('role_permission')->insert([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id
                    ]);
                }
            }

            return response()->json([
                'message' => 'Grupo de acesso salvo com sucesso',
                'role' => $role->load('permissions')
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
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
            'id' => 'required|integer|exists:role,id'
        ]);

        try {
            $role = Role::findOrFail($validated['id']);
            $role->delete();

            return response()->json([
                'message' => 'Grupo de acesso deletado com sucesso'
            ], 200);
        } catch (Exception $e) {
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
            'role_id' => 'required|integer|exists:role,id',
            'permission_id' => 'required|integer|exists:permission,id'
        ]);

        try {
            DB::table('role_permission')
                ->where('role_id', $validated['role_id'])
                ->where('permission_id', $validated['permission_id'])
                ->delete();

            return response()->json([
                'message' => 'Permissão removida com sucesso'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao remover permissão',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
