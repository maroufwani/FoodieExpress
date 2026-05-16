<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    public const DEFAULT_STATUS_FLOW = [
        ['key' => 'pending',          'label' => 'Pending',          'by_delivery' => false],
        ['key' => 'confirmed',        'label' => 'Confirmed',        'by_delivery' => false],
        ['key' => 'preparing',        'label' => 'Preparing',        'by_delivery' => false],
        ['key' => 'ready',            'label' => 'Ready',            'by_delivery' => false],
        ['key' => 'out_for_delivery', 'label' => 'Out for Delivery', 'by_delivery' => true],
        ['key' => 'picked_up',        'label' => 'At Your Door',     'by_delivery' => true],
        ['key' => 'delivered',        'label' => 'Delivered',        'by_delivery' => true],
    ];

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'cuisine_types',
        'image',
        'street',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'delivery_radius',
        'delivery_fee',
        'min_order_amount',
        'phone',
        'email',
        'opening_hours',
        'estimated_delivery_time',
        'rating',
        'is_active',
        'is_approved',
        'status_flow',
    ];

    protected $casts = [
        'cuisine_types'  => 'array',
        'opening_hours'  => 'array',
        'status_flow'    => 'array',
        'is_active'      => 'boolean',
        'is_approved'    => 'boolean',
        'delivery_radius'   => 'decimal:2',
        'delivery_fee'      => 'decimal:2',
        'min_order_amount'  => 'decimal:2',
        'rating'            => 'decimal:2',
        'latitude'          => 'decimal:8',
        'longitude'         => 'decimal:8',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deliveryPartners()
    {
        return $this->hasMany(User::class, 'restaurant_id')->where('role', 'delivery');
    }

    /**
     * Calculate Haversine distance (km) from this restaurant to given coordinates.
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($this->latitude)) * cos(deg2rad($lat))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if the given coordinates are within the delivery radius.
     */
    public function coversLocation(float $lat, float $lng): bool
    {
        return $this->distanceTo($lat, $lng) <= $this->delivery_radius;
    }

    public function getStatusFlow(): array
    {
        return $this->status_flow ?? self::DEFAULT_STATUS_FLOW;
    }

    public function getNextStatus(string $current): ?string
    {
        $keys = array_column($this->getStatusFlow(), 'key');
        $idx  = array_search($current, $keys, true);

        if ($idx === false || $idx >= count($keys) - 1) {
            return null;
        }

        return $keys[$idx + 1];
    }

    /**
     * The status just before the first delivery step — this is where a
     * delivery partner "accepts" and takes over the order.
     */
    public function getDeliveryHandoffStatus(): string
    {
        $prev = 'ready';
        foreach ($this->getStatusFlow() as $step) {
            if (!empty($step['by_delivery'])) {
                return $prev;
            }
            $prev = $step['key'];
        }
        return $prev;
    }

    /**
     * The first step controlled by the delivery partner.
     */
    public function getFirstDeliveryStatus(): string
    {
        foreach ($this->getStatusFlow() as $step) {
            if (!empty($step['by_delivery'])) {
                return $step['key'];
            }
        }
        return 'out_for_delivery';
    }
}
