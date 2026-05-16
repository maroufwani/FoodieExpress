@extends('layouts.portal')
@section('title', 'Delivery Partners')
@section('page-title', 'Delivery Partners')

@section('sidebar-nav')
    <a href="/restaurant/dashboard" class="portal-nav-link">
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
    <a href="/restaurant/delivery-partners" class="portal-nav-link active">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        Delivery Partners
    </a>
    <a href="/restaurant/settings" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Settings
    </a>
@endsection

@section('content')
<div x-data="restaurantDeliveryPartners">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-semibold" style="color:var(--color-portal-text)">Your Delivery Partners</h2>
            <p class="text-sm mt-0.5" style="color:var(--color-portal-muted)">Partners you add are exclusively assigned to your restaurant.</p>
        </div>
        <button @click="openAdd()" class="btn-portal flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Partner
        </button>
    </div>

    <!-- Loading skeleton -->
    <div x-show="loading" class="flex flex-col gap-3">
        <template x-for="n in 4"><div class="skeleton h-16 rounded-xl"></div></template>
    </div>

    <!-- Empty state -->
    <template x-if="!loading && partners.length === 0">
        <div class="portal-card flex flex-col items-center justify-center py-16 text-center">
            <span style="font-size:3rem">🛵</span>
            <p class="mt-3 font-semibold" style="color:var(--color-portal-text)">No delivery partners yet</p>
            <p class="text-sm mt-1" style="color:var(--color-portal-muted)">Add your first delivery partner to get started.</p>
            <button @click="openAdd()" class="btn-portal mt-5">Add Partner</button>
        </div>
    </template>

    <!-- Partners table -->
    <template x-if="!loading && partners.length > 0">
        <div class="portal-card overflow-x-auto">
            <table class="w-full text-sm">
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
                    <template x-for="p in partners" :key="p.id">
                        <tr style="border-bottom:1px solid var(--color-portal-border)">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
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
                                    <span :class="p.is_active ? 'badge-delivered' : 'badge-cancelled'" class="badge"
                                          x-text="p.is_active ? 'Active' : 'Inactive'"></span>
                                    <span :class="p.is_available ? 'badge-delivered' : ''" class="badge"
                                          style="background:rgba(59,130,246,0.1);color:#3b82f6"
                                          x-show="p.is_available">Online</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-2 flex-wrap">
                                    <button @click="openEdit(p)" class="btn-portal-ghost text-xs px-3 py-1.5">Edit</button>
                                    <button @click="remove(p)" class="text-xs px-3 py-1.5 rounded-lg transition-colors"
                                            style="color:var(--color-error);border:1px solid rgba(239,68,68,0.3)"
                                            onmouseover="this.style.background='rgba(239,68,68,0.08)'"
                                            onmouseout="this.style.background=''">Remove</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>

    <!-- ── Add / Edit Modal ── -->
    <template x-if="modal.open">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.6)">
            <div class="w-full max-w-md rounded-2xl p-6" style="background:var(--color-portal-surface);border:1px solid var(--color-portal-border)"
                 @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-semibold text-base" style="color:var(--color-portal-text)"
                        x-text="modal.editing ? 'Edit Delivery Partner' : 'Add Delivery Partner'"></h3>
                    <button @click="modal.open=false" style="color:var(--color-portal-muted)">&times;</button>
                </div>

                <form @submit.prevent="save()" class="flex flex-col gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:var(--color-portal-muted)">Full Name</label>
                        <input type="text" x-model="modal.name" required class="portal-input w-full" placeholder="Jane Doe">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:var(--color-portal-muted)">Email</label>
                        <input type="email" x-model="modal.email" :disabled="modal.editing" required class="portal-input w-full" placeholder="partner@example.com">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:var(--color-portal-muted)">Phone</label>
                        <input type="tel" x-model="modal.phone" required class="portal-input w-full" placeholder="+1 555 000 0000">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:var(--color-portal-muted)">Vehicle Type</label>
                        <select x-model="modal.vehicle_type" required class="portal-input w-full">
                            <option value="">Select vehicle…</option>
                            <option value="bicycle">Bicycle</option>
                            <option value="motorcycle">Motorcycle</option>
                            <option value="car">Car</option>
                            <option value="scooter">Scooter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1" style="color:var(--color-portal-muted)">
                            Password <span x-show="modal.editing" style="color:var(--color-portal-muted);font-weight:400">(leave blank to keep current)</span>
                        </label>
                        <input type="password" x-model="modal.password" :required="!modal.editing" class="portal-input w-full" placeholder="Min. 6 characters">
                    </div>
                    <template x-if="modal.editing">
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-semibold" style="color:var(--color-portal-muted)">Active</label>
                            <button type="button" @click="modal.is_active = !modal.is_active"
                                    class="relative w-10 h-5 rounded-full transition-colors"
                                    :style="modal.is_active ? 'background:var(--color-brand)' : 'background:var(--color-portal-border)'">
                                <span class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                                      :style="modal.is_active ? 'transform:translateX(20px)' : 'transform:translateX(2px)'"></span>
                            </button>
                        </div>
                    </template>

                    <div class="flex gap-3 justify-end mt-2">
                        <button type="button" @click="modal.open=false" class="btn-portal-ghost px-5">Cancel</button>
                        <button type="submit" class="btn-portal px-5 flex items-center gap-2" :disabled="modal.saving">
                            <span x-show="modal.saving" class="spinner w-4 h-4"></span>
                            <span x-text="modal.editing ? 'Save Changes' : 'Add Partner'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
