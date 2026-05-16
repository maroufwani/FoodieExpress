<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'delivery_partner_id',
        'delivery_address',
        'subtotal',
        'delivery_fee',
        'tax',
        'total',
        'payment_method',
        'payment_status',
        'status',
        'special_instructions',
        'estimated_delivery_time',
        'delivered_at',
        'food_rating',
        'delivery_rating',
    ];

    protected $casts = [
        'delivery_address'        => 'array',
        'food_rating'             => 'integer',
        'delivery_rating'         => 'integer',
        'subtotal'                => 'decimal:2',
        'delivery_fee'            => 'decimal:2',
        'tax'                     => 'decimal:2',
        'total'                   => 'decimal:2',
        'estimated_delivery_time' => 'integer',
        'delivered_at'            => 'datetime',
    ];

    // Statuses that always prevent cancellation regardless of restaurant flow
    public const UNCANCELLABLE_STATUSES = ['delivered', 'cancelled'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function deliveryPartner()
    {
        return $this->belongsTo(User::class, 'delivery_partner_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function isCancellable(): bool
    {
        if (in_array($this->status, self::UNCANCELLABLE_STATUSES, true)) {
            return false;
        }

        // Once the delivery partner is handling the order (any delivery step), cancel is not allowed
        if ($this->relationLoaded('restaurant') && $this->restaurant) {
            $flow = $this->restaurant->getStatusFlow();
            foreach ($flow as $step) {
                if ($step['key'] === $this->status && !empty($step['by_delivery'])) {
                    return false;
                }
            }
        } else {
            // Fallback: hardcode default delivery step keys
            if (in_array($this->status, ['out_for_delivery', 'picked_up'], true)) {
                return false;
            }
        }

        return true;
    }
}
