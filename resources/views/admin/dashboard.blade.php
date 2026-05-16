@extends('layouts.portal')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')
@section('admin-nav-dashboard', 'active')

@section('sidebar-nav')
    @include('admin._sidebar')
@endsection

@section('content')
<div x-data="adminDashboard">

    <!-- Loading -->
    <div x-show="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <template x-for="n in 4"><div class="skeleton h-28 rounded-xl"></div></template>
    </div>

    <template x-if="!loading && stats">
        <div>
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="stat-card">
                    <p class="stat-label">Total Users</p>
                    <p class="stat-value" x-text="stats.total_users"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Restaurants</p>
                    <p class="stat-value" x-text="stats.total_restaurants"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Total Orders</p>
                    <p class="stat-value" x-text="stats.total_orders"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Platform Revenue</p>
                    <p class="stat-value" x-text="fmtCurrency(stats.total_revenue || 0)"></p>
                </div>
            </div>

            <!-- Pending approvals alert -->
            <template x-if="pendingApprovals > 0">
                <div class="portal-card mb-6 flex items-center gap-4"
                     style="border-left:4px solid var(--color-warning)">
                    <span style="font-size:1.5rem">⚠️</span>
                    <div class="flex-1">
                        <p class="font-semibold" style="color:var(--color-portal-text)">
                            <span x-text="pendingApprovals"></span> restaurant(s) pending approval
                        </p>
                    </div>
                    <a href="/admin/restaurants?approved=0" class="btn-portal">Review</a>
                </div>
            </template>

            <!-- Quick links -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <a href="/admin/users" class="portal-card text-center hover:opacity-80 transition-opacity" style="text-decoration:none">
                    <p style="font-size:2rem">👥</p>
                    <p class="mt-2 text-sm font-semibold" style="color:var(--color-portal-text)">Manage Users</p>
                </a>
                <a href="/admin/restaurants" class="portal-card text-center hover:opacity-80 transition-opacity" style="text-decoration:none">
                    <p style="font-size:2rem">🍴</p>
                    <p class="mt-2 text-sm font-semibold" style="color:var(--color-portal-text)">Restaurants</p>
                </a>
                <a href="/admin/orders" class="portal-card text-center hover:opacity-80 transition-opacity" style="text-decoration:none">
                    <p style="font-size:2rem">📋</p>
                    <p class="mt-2 text-sm font-semibold" style="color:var(--color-portal-text)">All Orders</p>
                </a>
                <a href="/admin/delivery-partners" class="portal-card text-center hover:opacity-80 transition-opacity" style="text-decoration:none">
                    <p style="font-size:2rem">🛵</p>
                    <p class="mt-2 text-sm font-semibold" style="color:var(--color-portal-text)">Delivery Partners</p>
                </a>
            </div>

            <!-- Recent orders -->
            <div class="portal-card">
                <div class="flex items-center justify-between mb-4">
                    <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text)">Recent Orders</h2>
                    <a href="/admin/orders" class="btn-portal-ghost text-sm">View all</a>
                </div>
                <template x-if="recentOrders.length === 0">
                    <p class="text-sm" style="color:var(--color-portal-muted)">No orders yet.</p>
                </template>
                <div class="flex flex-col gap-2">
                    <template x-for="order in recentOrders" :key="order.id">
                        <div class="flex items-center justify-between p-3 rounded-xl" style="background:var(--color-portal-bg)">
                            <div>
                                <p class="text-sm font-medium" style="color:var(--color-portal-text)">
                                    #<span x-text="order.id"></span> · <span x-text="order.restaurant?.name"></span>
                                </p>
                                <p class="text-xs" style="color:var(--color-portal-muted)">
                                    <span x-text="order.customer?.name"></span> ·
                                    <span x-text="fmtCurrency(order.total)"></span>
                                </p>
                            </div>
                            <span :class="'status-' + order.status" class="badge" x-text="statusLabel(order.status)"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
