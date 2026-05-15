<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Auth\Repositories\RoleRepositoryInterface;

class RegisteredUserController extends Controller
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

    public function activateM($id)
    {
        if (!Gate::allows('local_admin')) abort(403);
        $this->authService->setUserStatus($id, true);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('local_admin')) abort(403);
        $this->authService->setUserStatus($id, false);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('user_admin')) abort(403);

        return Inertia::render('Auth/Register', [
            'users' => $this->userRepository->allWithRolesAndInactive(),
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
            $this->authService->storeUser($request->all(), $request->id > 0 ? $request->id : null, (array)$request->roles);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('register')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('user_admin')) abort(403);
        
        try {
            $this->authService->deleteUser($request->id);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    public function roleRemove(Request $request)
    {
        if (!Gate::allows('user_admin')) abort(403);
        
        try {
            $this->authService->removeUserRole($request->user_id, $request->role_id);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
