<?php

namespace App\Http\Controllers\Api\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    /**
     * Delivery partner accepts an order that is in the restaurant's
     * "delivery handoff" status (the step just before the first delivery step).
     */
    public function accept(Request $request, Order $order)
    {
        $user = $request->user();

        if (!$user->is_verified) {
            return response()->json(['message' => 'Your account is pending verification.'], 403);
        }

        if (!$user->is_available) {
            return response()->json(['message' => 'You must be online to accept orders.'], 422);
        }

        // Enforce restaurant scoping
        if ($user->restaurant_id && $order->restaurant_id !== $user->restaurant_id) {
            return response()->json(['message' => 'Order is not from your assigned restaurant.'], 403);
        }

        $restaurant         = $order->restaurant;
        $handoffStatus      = $restaurant ? $restaurant->getDeliveryHandoffStatus()  : 'ready';
        $firstDeliveryStatus = $restaurant ? $restaurant->getFirstDeliveryStatus()   : 'out_for_delivery';

        if ($order->status !== $handoffStatus || $order->delivery_partner_id !== null) {
            return response()->json(['message' => 'Order is no longer available.'], 422);
        }

        DB::transaction(function () use ($order, $user, $firstDeliveryStatus) {
            $order->update([
                'delivery_partner_id' => $user->id,
                'status'              => $firstDeliveryStatus,
            ]);
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $firstDeliveryStatus,
                'changed_by' => $user->id,
                'notes'      => 'Accepted by delivery partner.',
                'created_at' => now(),
            ]);
        });

        $order->load('restaurant', 'customer', 'items');

        return response()->json($order);
    }

    /**
     * Delivery partner advances through delivery-phase statuses defined
     * in the restaurant's status flow.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $user = $request->user();

        if ($order->delivery_partner_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $restaurant = $order->restaurant;

        // Guard: current status must be a delivery step
        if ($restaurant) {
            $flow        = $restaurant->getStatusFlow();
            $currentStep = collect($flow)->firstWhere('key', $order->status);
            if (!$currentStep || empty($currentStep['by_delivery'])) {
                return response()->json(['message' => 'This status is managed by the restaurant.'], 422);
            }
        }

        $nextStatus = $restaurant ? $restaurant->getNextStatus($order->status) : null;

        if (!$nextStatus) {
            return response()->json(['message' => "Cannot advance order from '{$order->status}'."], 422);
        }

        // Verify the next step is also a delivery step (safety check)
        if ($restaurant) {
            $flow      = $restaurant->getStatusFlow();
            $nextStep  = collect($flow)->firstWhere('key', $nextStatus);
            if (!$nextStep || empty($nextStep['by_delivery'])) {
                return response()->json(['message' => "Cannot advance to '{$nextStatus}' — that step is not a delivery step."], 422);
            }
        }

        // Determine whether the next status is the final step (no further step)
        $isFinal = $restaurant
            ? $restaurant->getNextStatus($nextStatus) === null
            : $nextStatus === 'delivered';

        DB::transaction(function () use ($order, $nextStatus, $user, $isFinal) {
            $updates = ['status' => $nextStatus];

            if ($isFinal) {
                $updates['payment_status'] = 'paid';
                $updates['delivered_at']   = now();
            }

            $order->update($updates);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $nextStatus,
                'changed_by' => $user->id,
                'created_at' => now(),
            ]);

            if ($isFinal) {
                $user->increment('total_deliveries');
            }
        });

        return response()->json(['message' => "Order status updated to '{$nextStatus}'.", 'status' => $nextStatus]);
    }

    /**
     * Delivery partner's completed order history.
     */
    public function history(Request $request)
    {
        $orders = Order::where('delivery_partner_id', $request->user()->id)
            ->where('status', 'delivered')
            ->with('restaurant', 'customer')
            ->latest('delivered_at')
            ->paginate(20);

        return response()->json($orders);
    }
}
