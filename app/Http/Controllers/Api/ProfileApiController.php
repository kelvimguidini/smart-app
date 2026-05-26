<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use Exception;

class ProfileApiController extends Controller
{
    protected $authService;
    protected $userRepository;

    public function __construct(
        AuthServiceInterface $authService,
        UserRepositoryInterface $userRepository
    ) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    public function show(Request $request)
    {
        return response()->json([
            'user' => $this->userRepository->findWithRoles(Auth::id())
        ]);
    }

    public function store(Request $request)
    {
        $id = $request->id ?? Auth::id();
        
        if (!Gate::allows('profile_edit', $id)) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
        ]);

        try {
            $user = $this->authService->storeUser($request->all(), $id);
            return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $user]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
