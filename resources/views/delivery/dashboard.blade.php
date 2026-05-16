@extends('layouts.portal')
@section('title', 'Delivery Dashboard')
@section('page-title', 'Delivery Dashboard')

@section('sidebar-nav')
    <a href="/delivery/dashboard" class="portal-nav-link active">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>
    <a href="/delivery/history" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 0 .5-4.5"/><polyline points="3 3 3 7 7 7"/></svg>
        History
    </a>
@endsection

@section('content')
<div x-data="deliveryDashboard">

    <!-- Skeleton -->
    <div x-show="loading">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <template x-for="n in 4"><div class="skeleton h-28 rounded-xl"></div></template>
        </div>
    </div>

    <template x-if="!loading && stats">
        <div>
            <!-- Availability toggle -->
            <div class="portal-card mb-6 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="font-semibold" style="color:var(--color-portal-text)">Availability Status</p>
                    <p class="text-sm" style="color:var(--color-portal-muted)">
                        You are currently
                        <strong :style="stats.is_available ? 'color:#22c55e' : 'color:var(--color-portal-muted)'"
                                x-text="stats.is_available ? 'ONLINE' : 'OFFLINE'"></strong>
                    </p>
                </div>
                <button @click="toggleAvailability()"
                        :class="stats.is_available ? 'btn-portal-ghost' : 'btn-portal'"
                        class="px-6 py-2.5">
                    <span x-text="stats.is_available ? 'Go Offline' : 'Go Online'"></span>
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="stat-card">
                    <p class="stat-label">Today's Deliveries</p>
                    <p class="stat-value" x-text="stats.today_deliveries"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Today's Earnings</p>
                    <p class="stat-value" x-text="fmtCurrency(stats.today_earnings)"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Total Deliveries</p>
                    <p class="stat-value" x-text="stats.total_deliveries"></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Rating</p>
                    <p class="stat-value">⭐ <span x-text="parseFloat(stats.rating||0).toFixed(1)"></span></p>
                </div>
            </div>

            <!-- Available orders -->
            <div class="portal-card mb-6">
                <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);margin-bottom:1rem">
                    Available Orders (<span x-text="availableOrders.length"></span>)
                </h2>

                <template x-if="availableOrders.length === 0">
                    <p class="text-sm" style="color:var(--color-portal-muted)">No orders available right now. Check back in a moment.</p>
                </template>

                <template x-if="!stats.is_available">
                    <div class="flex items-center gap-2 p-3 rounded-lg mb-3 text-sm"
                         style="background:rgba(245,158,11,0.1);color:#d97706;border:1px solid rgba(245,158,11,0.2)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        You must go <strong class="mx-1">Online</strong> to accept orders.
                    </div>
                </template>

                <div class="flex flex-col gap-3">
                    <template x-for="order in availableOrders" :key="order.id">
                        <div class="flex items-center justify-between p-4 rounded-xl" style="background:var(--color-portal-bg)">
                            <div>
                                <p class="font-medium text-sm" style="color:var(--color-portal-text)">
                                    Order #<span x-text="order.id"></span> · <span x-text="order.restaurant?.name"></span>
                                </p>
                                <p class="text-xs mt-0.5" style="color:var(--color-portal-muted)">
                                    <span x-text="fmtCurrency(order.total)"></span> ·
                                    <span x-text="(order.items?.length || 0)"></span> items
                                    <template x-if="order.distance_km">
                                        · <span x-text="parseFloat(order.distance_km).toFixed(1) + ' km'"></span>
                                    </template>
                                </p>
                            </div>
                            <button @click="accept(order)"
                                    :disabled="acceptingId === order.id || !stats.is_available"
                                    :title="!stats.is_available ? 'Go online first' : ''"
                                    class="btn-portal flex items-center gap-2 text-sm">
                                <span x-show="acceptingId === order.id" class="spinner" style="width:12px;height:12px"></span>
                                <span x-text="acceptingId === order.id ? 'Accepting…' : 'Accept'"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Active deliveries -->
            <div class="portal-card" x-show="activeDeliveries.length > 0">
                <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);margin-bottom:1rem">
                    Active Deliveries
                </h2>
                <div class="flex flex-col gap-3">
                    <template x-for="order in activeDeliveries" :key="order.id">
                        <div class="flex items-center justify-between p-4 rounded-xl" style="background:var(--color-portal-bg)">
                            <div>
                                <p class="font-medium text-sm" style="color:var(--color-portal-text)">
                                    Order #<span x-text="order.id"></span>
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span :class="'status-' + order.status" class="badge"
                                          x-text="statusLabel(order.status)"></span>
                                    <span class="text-xs" style="color:var(--color-portal-muted)"
                                          x-text="order.customer?.name"></span>
                                </div>
                            </div>
                            <button @click="advance(order)" class="btn-portal text-sm px-4 py-2">
                                Update Status →
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <p class="text-xs mt-6" style="color:var(--color-portal-muted);text-align:right">Auto-refreshes every 15s</p>
</div>
@endsection
