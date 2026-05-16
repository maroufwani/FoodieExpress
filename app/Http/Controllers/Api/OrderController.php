<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Place a new order. Prices are re-verified server-side.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurant_id'       => 'required|exists:restaurants,id',
            'items'               => 'required|array|min:1',
            'items.*.menu_item_id'          => 'required|exists:menu_items,id',
            'items.*.quantity'              => 'required|integer|min:1',
            'items.*.size'                  => 'nullable|string|max:50',
            'items.*.special_instructions'  => 'nullable|string|max:500',
            'items.*.extras'                => 'nullable|array',
            'items.*.extras.*.id'           => 'required|integer',
            'items.*.extras.*.name'         => 'required|string|max:191',
            'items.*.extras.*.price'        => 'required|numeric|min:0',
            'items.*.extras.*.size'         => 'nullable|string|max:50',
            'delivery_address'    => 'required|array',
            'delivery_address.label'          => 'required|string',
            'delivery_address.recipient_name' => 'required|string',
            'delivery_address.phone'          => 'required|string',
            'delivery_address.street'         => 'nullable|string',
            'delivery_address.city'           => 'nullable|string',
            'delivery_address.state'          => 'nullable|string',
            'delivery_address.zip_code'       => 'nullable|string',
            'delivery_address.latitude'       => 'nullable|numeric',
            'delivery_address.longitude'      => 'nullable|numeric',
            'payment_method'      => 'required|in:cash_on_delivery,card,digital_wallet,net_banking',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        $restaurant = Restaurant::findOrFail($data['restaurant_id']);

        if (!$restaurant->is_approved || !$restaurant->is_active) {
            return response()->json(['message' => 'Restaurant is not available.'], 422);
        }

        // Validate delivery radius when coordinates provided
        $addr = $data['delivery_address'];
        if (!empty($addr['latitude']) && !empty($addr['longitude'])) {
            if (!$restaurant->coversLocation((float) $addr['latitude'], (float) $addr['longitude'])) {
                return response()->json(['message' => 'Delivery address is outside the restaurant\'s delivery radius.'], 422);
            }
        }

        // Re-fetch prices server-side — client-submitted prices are discarded
        $menuItems = MenuItem::whereIn('id', collect($data['items'])->pluck('menu_item_id'))
            ->where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        foreach ($data['items'] as $item) {
            if (!isset($menuItems[$item['menu_item_id']])) {
                return response()->json([
                    'message' => "Menu item {$item['menu_item_id']} is not available from this restaurant.",
                ], 422);
            }
        }

        // Calculate totals — prices are re-verified server-side against DB
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $menuItem = $menuItems[$item['menu_item_id']];

            // Resolve price: use size variant when provided, else base price
            $price = (float) $menuItem->price;
            if (!empty($item['size']) && !empty($menuItem->sizes)) {
                $sizeEntry = collect($menuItem->sizes)->firstWhere('label', $item['size']);
                if ($sizeEntry) {
                    $price = (float) $sizeEntry['price'];
                }
            }

            // Add extras price (server-side re-verified below; use client price here only for min-order check)
            $extrasPrice = 0;
            if (!empty($item['extras'])) {
                foreach ($item['extras'] as $extra) {
                    $extrasPrice += (float) ($extra['price'] ?? 0);
                }
            }

            $subtotal += ($price + $extrasPrice) * $item['quantity'];
        }

        if ($subtotal < $restaurant->min_order_amount) {
            return response()->json([
                'message' => "Minimum order amount is {$restaurant->min_order_amount}.",
            ], 422);
        }

        $deliveryFee = $restaurant->delivery_fee;
        $tax         = round($subtotal * 0.10, 2);
        $total       = $subtotal + $deliveryFee + $tax;

        $order = DB::transaction(function () use ($data, $restaurant, $menuItems, $subtotal, $deliveryFee, $tax, $total, $request) {
            $order = Order::create([
                'customer_id'             => $request->user()->id,
                'restaurant_id'           => $restaurant->id,
                'delivery_address'        => $data['delivery_address'],
                'subtotal'                => $subtotal,
                'delivery_fee'            => $deliveryFee,
                'tax'                     => $tax,
                'total'                   => $total,
                'payment_method'          => $data['payment_method'],
                'status'                  => 'pending',
                'special_instructions'    => $data['special_instructions'] ?? null,
                'estimated_delivery_time' => $restaurant->estimated_delivery_time,
            ]);

            foreach ($data['items'] as $item) {
                $menuItem = $menuItems[$item['menu_item_id']];

            // Re-resolve price (same logic as totals calculation above)
                $price = (float) $menuItem->price;
                if (!empty($item['size']) && !empty($menuItem->sizes)) {
                    $sizeEntry = collect($menuItem->sizes)->firstWhere('label', $item['size']);
                    if ($sizeEntry) {
                        $price = (float) $sizeEntry['price'];
                    }
                }

                $itemName = $menuItem->name . (!empty($item['size']) ? ' (' . $item['size'] . ')' : '');

                // Verify extras against DB and re-resolve their prices
                $extrasData = null;
                if (!empty($item['extras'])) {
                    // Separate menu-item toppings (have id) from inline option group selections (id null)
                    $toppingIds = collect($item['extras'])->filter(fn($e) => !empty($e['id']))->pluck('id');
                    $toppingItems = MenuItem::whereIn('id', $toppingIds)
                        ->where('restaurant_id', $restaurant->id)
                        ->where('is_available', true)
                        ->get()
                        ->keyBy('id');

                    $extrasData = collect($item['extras'])->map(function ($extra) use ($toppingItems, $menuItem) {
                        // Inline option (from option_groups on the menu item — no separate menu item id)
                        if (empty($extra['id'])) {
                            $name  = strip_tags($extra['name'] ?? '');
                            $group = strip_tags($extra['group'] ?? '');
                            // Verify price against the menu item's stored option_groups
                            $verifiedPrice = null;
                            foreach ($menuItem->option_groups ?? [] as $og) {
                                foreach ($og['options'] ?? [] as $opt) {
                                    if (trim($opt['name'] ?? '') === trim($name)) {
                                        $verifiedPrice = (float) ($opt['price'] ?? 0);
                                        break 2;
                                    }
                                }
                            }
                            if ($verifiedPrice === null || $name === '') return null;
                            return ['id' => null, 'name' => $name, 'price' => $verifiedPrice, 'group' => $group];
                        }
                        // Regular topping (separate menu item)
                        $topping = $toppingItems[$extra['id']] ?? null;
                        if (!$topping) return null;
                        // Resolve price from sizes if size provided, else base price
                        $extraPrice = (float) $topping->price;
                        if (!empty($extra['size']) && !empty($topping->sizes)) {
                            $sz = collect($topping->sizes)->firstWhere('label', $extra['size']);
                            if ($sz) $extraPrice = (float) $sz['price'];
                        }
                        return [
                            'id'    => $topping->id,
                            'name'  => $topping->name,
                            'price' => $extraPrice,
                            'size'  => $extra['size'] ?? null,
                        ];
                    })->filter()->values()->toArray();
                }

                OrderItem::create([
                    'order_id'             => $order->id,
                    'menu_item_id'         => $menuItem->id,
                    'name'                 => $itemName,
                    'price'                => $price,
                    'quantity'             => $item['quantity'],
                    'special_instructions' => $item['special_instructions'] ?? null,
                    'extras'               => $extrasData ?: null,
                ]);
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'pending',
                'changed_by' => $request->user()->id,
                'created_at' => now(),
            ]);

            return $order;
        });

        $order->load('items', 'statusHistories', 'restaurant');

        return response()->json($order, 201);
    }

    /**
     * Customer order history with status filter.
     */
    public function index(Request $request)
    {
        $request->validate([
            'filter' => 'nullable|in:all,active,completed,cancelled',
        ]);

        $query = Order::where('customer_id', $request->user()->id)
            ->with('restaurant', 'items')
            ->latest();

        $filter = $request->get('filter', 'all');

        if ($filter === 'active') {
            $query->whereNotIn('status', ['delivered', 'cancelled']);
        } elseif ($filter === 'completed') {
            $query->where('status', 'delivered');
        } elseif ($filter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        return response()->json($query->paginate(15));
    }

    /**
     * Customer order detail.
     */
    public function show(Request $request, Order $order)
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $order->load('items', 'statusHistories', 'restaurant', 'deliveryPartner');

        return response()->json($order);
    }

    /**
     * Customer cancels their order.
     */
    public function cancel(Request $request, Order $order)
    {
        if ($order->customer_id !== $request->user()->id) {
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
                'notes'      => 'Cancelled by customer.',
                'created_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Order cancelled.']);
    }
}
