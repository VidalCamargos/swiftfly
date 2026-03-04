<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Auth\JwtService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly Factory $auth,
        private readonly User $user,
        private readonly JwtService $jwtService,
    ) {
    }

    public function register(RegisterRequest $request)
    {
        return response()->json(
            $this->jwtService->authenticate($this->user->create($request->validated())),
            Response::HTTP_CREATED,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = $this->user->where('email', $credentials['email'])->first();

        if (is_null($user) || ! $this->auth->validate($credentials)) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json($this->jwtService->authenticate($user));
    }

    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
