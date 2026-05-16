@extends('layouts.app')
@section('title', 'My Orders')

@section('content')
<div x-data="customerOrders" class="max-w-3xl mx-auto px-4 py-10">
    <h1 style="font-family:var(--font-display);font-size:2rem;font-weight:800;margin-bottom:1.5rem">My Orders</h1>

    <!-- Status tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2 mb-8 no-scrollbar">
        <template x-for="tab in [{v:'all',l:'All'},{v:'active',l:'Active'},{v:'completed',l:'Completed'},{v:'cancelled',l:'Cancelled'}]" :key="tab.v">
            <button class="tab-btn shrink-0" :class="filter===tab.v ? 'active' : ''"
                    @click="setFilter(tab.v)" x-text="tab.l"></button>
        </template>
    </div>

    <!-- Skeleton -->
    <div x-show="loading" class="flex flex-col gap-4">
        <template x-for="n in 3">
            <div class="card p-5">
                <div class="skeleton h-5 w-1/3 rounded mb-3"></div>
                <div class="skeleton h-4 w-2/3 rounded mb-2"></div>
                <div class="skeleton h-4 w-1/2 rounded"></div>
            </div>
        </template>
    </div>

    <!-- Orders list -->
    <div x-show="!loading" class="flex flex-col gap-4">
        <template x-if="orders.length === 0">
            <div class="text-center py-20">
                <span style="font-size:3.5rem">📦</span>
                <p class="mt-4 text-lg font-semibold" style="color:var(--color-warm-muted)">No orders yet</p>
                <a href="/" class="btn-brand mt-4 inline-block">Browse restaurants</a>
            </div>
        </template>

        <template x-for="(order, idx) in orders" :key="order.id">
            <a :href="'/orders/'+order.id"
               class="card card-hover p-5 block anim-fade-up"
               :style="'animation-delay:' + (idx * 0.06) + 's'"
               style="text-decoration:none;color:inherit">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="font-bold text-sm" style="font-family:var(--font-display)"
                                  x-text="order.restaurant?.name || 'Restaurant'"></span>
                            <span :class="'status-' + order.status" class="badge" x-text="statusLabel(order.status)"></span>
                        </div>
                        <p class="text-sm mb-1" style="color:var(--color-warm-muted)">
                            <span x-text="order.items?.length || order.order_items_count"></span> items ·
                            <span x-text="fmtCurrency(order.total)"></span>
                        </p>
                        <p class="text-xs" style="color:var(--color-warm-muted)" x-text="fmtDateTime(order.created_at)"></p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-warm-muted);flex-shrink:0;margin-top:0.25rem"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </a>
        </template>
    </div>

    <!-- Pagination -->
    <div x-show="lastPage > 1" class="flex justify-center gap-2 mt-8">
        <button @click="page--; load()" :disabled="page<=1" class="btn-outline px-4 py-2">← Prev</button>
        <span class="px-4 py-2 text-sm" style="color:var(--color-warm-muted)">
            Page <span x-text="page"></span> of <span x-text="lastPage"></span>
        </span>
        <button @click="page++; load()" :disabled="page>=lastPage" class="btn-outline px-4 py-2">Next →</button>
    </div>
</div>
@endsection
