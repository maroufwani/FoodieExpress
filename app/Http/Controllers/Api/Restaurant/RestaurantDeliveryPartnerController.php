<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RestaurantDeliveryPartnerController extends Controller
{
    private function restaurant(Request $request)
    {
        return $request->user()->restaurant;
    }

    public function index(Request $request)
    {
        $restaurant = $this->restaurant($request);

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        $partners = $restaurant->deliveryPartners()
            ->orderBy('name')
            ->get();

        return response()->json($partners);
    }

    public function store(Request $request)
    {
        $restaurant = $this->restaurant($request);

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'required|string|max:20',
            'password'     => ['required', 'string', Password::min(6)],
            'vehicle_type' => 'required|in:bicycle,motorcycle,car,scooter',
        ]);

        $partner = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
            'password'      => $data['password'],
            'role'          => 'delivery',
            'vehicle_type'  => $data['vehicle_type'],
            'restaurant_id' => $restaurant->id,
            'is_active'     => true,
            'is_verified'   => true,
        ]);

        return response()->json($partner, 201);
    }

    public function update(Request $request, User $user)
    {
        $restaurant = $this->restaurant($request);

        if (!$restaurant || $user->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'phone'        => 'sometimes|string|max:20',
            'vehicle_type' => 'sometimes|in:bicycle,motorcycle,car,scooter',
            'is_active'    => 'sometimes|boolean',
            'password'     => ['sometimes', 'string', Password::min(6)],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        $restaurant = $this->restaurant($request);

        if (!$restaurant || $user->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Delivery partner removed.']);
    }
}
