<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RestaurantSettingsController extends Controller
{
    public function show(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        return response()->json($restaurant);
    }

    public function create(Request $request)
    {
        $user = $request->user();

        if ($user->restaurant) {
            return response()->json(['message' => 'You already have a restaurant.'], 422);
        }

        $data = $request->validate([
            'name'                    => 'required|string|max:255',
            'description'             => 'nullable|string',
            'cuisine_types'           => 'nullable|array',
            'cuisine_types.*'         => 'string',
            'image'                   => 'nullable|image|max:4096',
            'street'                  => 'required|string',
            'city'                    => 'required|string',
            'state'                   => 'required|string',
            'zip_code'                => 'required|string',
            'latitude'                => 'nullable|numeric|between:-90,90',
            'longitude'               => 'nullable|numeric|between:-180,180',
            'delivery_radius'         => 'required|numeric|min:0.1',
            'delivery_fee'            => 'required|numeric|min:0',
            'min_order_amount'        => 'required|numeric|min:0',
            'phone'                   => 'nullable|string',
            'email'                   => 'nullable|email',
            'opening_hours'           => 'nullable|array',
            'estimated_delivery_time' => 'nullable|integer|min:1',
            'status_flow'                => 'nullable|array|max:16',
            'status_flow.*.key'          => ['required','string','regex:/^[a-z][a-z0-9_]{0,49}$/','not_in:cancelled'],
            'status_flow.*.label'        => 'required|string|max:50',
            'status_flow.*.by_delivery'  => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('restaurants', 'public');
        }

        if (isset($data['status_flow'])) {
            $data['status_flow'] = $this->normaliseFlow($data['status_flow']);
        }

        $data['owner_id'] = $user->id;
        $restaurant = Restaurant::create($data);

        return response()->json($restaurant, 201);
    }

    public function update(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        $data = $request->validate([
            'name'                    => 'sometimes|string|max:255',
            'description'             => 'nullable|string',
            'cuisine_types'           => 'nullable|array',
            'cuisine_types.*'         => 'string',
            'image'                   => 'nullable|image|max:4096',
            'street'                  => 'sometimes|string',
            'city'                    => 'sometimes|string',
            'state'                   => 'sometimes|string',
            'zip_code'                => 'sometimes|string',
            'latitude'                => 'nullable|numeric|between:-90,90',
            'longitude'               => 'nullable|numeric|between:-180,180',
            'delivery_radius'         => 'sometimes|numeric|min:0.1',
            'delivery_fee'            => 'sometimes|numeric|min:0',
            'min_order_amount'        => 'sometimes|numeric|min:0',
            'phone'                   => 'nullable|string',
            'email'                   => 'nullable|email',
            'opening_hours'           => 'nullable|array',
            'estimated_delivery_time' => 'nullable|integer|min:1',
            'status_flow'                => 'nullable|array|max:16',
            'status_flow.*.key'          => ['sometimes','required','string','regex:/^[a-z][a-z0-9_]{0,49}$/','not_in:cancelled'],
            'status_flow.*.label'        => 'sometimes|required|string|max:50',
            'status_flow.*.by_delivery'  => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($restaurant->image) {
                Storage::disk('public')->delete($restaurant->image);
            }
            $data['image'] = $request->file('image')->store('restaurants', 'public');
        }

        if (isset($data['status_flow'])) {
            $data['status_flow'] = $this->normaliseFlow($data['status_flow']);
        }

        $restaurant->update($data);

        return response()->json($restaurant);
    }

    private function normaliseFlow(array $flow): array
    {
        // Normalize by_delivery to a real bool (FormData sends "0"/"1"/"true"/"false")
        $flow = array_map(function ($s) {
            $bd = $s['by_delivery'] ?? false;
            $s['by_delivery'] = filter_var($bd, FILTER_VALIDATE_BOOLEAN);
            return $s;
        }, $flow);

        // Strip the always-managed anchors from the submitted list
        $restaurantSteps = array_values(array_filter(
            $flow,
            fn($s) => !$s['by_delivery'] && !in_array($s['key'], ['pending', 'delivered'], true)
        ));
        $deliverySteps = array_values(array_filter(
            $flow,
            fn($s) => $s['by_delivery'] && $s['key'] !== 'delivered'
        ));

        // Deduplicate each section by key
        $dedup = function (array $list): array {
            $seen = [];
            return array_values(array_filter($list, function ($s) use (&$seen) {
                if (in_array($s['key'], $seen, true)) return false;
                $seen[] = $s['key'];
                return true;
            }));
        };

        $restaurantSteps = $dedup($restaurantSteps);
        $deliverySteps   = $dedup($deliverySteps);

        // Find delivered label if the restaurant customised it
        $deliveredLabel = 'Delivered';
        foreach ($flow as $s) {
            if ($s['key'] === 'delivered') { $deliveredLabel = $s['label'] ?? 'Delivered'; break; }
        }

        // Build: pending → [restaurant steps] → [delivery steps] → delivered
        return [
            ['key' => 'pending',   'label' => 'Pending',       'by_delivery' => false],
            ...$restaurantSteps,
            ...$deliverySteps,
            ['key' => 'delivered', 'label' => $deliveredLabel, 'by_delivery' => true],
        ];
    }
}
