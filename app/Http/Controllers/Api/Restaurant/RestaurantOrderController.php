<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantOrderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|string|max:50',
        ]);

        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'No restaurant found.'], 404);
        }

        $query = Order::where('restaurant_id', $restaurant->id)
            ->with('customer', 'items', 'deliveryPartner')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginated = $query->paginate(20)->toArray();
        $paginated['status_flow'] = $restaurant->getStatusFlow();

        return response()->json($paginated);
    }

    public function show(Request $request, Order $order)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant || $order->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $order->load('customer', 'items', 'statusHistories', 'deliveryPartner');

        return response()->json($order);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant || $order->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $nextStatus = $restaurant->getNextStatus($order->status);

        if (!$nextStatus) {
            return response()->json(['message' => "Cannot advance order from '{$order->status}'."], 422);
        }

        DB::transaction(function () use ($order, $nextStatus, $request) {
            $order->update(['status' => $nextStatus]);
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $nextStatus,
                'changed_by' => $request->user()->id,
                'created_at' => now(),
            ]);
        });

        // Resolve next status label for the response
        $flow       = $restaurant->getStatusFlow();
        $nextLabel  = collect($flow)->firstWhere('key', $nextStatus)['label'] ?? $nextStatus;

        return response()->json(['message' => "Order status updated to '{$nextLabel}'.", 'status' => $nextStatus]);
    }

    public function cancel(Request $request, Order $order)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant || $order->restaurant_id !== $restaurant->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (!$order->isCancellable()) {
            return response()->json(['message' => 'Order cannot be cancelled at this stage.'], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update(['status' => 'cancelled']);
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'cancelled',
                'changed_by' => $request->user()->id,
                'notes'      => 'Cancelled by restaurant.',
                'created_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Order cancelled.']);
    }
}
