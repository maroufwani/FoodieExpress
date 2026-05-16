@extends('layouts.portal')
@section('title', 'Restaurants')
@section('page-title', 'Restaurants')
@section('admin-nav-restaurants', 'active')

@section('sidebar-nav')
    @include('admin._sidebar')
@endsection

@section('content')
<div x-data="adminRestaurants">

    <!-- Filter -->
    <div class="flex gap-3 mb-6 flex-wrap">
        <select x-model="approved" @change="page=1; load()" class="portal-input" style="width:200px">
            <option value="">All restaurants</option>
            <option value="1">Approved</option>
            <option value="0">Pending Approval</option>
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
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Restaurant</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Owner</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">City</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Status</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="restaurants.length === 0">
                    <tr><td colspan="5" class="p-6 text-center" style="color:var(--color-portal-muted)">No restaurants found.</td></tr>
                </template>
                <template x-for="r in restaurants" :key="r.id">
                    <tr style="border-bottom:1px solid var(--color-portal-border)" class="hover:opacity-80 transition-opacity">
                        <td class="p-4">
                            <p class="font-medium" style="color:var(--color-portal-text)" x-text="r.name"></p>
                            <p class="text-xs" style="color:var(--color-portal-muted)" x-text="(r.cuisine_types||[]).slice(0,2).join(', ')"></p>
                        </td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="r.owner?.name || '—'"></td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="r.city"></td>
                        <td class="p-4">
                            <div class="flex gap-1 flex-wrap">
                                <span :class="r.is_approved ? 'badge-delivered' : 'badge-warning'" class="badge"
                                      x-text="r.is_approved ? 'Approved' : 'Pending'"></span>
                                <span :class="r.is_active ? 'badge-delivered' : 'badge-cancelled'" class="badge"
                                      x-text="r.is_active ? 'Active' : 'Inactive'"></span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex gap-2 flex-wrap">
                                <template x-if="!r.is_approved">
                                    <button @click="approve(r)" class="btn-portal text-xs px-3 py-1.5">Approve</button>
                                </template>
                                <button @click="toggle(r)" class="btn-portal-ghost text-xs px-3 py-1.5"
                                        x-text="r.is_active ? 'Deactivate' : 'Activate'"></button>
                                <button @click="deleteRest(r)" class="btn-portal-ghost text-xs px-3 py-1.5" style="color:var(--color-error)">Delete</button>
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
