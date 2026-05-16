@extends('layouts.portal')
@section('title', 'Orders')
@section('page-title', 'All Orders')
@section('admin-nav-orders', 'active')

@section('sidebar-nav')
    @include('admin._sidebar')
@endsection

@section('content')
<div x-data="adminOrders">

    <!-- Status filter -->
    <div class="flex gap-2 overflow-x-auto pb-3 mb-6 no-scrollbar">
        <template x-for="tab in [{v:'',l:'All'},{v:'pending',l:'Pending'},{v:'confirmed',l:'Confirmed'},{v:'preparing',l:'Preparing'},{v:'out_for_delivery',l:'Out for Delivery'},{v:'delivered',l:'Delivered'},{v:'cancelled',l:'Cancelled'}]" :key="tab.v">
            <button class="tab-btn shrink-0" :class="status===tab.v ? 'active' : ''"
                    @click="status=tab.v; page=1; load()" x-text="tab.l"></button>
        </template>
    </div>

    <!-- Table -->
    <div class="portal-card overflow-x-auto">
        <div x-show="loading" class="p-6">
            <div class="flex flex-col gap-3">
                <template x-for="n in 5"><div class="skeleton h-14 rounded-lg"></div></template>
            </div>
        </div>

        <table x-show="!loading" class="w-full text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--color-portal-border)">
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Order</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Customer</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Restaurant</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Amount</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Status</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Date</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="orders.length === 0">
                    <tr><td colspan="7" class="p-6 text-center" style="color:var(--color-portal-muted)">No orders found.</td></tr>
                </template>
                <template x-for="order in orders" :key="order.id">
                    <tr style="border-bottom:1px solid var(--color-portal-border)" class="hover:opacity-80 transition-opacity">
                        <td class="p-4 font-medium" style="color:var(--color-portal-text)">#<span x-text="order.id"></span></td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="order.customer?.name || '—'"></td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="order.restaurant?.name || '—'"></td>
                        <td class="p-4" style="color:var(--color-portal-text)" x-text="fmtCurrency(order.total)"></td>
                        <td class="p-4">
                            <span :class="'status-' + order.status" class="badge" x-text="statusLabel(order.status)"></span>
                        </td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="fmtDate(order.created_at)"></td>
                        <td class="p-4">
                            <template x-if="!['delivered','cancelled'].includes(order.status)">
                                <button @click="cancel(order)" class="btn-portal-ghost text-xs px-3 py-1.5" style="color:var(--color-error)">Cancel</button>
                            </template>
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
</div>
@endsection
