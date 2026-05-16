<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Public restaurant listing with location filtering, search, and sort.
     */
    public function index(Request $request)
    {
        $request->validate([
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'cuisine'   => 'nullable|string',
            'search'    => 'nullable|string|max:100',
            'sort'      => 'nullable|in:rating,estimated_delivery_time',
        ]);

        $query = Restaurant::where('is_active', true)->where('is_approved', true);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by cuisine type
        if ($request->filled('cuisine')) {
            $cuisine = $request->string('cuisine')->toString();
            $query->whereJsonContains('cuisine_types', $cuisine);
        }

        // Sort
        $sort = $request->get('sort', 'rating');
        $query->orderBy($sort, $sort === 'rating' ? 'desc' : 'asc');

        $restaurants = $query->get();

        // Filter by delivery radius if location provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;

            $restaurants = $restaurants->filter(
                fn (Restaurant $r) => $r->coversLocation($lat, $lng)
            )->values();
        }

        return response()->json($restaurants);
    }

    /**
     * Public restaurant detail with menu items grouped by category.
     */
    public function show(Restaurant $restaurant)
    {
        if (!$restaurant->is_approved || !$restaurant->is_active) {
            return response()->json(['message' => 'Restaurant not found.'], 404);
        }

        $restaurant->load('menuItems');

        $grouped = $restaurant->menuItems
            ->where('is_available', true)
            ->groupBy('category')
            ->map->values();

        return response()->json([
            'restaurant' => $restaurant,
            'menu'       => $grouped,
        ]);
    }
}
