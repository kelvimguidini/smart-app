<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Domains\Auth\Repositories\RoleRepositoryInterface;

class RoleController extends Controller
{
    protected $authService;
    protected $roleRepository;

    public function __construct(
        AuthServiceInterface $authService,
        RoleRepositoryInterface $roleRepository
    ) {
        $this->authService = $authService;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display the registration view.
     */
    public function create()
    {
        if (!Gate::allows('role_admin')) abort(403);

        return Inertia::render('Auth/Role', [
            'roles' => $this->roleRepository->allWithPermissions(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('role_admin')) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required',
        ]);

        try {
            $this->authService->storeRole(
                $request->only(['name', 'active']), 
                $request->id > 0 ? $request->id : null,
                (array)$request->permissions
            );
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('role')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Delete role.
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('role_admin')) abort(403);

        try {
            $this->roleRepository->delete($request->id);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('role')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    /**
     * Remove specific permission.
     */
    public function permissionRemove(Request $request)
    {
        if (!Gate::allows('role_admin')) abort(403);

        try {
            $this->roleRepository->removePermission($request->role_id, $request->permission_id);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('role')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
