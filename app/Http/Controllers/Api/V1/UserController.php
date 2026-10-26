<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::with('producer');
        if ($role = $request->role) {
            $query->where('role', $role);
        }
        if ($search = $request->search) {
            $like = '%'.strtolower($search).'%';
            $query->where(fn ($q) => $q
                ->where(DB::raw('LOWER(name)'), 'like', $like)
                ->orWhere(DB::raw('LOWER(lastname)'), 'like', $like)
                ->orWhere(DB::raw('LOWER(email)'), 'like', $like)
                ->orWhere('dni', 'like', "%{$search}%"));
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        return UserResource::collection($query->orderBy('name')->paginate($perPage)->withQueryString());
    }

    public function store(StoreUserRequest $request, UserService $userService)
    {
        $user = $userService->create([...$request->validated(), 'active' => $request->boolean('active', true)]);

        return (new UserResource($user->load('producer')))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user->update([...$request->validated(), 'active' => $request->boolean('active', $user->active)]);

        return new UserResource($user->fresh('producer'));
    }

    /**
     * Activar / desactivar la cuenta. Un administrador no puede desactivarse a sí mismo.
     */
    public function updateStatus(Request $request, User $user): UserResource
    {
        $data = $request->validate(['active' => 'required|boolean']);
        if (! $data['active'] && $user->is($request->user())) {
            abort(422, 'No puede desactivar su propia cuenta.');
        }
        $user->update(['active' => $data['active']]);

        return new UserResource($user->fresh('producer'));
    }
}
