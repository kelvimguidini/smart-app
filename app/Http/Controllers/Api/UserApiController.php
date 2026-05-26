<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Auth\Repositories\RoleRepositoryInterface;
use Exception;

class UserApiController extends Controller
{
    protected $authService;
    protected $userRepository;
    protected $roleRepository;

    public function __construct(
        AuthServiceInterface $authService,
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository
    ) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('user_admin')) abort(403);
        
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $sortColumn = $request->input('sort_column', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        return response()->json([
            'users' => $this->userRepository->paginateWithRolesAndInactive($perPage, $search, $sortColumn, $sortDirection),
            'roles' => $this->roleRepository->allWithPermissions(),
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('user_admin')) abort(403);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255' . ($request->id > 0 ? '' : '|unique:users'),
            'phone' => 'nullable|string|max:255'
        ];
        $request->validate($rules);

        try {
            $user = $this->authService->storeUser($request->all(), $request->id > 0 ? $request->id : null, (array)$request->roles);
            return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $user], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('user_admin')) abort(403);
        
        try {
            $this->authService->deleteUser($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function roleRemove(Request $request)
    {
        if (!Gate::allows('user_admin')) abort(403);
        
        $request->validate([
            'user_id' => 'required|integer',
            'role_id' => 'required|integer'
        ]);

        try {
            $this->authService->removeUserRole($request->user_id, $request->role_id);
            return response()->json(['message' => 'Grupo de acesso removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('local_admin')) abort(403);
        
        try {
            $this->authService->setUserStatus($id, true);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('local_admin')) abort(403);
        
        try {
            $this->authService->setUserStatus($id, false);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
