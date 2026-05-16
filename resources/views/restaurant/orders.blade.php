@extends('layouts.portal')
@section('title', 'Orders')
@section('page-title', 'Orders')

@section('sidebar-nav')
    <a href="/restaurant/dashboard" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>
    <a href="/restaurant/orders" class="portal-nav-link active">
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
<div x-data="restaurantOrders">

    <!-- Status tabs (dynamic from restaurant's flow) -->
    <div class="flex gap-2 overflow-x-auto pb-3 mb-6 no-scrollbar">
        <template x-for="tab in allTabs" :key="tab.v">
            <button class="tab-btn shrink-0" :class="status===tab.v ? 'active' : ''"
                    @click="setStatus(tab.v)" x-text="tab.l"></button>
        </template>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex flex-col gap-3">
        <template x-for="n in 5"><div class="skeleton h-20 rounded-xl"></div></template>
    </div>

    <!-- Table -->
    <div x-show="!loading" class="portal-card overflow-x-auto">
        <template x-if="orders.length === 0">
            <p class="p-6 text-sm" style="color:var(--color-portal-muted)">No orders match this filter.</p>
        </template>

        <table x-show="orders.length > 0" class="w-full text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--color-portal-border)">
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Order</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Customer</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Amount</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Status</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Time</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="order in orders" :key="order.id">
                    <tr style="border-bottom:1px solid var(--color-portal-border)" class="hover:opacity-80 transition-opacity">
                        <td class="p-4 font-medium" style="color:var(--color-portal-text)">#<span x-text="order.id"></span></td>
                        <td class="p-4" style="color:var(--color-portal-text)" x-text="order.customer?.name || '—'"></td>
                        <td class="p-4" style="color:var(--color-portal-text)" x-text="fmtCurrency(order.total)"></td>
                        <td class="p-4">
                            <span :class="'status-' + order.status" class="badge" x-text="flowLabel(order.status)"></span>
                        </td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="fmtDateTime(order.created_at)"></td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <button @click="viewOrder(order)" class="btn-portal-ghost text-xs px-3 py-1.5">View</button>
                                <template x-if="nextStatusFor(order)">
                                    <button @click="advance(order)" class="btn-portal text-xs px-3 py-1.5">
                                        <span x-text="nextStatusFor(order)?.label + ' →'"></span>
                                    </button>
                                </template>
                                <template x-if="isRestaurantControlled(order.status) && order.status !== 'cancelled'">
                                    <button @click="cancel(order)" class="btn-portal-ghost text-xs px-3 py-1.5" style="color:var(--color-error)">Cancel</button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div x-show="lastPage > 1" class="flex justify-center gap-2 mt-6">
        <button @click="page--; load()" :disabled="page<=1" class="btn-portal-ghost px-4 py-2">← Prev</button>
        <span class="px-4 py-2 text-sm" style="color:var(--color-portal-muted)">
            Page <span x-text="page"></span> / <span x-text="lastPage"></span>
        </span>
        <button @click="page++; load()" :disabled="page>=lastPage" class="btn-portal-ghost px-4 py-2">Next →</button>
    </div>

    <!-- Order detail modal -->
    <div x-show="selectedOrder" x-cloak class="modal-backdrop" style="z-index:150">
        <div class="portal-modal-box">
            <div class="flex items-center justify-between mb-4">
                <h3 style="font-family:var(--font-display);font-weight:700;font-size:1.2rem;color:var(--color-portal-text)">
                    Order #<span x-text="selectedOrder?.id"></span>
                </h3>
                <button @click="selectedOrder=null" class="btn-portal-ghost px-3 py-1.5">&times;</button>
            </div>
            <template x-if="selectedOrder">
                <div class="text-sm" style="color:var(--color-portal-text)">
                    <div class="flex justify-between mb-3">
                        <span style="color:var(--color-portal-muted)">Customer</span>
                        <span x-text="selectedOrder.customer?.name"></span>
                    </div>
                    <div class="flex justify-between mb-3">
                        <span style="color:var(--color-portal-muted)">Status</span>
                        <span :class="'status-' + selectedOrder.status" class="badge" x-text="flowLabel(selectedOrder.status)"></span>
                    </div>
                    <hr style="border-color:var(--color-portal-border);margin:0.75rem 0">
                    <div class="flex flex-col gap-2 mb-3">
                        <template x-for="item in selectedOrder.items" :key="item.id">
                            <div>
                                <div class="flex justify-between">
                                    <span x-text="item.quantity + '× ' + item.name"></span>
                                    <span x-text="fmtCurrency(item.price * item.quantity)"></span>
                                </div>
                                <template x-if="item.extras && item.extras.length > 0">
                                    <ul class="mt-1 flex flex-col gap-0.5 pl-4">
                                        <template x-for="extra in item.extras" :key="extra.id">
                                            <li class="flex justify-between text-xs" style="color:var(--color-portal-muted)">
                                                <span x-text="'＋ ' + extra.name + (extra.size ? ' (' + extra.size + ')' : '')"></span>
                                                <span x-text="'+' + fmtCurrency(extra.price * item.quantity)"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                            </div>
                        </template>
                    </div>
                    <hr style="border-color:var(--color-portal-border);margin:0.75rem 0">
                    <div class="flex justify-between font-bold">
                        <span>Total</span>
                        <span x-text="fmtCurrency(selectedOrder.total)"></span>
                    </div>
                    <template x-if="selectedOrder.special_instructions">
                        <p class="mt-3 p-3 rounded-lg text-xs" style="background:var(--color-portal-bg);color:var(--color-portal-muted)">
                            📝 <span x-text="selectedOrder.special_instructions"></span>
                        </p>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <p class="text-xs mt-4" style="color:var(--color-portal-muted);text-align:right">Auto-refreshes every 30s</p>
</div>
@endsection
