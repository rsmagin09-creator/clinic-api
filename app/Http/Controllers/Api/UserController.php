<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    /** GET /users?role= */
    public function index(Request $request)
    {
        $request->validate(['role' => ['nullable', Rule::enum(UserRole::class)]],
            ['role.enum' => 'Недопустимая роль']);

        return UserResource::collection($this->service->list($request->query('role')));
    }

    /** GET /users/{user} */
    public function show(User $user)
    {
        return new UserResource($user->load('doctor')->loadCount('appointments'));
    }

    /** PATCH /users/{user}/role */
    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        return new UserResource($this->service->updateRole($user, $request->validated('role')));
    }

    /** DELETE /users/{user} */
    public function destroy(User $user)
    {
        $this->service->delete($user);

        return response()->noContent();
    }
}
