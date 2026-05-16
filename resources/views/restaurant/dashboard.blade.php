@extends('layouts.portal')
@section('title', 'Restaurant Dashboard')
@section('page-title', 'Dashboard')

@section('sidebar-nav')
    <a href="/restaurant/dashboard" class="portal-nav-link active">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>
    <a href="/restaurant/orders" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Orders
    </a>
    <a href="/restaurant/menu" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
        Menu
    </a>
    <a href="/restaurant/delivery-partners" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        Delivery Partners
    </a>
    <a href="/restaurant/settings" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Settings
    </a>
@endsection

@section('content')
<div x-data="restaurantDashboard">

    <!-- Loading -->
    <div x-show="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <template x-for="n in 4"><div class="skeleton h-28 rounded-xl"></div></template>
    </div>

    <template x-if="!loading && stats">
        <div>
            <!-- Stats row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="stat-card">
                    <p class="stat-label">Today's Orders</p>
                    <p class="stat-value" x-text="stats.today_orders"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Today's Revenue</p>
                    <p class="stat-value" x-text="fmtCurrency(stats.today_revenue)"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Pending Orders</p>
                    <p class="stat-value" x-text="stats.pending_orders"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Total Orders</p>
                    <p class="stat-value" x-text="stats.total_orders"></p>
                </div>
            </div>

            <!-- Restaurant status -->
            <template x-if="restaurant">
                <div class="portal-card mb-6 flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <p class="font-semibold" style="color:var(--color-portal-text)" x-text="restaurant.name"></p>
                        <div class="flex gap-2 mt-1">
                            <span :class="restaurant.is_active ? 'badge-delivered' : 'badge-cancelled'" class="badge"
                                  x-text="restaurant.is_active ? 'Open' : 'Closed'"></span>
                            <span :class="restaurant.is_approved ? 'badge-delivered' : 'badge-warning'" class="badge"
                                  x-text="restaurant.is_approved ? 'Approved' : 'Pending Approval'"></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="/restaurant/orders" class="btn-portal-ghost">View Orders</a>
                        <a href="/restaurant/settings" class="btn-portal">Settings</a>
                    </div>
                </div>
            </template>

            <!-- Recent orders -->
            <div class="portal-card">
                <div class="flex items-center justify-between mb-5">
                    <h2 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:var(--color-portal-text)">Recent Orders</h2>
                    <a href="/restaurant/orders" class="btn-portal-ghost text-sm">View all</a>
                </div>

                <template x-if="recentOrders.length === 0">
                    <p class="text-sm" style="color:var(--color-portal-muted)">No orders yet today.</p>
                </template>

                <div class="flex flex-col gap-3">
                    <template x-for="order in recentOrders" :key="order.id">
                        <div class="flex items-center justify-between p-3 rounded-xl" style="background:var(--color-portal-bg)">
                            <div>
                                <p class="text-sm font-medium" style="color:var(--color-portal-text)">
                                    Order #<span x-text="order.id"></span>
                                </p>
                                <p class="text-xs" style="color:var(--color-portal-muted)">
                                    <span x-text="fmtCurrency(order.total)"></span> ·
                                    <span x-text="fmtDateTime(order.created_at)"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span :class="'status-' + order.status" class="badge"
                                      x-text="statusLabel(order.status)"></span>
                                <template x-if="['pending','confirmed','preparing'].includes(order.status)">
                                    <button @click="advance(order)" :disabled="advancingId === order.id"
                                            class="btn-portal text-xs px-3 py-1.5">
                                        <span x-show="advancingId === order.id" class="spinner" style="width:12px;height:12px"></span>
                                        <span x-show="advancingId !== order.id">Advance →</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
