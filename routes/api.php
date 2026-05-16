<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminDeliveryPartnerController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminRestaurantController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Delivery\DeliveryDashboardController;
use App\Http\Controllers\Api\Delivery\DeliveryOrderController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderRatingController;
use App\Http\Controllers\Api\Restaurant\MenuItemController;
use App\Http\Controllers\Api\Restaurant\RestaurantDashboardController;
use App\Http\Controllers\Api\Restaurant\RestaurantDeliveryPartnerController;
use App\Http\Controllers\Api\Restaurant\RestaurantOrderController;
use App\Http\Controllers\Api\Restaurant\RestaurantSettingsController;
use App\Http\Controllers\Api\RestaurantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Public restaurant listing & detail
Route::get('/restaurants',       [RestaurantController::class, 'index']);
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (any role)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout',     [AuthController::class, 'logout']);
    Route::get('/auth/profile',     [AuthController::class, 'profile']);
    Route::put('/auth/profile',     [AuthController::class, 'updateProfile']);

    // Addresses (customers)
    Route::prefix('auth/addresses')->group(function () {
        Route::get('/',        [AddressController::class, 'index']);
        Route::post('/',       [AddressController::class, 'store']);
        Route::put('/{address}',    [AddressController::class, 'update']);
        Route::delete('/{address}', [AddressController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:customer')->prefix('orders')->group(function () {
        Route::get('/',               [OrderController::class, 'index']);
        Route::post('/',              [OrderController::class, 'store']);
        Route::get('/{order}',        [OrderController::class, 'show']);
        Route::put('/{order}/cancel', [OrderController::class, 'cancel']);
        Route::post('/{order}/rate',  [OrderRatingController::class, 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | Restaurant Owner Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:restaurant_owner')->prefix('restaurant')->group(function () {
        Route::get('/dashboard',    [RestaurantDashboardController::class, 'index']);
        Route::post('/',            [RestaurantSettingsController::class, 'create']);
        Route::get('/settings',     [RestaurantSettingsController::class, 'show']);
        Route::put('/settings',     [RestaurantSettingsController::class, 'update']);

        // Orders
        Route::get('/orders',               [RestaurantOrderController::class, 'index']);
        Route::get('/orders/{order}',       [RestaurantOrderController::class, 'show']);
        Route::put('/orders/{order}/advance', [RestaurantOrderController::class, 'updateStatus']);
        Route::put('/orders/{order}/cancel',  [RestaurantOrderController::class, 'cancel']);

        // Menu items
        Route::get('/menu',              [MenuItemController::class, 'index']);
        Route::post('/menu',             [MenuItemController::class, 'store']);
        Route::put('/menu/{menuItem}',   [MenuItemController::class, 'update']);
        Route::delete('/menu/{menuItem}', [MenuItemController::class, 'destroy']);

        // Delivery partners
        Route::get('/delivery-partners',           [RestaurantDeliveryPartnerController::class, 'index']);
        Route::post('/delivery-partners',          [RestaurantDeliveryPartnerController::class, 'store']);
        Route::put('/delivery-partners/{user}',    [RestaurantDeliveryPartnerController::class, 'update']);
        Route::delete('/delivery-partners/{user}', [RestaurantDeliveryPartnerController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Delivery Partner Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:delivery')->prefix('delivery')->group(function () {
        Route::get('/dashboard',                    [DeliveryDashboardController::class, 'index']);
        Route::put('/availability',                 [DeliveryDashboardController::class, 'toggleAvailability']);
        Route::post('/orders/{order}/accept',       [DeliveryOrderController::class, 'accept']);
        Route::put('/orders/{order}/status',        [DeliveryOrderController::class, 'updateStatus']);
        Route::get('/history',                      [DeliveryOrderController::class, 'history']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        // Users
        Route::get('/users',            [AdminUserController::class, 'index']);
        Route::post('/users',           [AdminUserController::class, 'store']);
        Route::put('/users/{user}',     [AdminUserController::class, 'update']);
        Route::delete('/users/{user}',  [AdminUserController::class, 'destroy']);

        // Restaurants
        Route::get('/restaurants',                          [AdminRestaurantController::class, 'index']);
        Route::put('/restaurants/{restaurant}/approve',     [AdminRestaurantController::class, 'approve']);
        Route::put('/restaurants/{restaurant}/toggle',      [AdminRestaurantController::class, 'toggle']);
        Route::delete('/restaurants/{restaurant}',          [AdminRestaurantController::class, 'destroy']);

        // Orders
        Route::get('/orders',                [AdminOrderController::class, 'index']);
        Route::put('/orders/{order}/cancel', [AdminOrderController::class, 'cancel']);

        // Delivery partners
        Route::get('/delivery-partners',                          [AdminDeliveryPartnerController::class, 'index']);
        Route::put('/delivery-partners/{user}/verify',            [AdminDeliveryPartnerController::class, 'verify']);
        Route::put('/delivery-partners/{user}/toggle-availability', [AdminDeliveryPartnerController::class, 'toggleAvailability']);
    });
});
