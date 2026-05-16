<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        return response()->json($restaurant->menuItems()->orderBy('category')->get());
    }

    public function store(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'category'         => 'required|string|max:100',
            'image'            => 'nullable|image|max:2048',
            'is_vegetarian'    => 'boolean',
            'is_vegan'         => 'boolean',
            'is_gluten_free'   => 'boolean',
            'spice_level'      => 'in:none,mild,medium,hot,extra_hot',
            'preparation_time' => 'integer|min:1',
            'is_available'     => 'boolean',
        ]);

        if ($request->has('sizes')) {
            $sizes = json_decode($request->input('sizes'), true);
            $data['sizes'] = is_array($sizes) ? $sizes : [];
        }
        if ($request->has('sizes_heading')) {
            $data['sizes_heading'] = substr(strip_tags($request->input('sizes_heading')), 0, 100) ?: 'Size';
        }
        if ($request->has('option_groups')) {
            $groups = json_decode($request->input('option_groups'), true);
            $data['option_groups'] = is_array($groups) ? $groups : null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu_items', 'public');
        }

        $item = $restaurant->menuItems()->create($data);

        return response()->json($item, 201);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant || $menuItem->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'sometimes|numeric|min:0',
            'category'         => 'sometimes|string|max:100',
            'image'            => 'nullable|image|max:2048',
            'is_vegetarian'    => 'boolean',
            'is_vegan'         => 'boolean',
            'is_gluten_free'   => 'boolean',
            'spice_level'      => 'in:none,mild,medium,hot,extra_hot',
            'preparation_time' => 'integer|min:1',
            'is_available'     => 'boolean',
        ]);

        if ($request->has('sizes')) {
            $sizes = json_decode($request->input('sizes'), true);
            $data['sizes'] = is_array($sizes) ? $sizes : [];
        }
        if ($request->has('sizes_heading')) {
            $data['sizes_heading'] = substr(strip_tags($request->input('sizes_heading')), 0, 100) ?: 'Size';
        }
        if ($request->has('option_groups')) {
            $groups = json_decode($request->input('option_groups'), true);
            $data['option_groups'] = is_array($groups) ? $groups : null;
        }

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $data['image'] = $request->file('image')->store('menu_items', 'public');
        }

        $menuItem->update($data);

        return response()->json($menuItem);
    }

    public function destroy(Request $request, MenuItem $menuItem)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant || $menuItem->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($menuItem->image) {
            Storage::disk('public')->delete($menuItem->image);
        }

        $menuItem->delete();

        return response()->json(['message' => 'Menu item deleted.']);
    }
}
