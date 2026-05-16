<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDeliveryPartnerController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'verified'   => 'nullable|boolean',
            'available'  => 'nullable|boolean',
        ]);

        $query = User::where('role', 'delivery');

        if ($request->has('verified')) {
            $query->where('is_verified', filter_var($request->verified, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('available')) {
            $query->where('is_available', filter_var($request->available, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->paginate(20));
    }

    public function verify(User $user)
    {
        if ($user->role !== 'delivery') {
            return response()->json(['message' => 'User is not a delivery partner.'], 422);
        }

        $user->update(['is_verified' => true]);

        return response()->json(['message' => 'Delivery partner verified.', 'user' => $user]);
    }

    public function toggleAvailability(User $user)
    {
        if ($user->role !== 'delivery') {
            return response()->json(['message' => 'User is not a delivery partner.'], 422);
        }

        $user->update(['is_available' => !$user->is_available]);

        return response()->json([
            'is_available' => $user->is_available,
            'message'      => $user->is_available ? 'Partner set to available.' : 'Partner set to unavailable.',
        ]);
    }
}
