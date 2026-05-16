<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'name',
        'price',
        'quantity',
        'special_instructions',
        'extras',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
        'extras'   => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
