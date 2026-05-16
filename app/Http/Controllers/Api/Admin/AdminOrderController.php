<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,confirmed,preparing,ready,out_for_delivery,picked_up,delivered,cancelled',
        ]);

        $query = Order::with('customer', 'restaurant', 'deliveryPartner')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function cancel(Request $request, Order $order)
    {
        if (!$order->isCancellable()) {
            return response()->json(['message' => 'Order cannot be cancelled at this stage.'], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update(['status' => 'cancelled']);
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'cancelled',
                'changed_by' => $request->user()->id,
                'notes'      => 'Cancelled by admin.',
                'created_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Order cancelled.']);
    }
}
