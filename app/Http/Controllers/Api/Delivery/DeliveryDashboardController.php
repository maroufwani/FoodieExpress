<?php

namespace App\Http\Controllers\Api\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DeliveryDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->is_verified) {
            return response()->json(['message' => 'Your account is pending verification.'], 403);
        }

        $today = Carbon::today();

        $todayDeliveries = Order::where('delivery_partner_id', $user->id)
            ->whereDate('delivered_at', $today)
            ->where('status', 'delivered')
            ->count();

        $todayEarnings = Order::where('delivery_partner_id', $user->id)
            ->whereDate('delivered_at', $today)
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        // Available orders: handoff status, no driver assigned, scoped to assigned restaurant
        $availableOrdersQuery = Order::whereNull('delivery_partner_id')
            ->with('restaurant', 'items')
            ->latest();

        if ($user->restaurant_id) {
            $availableOrdersQuery->where('restaurant_id', $user->restaurant_id);
            // Filter by each restaurant's handoff status
            $availableOrdersQuery->whereHas('restaurant', function ($q) use ($user) {
                $q->where('id', $user->restaurant_id);
            });
            $restaurant = \App\Models\Restaurant::find($user->restaurant_id);
            $handoffStatus = $restaurant ? $restaurant->getDeliveryHandoffStatus() : 'ready';
            $availableOrdersQuery->where('status', $handoffStatus);
        } else {
            $availableOrdersQuery->where('status', 'ready');
        }

        $availableOrders = $availableOrdersQuery->get();

        // This partner's active deliveries
        $activeDeliveries = Order::where('delivery_partner_id', $user->id)
            ->whereIn('status', ['out_for_delivery', 'picked_up'])
            ->with('restaurant', 'customer')
            ->get();

        return response()->json([
            'stats' => [
                'today_deliveries'  => $todayDeliveries,
                'today_earnings'    => $todayEarnings,
                'total_deliveries'  => $user->total_deliveries,
                'rating'            => $user->rating,
                'is_available'      => $user->is_available,
            ],
            'available_orders'  => $availableOrders,
            'active_deliveries' => $activeDeliveries,
        ]);
    }

    public function toggleAvailability(Request $request)
    {
        $user = $request->user();
        $user->update(['is_available' => !$user->is_available]);

        return response()->json([
            'is_available' => $user->is_available,
            'message'      => $user->is_available ? 'You are now online.' : 'You are now offline.',
        ]);
    }
}
