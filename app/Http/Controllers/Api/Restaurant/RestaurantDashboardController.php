<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RestaurantDashboardController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        $today = Carbon::today();

        $todayOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', $today)
            ->count();

        $todayRevenue = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', $today)
            ->where('status', 'delivered')
            ->sum('total');

        $pendingOrders = Order::where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->count();

        $totalOrders = Order::where('restaurant_id', $restaurant->id)->count();

        $recentOrders = Order::where('restaurant_id', $restaurant->id)
            ->with('customer', 'items')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'restaurant'    => $restaurant,
            'stats' => [
                'today_orders'   => $todayOrders,
                'today_revenue'  => $todayRevenue,
                'pending_orders' => $pendingOrders,
                'total_orders'   => $totalOrders,
            ],
            'recent_orders' => $recentOrders,
        ]);
    }
}
