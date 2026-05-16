@extends('layouts.portal')
@section('title', 'Delivery History')
@section('page-title', 'Delivery History')

@section('sidebar-nav')
    <a href="/delivery/dashboard" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>
    <a href="/delivery/history" class="portal-nav-link active">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 0 .5-4.5"/><polyline points="3 3 3 7 7 7"/></svg>
        History
    </a>
@endsection

@section('content')
<div x-data="deliveryHistory">

    <!-- Skeleton -->
    <div x-show="loading" class="flex flex-col gap-3">
        <template x-for="n in 5"><div class="skeleton h-20 rounded-xl"></div></template>
    </div>

    <!-- List -->
    <div x-show="!loading" class="portal-card">
        <template x-if="orders.length === 0">
            <div class="text-center py-16">
                <span style="font-size:3rem">🛵</span>
                <p class="mt-4" style="color:var(--color-portal-muted)">No completed deliveries yet.</p>
            </div>
        </template>

        <div class="flex flex-col gap-3">
            <template x-for="order in orders" :key="order.id">
                <div class="flex items-center justify-between p-4 rounded-xl" style="background:var(--color-portal-bg)">
                    <div>
                        <p class="font-medium text-sm" style="color:var(--color-portal-text)">
                            Order #<span x-text="order.id"></span> · <span x-text="order.restaurant?.name"></span>
                        </p>
                        <p class="text-xs mt-0.5" style="color:var(--color-portal-muted)">
                            <span x-text="fmtCurrency(order.total)"></span> ·
                            <span x-text="fmtDate(order.delivered_at || order.updated_at)"></span>
                        </p>
                    </div>
                    <span :class="'status-' + order.status" class="badge" x-text="statusLabel(order.status)"></span>
                </div>
            </template>
        </div>
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
