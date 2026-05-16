@extends('layouts.portal')
@section('title', 'Delivery Partners')
@section('page-title', 'Delivery Partners')
@section('admin-nav-delivery', 'active')

@section('sidebar-nav')
    @include('admin._sidebar')
@endsection

@section('content')
<div x-data="adminDeliveryPartners">

    <!-- Filter -->
    <div class="flex gap-3 mb-6">
        <select x-model="verified" @change="page=1; load()" class="portal-input" style="width:200px">
            <option value="">All partners</option>
            <option value="1">Verified</option>
            <option value="0">Unverified</option>
        </select>
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
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Partner</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Vehicle</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Rating</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Deliveries</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Status</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="partners.length === 0">
                    <tr><td colspan="6" class="p-6 text-center" style="color:var(--color-portal-muted)">No delivery partners found.</td></tr>
                </template>
                <template x-for="p in partners" :key="p.id">
                    <tr style="border-bottom:1px solid var(--color-portal-border)" class="hover:opacity-80 transition-opacity">
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                     style="background:var(--color-brand)"
                                     x-text="(p.name||'D')[0].toUpperCase()"></div>
                                <div>
                                    <p class="font-medium" style="color:var(--color-portal-text)" x-text="p.name"></p>
                                    <p class="text-xs" style="color:var(--color-portal-muted)" x-text="p.email"></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 capitalize" style="color:var(--color-portal-muted)" x-text="p.vehicle_type || '—'"></td>
                        <td class="p-4" style="color:var(--color-portal-text)">⭐ <span x-text="parseFloat(p.rating||0).toFixed(1)"></span></td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="p.total_deliveries || 0"></td>
                        <td class="p-4">
                            <div class="flex gap-1 flex-wrap">
                                <span :class="p.is_verified ? 'badge-delivered' : 'badge-warning'" class="badge"
                                      x-text="p.is_verified ? 'Verified' : 'Unverified'"></span>
                                <span :class="p.is_available ? 'badge-delivered' : 'badge-cancelled'" class="badge"
                                      x-text="p.is_available ? 'Online' : 'Offline'"></span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-2 flex-wrap">
                                <template x-if="!p.is_verified">
                                    <button @click="verify(p)" class="btn-portal text-xs px-3 py-1.5">Verify</button>
                                </template>
                                <button @click="toggleAvail(p)" class="btn-portal-ghost text-xs px-3 py-1.5"
                                        x-text="p.is_available ? 'Force Offline' : 'Force Online'"></button>
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
</div>
@endsection
