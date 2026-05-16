<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    // Public
    public function home()          { return view('pages.home'); }
    public function restaurant($id) { return view('pages.restaurant', ['restaurantId' => $id]); }

    // Auth
    public function login()    { return view('auth.login'); }
    public function register() { return view('auth.register'); }

    // Customer
    public function orders()       { return view('customer.orders'); }
    public function orderDetail($id){ return view('customer.order-detail', ['orderId' => $id]); }
    public function checkout()     { return view('customer.checkout'); }
    public function profile()      { return view('customer.profile'); }

    // Restaurant portal
    public function restaurantDashboard() { return view('restaurant.dashboard'); }
    public function restaurantOrders()    { return view('restaurant.orders'); }
    public function restaurantMenu()      { return view('restaurant.menu'); }
    public function restaurantSettings()  { return view('restaurant.settings'); }
    public function restaurantDeliveryPartners() { return view('restaurant.delivery-partners'); }

    // Delivery portal
    public function deliveryDashboard() { return view('delivery.dashboard'); }
    public function deliveryHistory()   { return view('delivery.history'); }

    // Admin portal
    public function adminDashboard()    { return view('admin.dashboard'); }
    public function adminUsers()        { return view('admin.users'); }
    public function adminRestaurants()  { return view('admin.restaurants'); }
    public function adminOrders()       { return view('admin.orders'); }
    public function adminDelivery()     { return view('admin.delivery-partners'); }
}
