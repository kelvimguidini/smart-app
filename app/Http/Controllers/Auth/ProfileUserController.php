<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

class ProfileUserController extends Controller
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

    /**
     * Display the profile view.
     */
    public function create(Request $request)
    {
        return Inertia::render('Auth/Profile', [
            'user' => $this->userRepository->findWithRoles(Auth::user()->id)
        ]);
    }

    /**
     * Update user profile.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('profile_edit', $request->id)) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
        ]);

        try {
            $this->authService->storeUser($request->all(), $request->id);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }
}
