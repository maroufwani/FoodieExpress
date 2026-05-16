<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderRatingController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $user = $request->user();

        if ($order->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($order->status !== 'delivered') {
            return response()->json(['message' => 'You can only rate a delivered order.'], 422);
        }

        if ($order->food_rating !== null || $order->delivery_rating !== null) {
            return response()->json(['message' => 'This order has already been rated.'], 422);
        }

        $data = $request->validate([
            'food_rating'     => 'required|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
        ]);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'food_rating'     => $data['food_rating'],
                'delivery_rating' => $data['delivery_rating'] ?? null,
            ]);

            // Update restaurant's average rating
            $restaurant = $order->restaurant;
            if ($restaurant) {
                $avg = \App\Models\Order::where('restaurant_id', $restaurant->id)
                    ->whereNotNull('food_rating')
                    ->avg('food_rating');
                $restaurant->update(['rating' => round($avg, 2)]);
            }

            // Update delivery partner's average rating
            if (!empty($data['delivery_rating']) && $order->delivery_partner_id) {
                $partner = \App\Models\User::find($order->delivery_partner_id);
                if ($partner) {
                    $avgDelivery = \App\Models\Order::where('delivery_partner_id', $partner->id)
                        ->whereNotNull('delivery_rating')
                        ->avg('delivery_rating');
                    $partner->update(['rating' => round($avgDelivery, 2)]);
                }
            }
        });

        return response()->json(['message' => 'Thank you for your rating!']);
    }
}
