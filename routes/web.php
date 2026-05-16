<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/restaurants/{id}', [PageController::class, 'restaurant'])->name('restaurant');

// Auth
Route::get('/login',    [PageController::class, 'login'])->name('login');
Route::get('/register', [PageController::class, 'register'])->name('register');

// Customer
Route::get('/orders',        [PageController::class, 'orders'])->name('orders');
Route::get('/orders/{id}',   [PageController::class, 'orderDetail'])->name('order.detail');
Route::get('/checkout',      [PageController::class, 'checkout'])->name('checkout');
Route::get('/profile',       [PageController::class, 'profile'])->name('profile');

// Restaurant portal
Route::get('/restaurant/dashboard',          [PageController::class, 'restaurantDashboard'])->name('restaurant.dashboard');
Route::get('/restaurant/orders',             [PageController::class, 'restaurantOrders'])->name('restaurant.orders');
Route::get('/restaurant/menu',               [PageController::class, 'restaurantMenu'])->name('restaurant.menu');
Route::get('/restaurant/settings',           [PageController::class, 'restaurantSettings'])->name('restaurant.settings');
Route::get('/restaurant/delivery-partners',  [PageController::class, 'restaurantDeliveryPartners'])->name('restaurant.delivery-partners');

// Delivery portal
Route::get('/delivery/dashboard', [PageController::class, 'deliveryDashboard'])->name('delivery.dashboard');
Route::get('/delivery/history',   [PageController::class, 'deliveryHistory'])->name('delivery.history');

// Admin portal
Route::get('/admin/dashboard',          [PageController::class, 'adminDashboard'])->name('admin.dashboard');
Route::get('/admin/users',              [PageController::class, 'adminUsers'])->name('admin.users');
Route::get('/admin/restaurants',        [PageController::class, 'adminRestaurants'])->name('admin.restaurants');
Route::get('/admin/orders',             [PageController::class, 'adminOrders'])->name('admin.orders');
Route::get('/admin/delivery-partners',  [PageController::class, 'adminDelivery'])->name('admin.delivery');
