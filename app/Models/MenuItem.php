<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'price',
        'sizes',
        'sizes_heading',
        'option_groups',
        'category',
        'image',
        'is_vegetarian',
        'is_vegan',
        'is_gluten_free',
        'spice_level',
        'preparation_time',
        'is_available',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'sizes'            => 'array',
        'option_groups'    => 'array',
        'is_vegetarian'    => 'boolean',
        'is_vegan'         => 'boolean',
        'is_gluten_free'   => 'boolean',
        'is_available'     => 'boolean',
        'preparation_time' => 'integer',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
