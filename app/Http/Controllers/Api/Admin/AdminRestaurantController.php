<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class AdminRestaurantController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'approved' => 'nullable|boolean',
            'active'   => 'nullable|boolean',
        ]);

        $query = Restaurant::with('owner');

        if ($request->has('approved')) {
            $query->where('is_approved', filter_var($request->approved, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->paginate(20));
    }

    public function approve(Restaurant $restaurant)
    {
        $restaurant->update(['is_approved' => true]);

        return response()->json(['message' => 'Restaurant approved.', 'restaurant' => $restaurant]);
    }

    public function toggle(Restaurant $restaurant)
    {
        $restaurant->update(['is_active' => !$restaurant->is_active]);

        return response()->json([
            'is_active' => $restaurant->is_active,
            'message'   => $restaurant->is_active ? 'Restaurant activated.' : 'Restaurant deactivated.',
        ]);
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete(); // cascades to menu_items

        return response()->json(['message' => 'Restaurant deleted.']);
    }
}
