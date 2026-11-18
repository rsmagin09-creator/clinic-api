<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /** POST /api/v1/register */
    public function register(RegisterRequest $request)
    {
        [$user, $token] = $this->service->register($request->validated());

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /** POST /api/v1/login */
    public function login(LoginRequest $request)
    {
        $result = $this->service->login($request->validated('email'), $request->validated('password'));

        if (! $result) {
            return response()->json(['message' => 'Неверный email или пароль'], 401);
        }

        [$user, $token] = $result;

        return response()->json([
            'user' => new UserResource($user->load('doctor')),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in_minutes' => config('sanctum.expiration'),
        ]);
    }

    /** POST /api/v1/logout */
    public function logout(Request $request)
    {
        $this->service->logout($request->user());

        return response()->noContent();
    }

    /** GET /api/v1/user */
    public function me(Request $request)
    {
        return new UserResource($request->user()->load('doctor'));
    }
}
