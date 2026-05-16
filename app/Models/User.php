<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'is_verified',
        'is_available',
        'vehicle_type',
        'restaurant_id',
        'rating',
        'total_deliveries',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'is_verified'      => 'boolean',
        'is_available'     => 'boolean',
        'rating'           => 'decimal:2',
        'total_deliveries' => 'integer',
        'password'         => 'hashed',
    ];

    // Relationships
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function restaurant()
    {
        return $this->hasOne(Restaurant::class, 'owner_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Order::class, 'delivery_partner_id');
    }

    public function assignedRestaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRestaurantOwner(): bool
    {
        return $this->role === 'restaurant_owner';
    }

    public function isDelivery(): bool
    {
        return $this->role === 'delivery';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
}
