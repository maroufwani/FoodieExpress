<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'role'   => 'nullable|in:customer,restaurant_owner,delivery,admin',
            'search' => 'nullable|string|max:100',
        ]);

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => ['required', Password::min(6)],
            'phone'        => 'nullable|string|max:20',
            'role'         => 'required|in:customer,restaurant_owner,delivery,admin',
            'vehicle_type' => 'nullable|in:bicycle,motorcycle,car,scooter',
        ]);

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'email'        => 'sometimes|email|unique:users,email,' . $user->id,
            'phone'        => 'nullable|string|max:20',
            'role'         => 'sometimes|in:customer,restaurant_owner,delivery,admin',
            'is_active'    => 'sometimes|boolean',
            'vehicle_type' => 'nullable|in:bicycle,motorcycle,car,scooter',
        ]);

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
