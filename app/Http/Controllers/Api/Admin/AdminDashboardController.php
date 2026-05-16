<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalCustomers       = User::where('role', 'customer')->count();
        $totalRestaurants     = Restaurant::count();
        $totalOrders          = Order::count();
        $totalDeliveryPartners = User::where('role', 'delivery')->count();
        $totalRevenue         = Order::where('status', 'delivered')->sum('total');
        $activeOrders         = Order::whereNotIn('status', ['delivered', 'cancelled'])->count();

        $pendingApprovals = Restaurant::where('is_approved', false)->count();

        $recentOrders = Order::with('customer', 'restaurant')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'stats' => [
                'total_customers'        => $totalCustomers,
                'total_restaurants'      => $totalRestaurants,
                'total_orders'           => $totalOrders,
                'total_delivery_partners' => $totalDeliveryPartners,
                'total_revenue'          => $totalRevenue,
                'active_orders'          => $activeOrders,
            ],
            'pending_approvals' => $pendingApprovals,
            'recent_orders'     => $recentOrders,
        ]);
    }
}
