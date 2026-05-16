@extends('layouts.portal')
@section('title', 'Restaurant Settings')
@section('page-title', 'Restaurant Settings')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

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
    <a href="/restaurant/delivery-partners" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        Delivery Partners
    </a>
    <a href="/restaurant/settings" class="portal-nav-link active">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Settings
    </a>
@endsection

@section('content')
<div x-data="restaurantSettings">
    <div x-show="loading" class="flex flex-col gap-4">
        <div class="skeleton h-40 rounded-xl"></div>
        <div class="skeleton h-60 rounded-xl"></div>
    </div>

    <template x-if="!loading">
        <form @submit.prevent="save()" class="flex flex-col gap-6">

            <!-- Basic info -->
            <div class="portal-card">
                <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);margin-bottom:1.25rem">Basic Info</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label">Restaurant Name *</label>
                        <input type="text" x-model="form.name" required class="portal-input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="label">Description</label>
                        <textarea x-model="form.description" rows="3" class="portal-input resize-none"></textarea>
                    </div>
                    <div>
                        <label class="label">Contact Phone</label>
                        <input type="tel" x-model="form.phone" class="portal-input">
                    </div>
                    <div>
                        <label class="label">Contact Email</label>
                        <input type="email" x-model="form.email" class="portal-input">
                    </div>
                    <div>
                        <label class="label">Est. Delivery Time (min)</label>
                        <input type="number" min="5" x-model="form.estimated_delivery_time" class="portal-input">
                    </div>
                    <div>
                        <label class="label">Cover Photo</label>
                        <input type="file" accept="image/*" @change="imageFile = $event.target.files[0]" class="portal-input">
                    </div>
                </div>
            </div>

            <!-- Cuisine types -->
            <div class="portal-card">
                <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);margin-bottom:1rem">Cuisine Types</h2>
                <div class="flex gap-2 flex-wrap mb-3">
                    <template x-for="c in form.cuisine_types" :key="c">
                        <span class="flex items-center gap-1 px-3 py-1 rounded-full text-sm"
                              style="background:rgba(232,98,26,0.15);color:var(--color-brand)">
                            <span x-text="c"></span>
                            <button type="button" @click="removeCuisine(c)" class="ml-1 opacity-60 hover:opacity-100">&times;</button>
                        </span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <input type="text" x-model="cuisineInput" @keydown.enter.prevent="addCuisine()"
                           placeholder="e.g. Italian" class="portal-input flex-1">
                    <button type="button" @click="addCuisine()" class="btn-portal">Add</button>
                </div>
            </div>

            <!-- Location & delivery -->
            <div class="portal-card">
                <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);margin-bottom:1.25rem">Location & Delivery</h2>

                <!-- Map search bar -->
                <div class="relative mb-3">
                    <div class="flex gap-2">
                        <input type="text" x-model="mapSearch"
                               @keydown.enter.prevent="geocodeSearch()"
                               class="portal-input flex-1" placeholder="Search your restaurant address…">
                        <button type="button" @click="geocodeSearch()" :disabled="geocoding"
                                class="btn-portal shrink-0 flex items-center gap-2" style="min-width:110px;justify-content:center">
                            <span x-show="geocoding" class="spinner" style="width:14px;height:14px;border-width:2px"></span>
                            <span x-show="!geocoding">Search</span>
                        </button>
                        <button type="button" @click="useMyLocation()" :disabled="locatingGps"
                                class="btn-portal-outline shrink-0 flex items-center gap-2 px-3" style="min-width:130px;justify-content:center">
                            <template x-if="!locatingGps">
                                <span class="flex items-center gap-2 text-sm font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M1 12h4M19 12h4"/></svg>
                                    My Location
                                </span>
                            </template>
                            <template x-if="locatingGps">
                                <span class="flex items-center gap-2 text-sm font-semibold">
                                    <span class="spinner" style="width:13px;height:13px;border-width:2px"></span>
                                    Locating…
                                </span>
                            </template>
                        </button>
                    </div>
                    <!-- Geocode results dropdown -->
                    <div x-show="geocodeResults.length > 0"
                         class="absolute left-0 right-0 mt-1 rounded-xl shadow-xl overflow-hidden"
                         style="background:#fff;border:1px solid var(--color-portal-border);z-index:1500;top:100%">
                        <div class="flex justify-end px-2 pt-1.5 pb-0.5">
                            <button type="button" @click="geocodeResults = []"
                                    class="text-xs font-semibold flex items-center gap-1 px-2 py-0.5 rounded-md"
                                    style="color:var(--color-portal-muted);background:var(--color-portal-bg)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Close
                            </button>
                        </div>
                        <template x-for="(r, i) in geocodeResults" :key="i">
                            <button type="button" @click="selectGeocode(r)"
                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50"
                                    style="border-bottom:1px solid var(--color-portal-border)"
                                    x-text="r.display_name"></button>
                        </template>
                    </div>
                </div>

                <!-- Map -->
                <div id="restaurant-map" style="height:300px;border-radius:0.75rem;overflow:hidden;border:1px solid var(--color-portal-border);margin-bottom:1rem"></div>

                <!-- Detected address + coords -->
                <div x-show="form.street || form.latitude" class="flex items-start gap-2 text-sm mb-4 px-1" style="color:var(--color-portal-muted)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;shrink:0;color:var(--color-brand)"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span x-text="[form.street, form.city, form.state].filter(Boolean).join(', ') || ('Lat: ' + form.latitude + ', Lng: ' + form.longitude)"></span>
                </div>
                <p class="text-xs mb-4 px-1" style="color:var(--color-portal-muted)">Click on the map or drag the pin to set the exact restaurant location.</p>

                <!-- Address fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="label">Street</label>
                        <input type="text" x-model="form.street" class="portal-input">
                    </div>
                    <div>
                        <label class="label">City</label>
                        <input type="text" x-model="form.city" class="portal-input">
                    </div>
                    <div>
                        <label class="label">State</label>
                        <input type="text" x-model="form.state" class="portal-input">
                    </div>
                    <div>
                        <label class="label">Zip Code</label>
                        <input type="text" x-model="form.zip_code" class="portal-input">
                    </div>
                </div>

                <!-- Delivery radius slider -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <label class="label mb-0">Delivery Radius</label>
                        <span class="text-sm font-bold" style="color:var(--color-brand)" x-text="form.delivery_radius + ' km'"></span>
                    </div>
                    <input type="range" min="0.5" max="30" step="0.5" x-model="form.delivery_radius"
                           @input="updateRadiusCircle()"
                           class="w-full" style="accent-color:var(--color-brand)">
                    <div class="flex justify-between text-xs mt-1" style="color:var(--color-portal-muted)">
                        <span>0.5 km</span><span>30 km</span>
                    </div>
                </div>

                <!-- Delivery fee + min order -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Delivery Fee (₹)</label>
                        <input type="number" step="0.01" min="0" x-model="form.delivery_fee" class="portal-input">
                    </div>
                    <div>
                        <label class="label">Minimum Order (₹)</label>
                        <input type="number" step="0.01" min="0" x-model="form.min_order_amount" class="portal-input">
                    </div>
                </div>
            </div>

            <!-- Opening hours -->
            <div class="portal-card">
                <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);margin-bottom:1.25rem">Opening Hours</h2>
                <div class="flex flex-col gap-3">
                    <template x-for="day in days" :key="day">
                        <div class="flex items-center gap-4">
                            <span class="w-28 capitalize text-sm" style="color:var(--color-portal-text)" x-text="day"></span>
                            <div class="flex items-center gap-2">
                                <input type="time" x-model="hours[day].open" class="portal-input" style="width:8rem;padding:0.4rem 0.6rem">
                                <span style="color:var(--color-portal-muted)">—</span>
                                <input type="time" x-model="hours[day].close" class="portal-input" style="width:8rem;padding:0.4rem 0.6rem">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Order Status Flow -->
            <div class="portal-card">
                <h2 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);margin-bottom:0.4rem">Order Status Flow</h2>
                <p class="text-sm mb-5" style="color:var(--color-portal-muted)">Define every status your orders progress through — from preparation through delivery. Both sections are fully customisable.</p>

                <!-- ── Restaurant Steps ── -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-semibold uppercase tracking-widest px-2 py-0.5 rounded-full" style="background:rgba(232,98,26,0.12);color:var(--color-brand)">Restaurant</span>
                        <span class="text-xs" style="color:var(--color-portal-muted)">Steps your kitchen manages</span>
                    </div>
                    <div class="flex flex-col gap-2 mb-4">
                        <template x-for="(step, idx) in restaurantSteps" :key="step.key">
                            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl"
                                 style="background:var(--color-portal-surface-2);border:1px solid var(--color-portal-border)">
                                <span class="text-xs font-bold w-5 text-center flex-shrink-0"
                                      style="color:var(--color-portal-muted)" x-text="idx + 1"></span>
                                <!-- Up / Down (within restaurant steps only) -->
                                <div class="flex flex-col gap-0.5 flex-shrink-0">
                                    <button type="button" @click="moveFlowItem(step.key,'up')"
                                            :disabled="idx <= 0"
                                            class="text-xs leading-none px-1 rounded hover:bg-white/5 disabled:opacity-25"
                                            style="color:var(--color-portal-muted)">▲</button>
                                    <button type="button" @click="moveFlowItem(step.key,'down')"
                                            :disabled="idx >= restaurantSteps.length - 1"
                                            class="text-xs leading-none px-1 rounded hover:bg-white/5 disabled:opacity-25"
                                            style="color:var(--color-portal-muted)">▼</button>
                                </div>
                                <!-- Label -->
                                <template x-if="step.key === 'pending'">
                                    <span class="flex-1 text-sm font-medium" style="color:var(--color-portal-text)" x-text="step.label"></span>
                                </template>
                                <template x-if="step.key !== 'pending'">
                                    <input type="text" x-model="step.label"
                                           class="flex-1 text-sm font-medium bg-transparent outline-none border-b border-transparent focus:border-brand"
                                           style="color:var(--color-portal-text);border-bottom-color:transparent"
                                           @focus="$event.target.style.borderBottomColor='var(--color-brand)'"
                                           @blur="$event.target.style.borderBottomColor='transparent'">
                                </template>
                                <code class="text-xs px-1.5 py-0.5 rounded flex-shrink-0"
                                      style="background:rgba(232,98,26,0.1);color:var(--color-brand)" x-text="step.key"></code>
                                <template x-if="step.key === 'pending'">
                                    <span style="color:var(--color-portal-muted);font-size:0.75rem" title="Required">🔒</span>
                                </template>
                                <template x-if="step.key !== 'pending'">
                                    <button type="button" @click="removeFlowItem(step.key)"
                                            class="text-xs flex-shrink-0 opacity-50 hover:opacity-100 transition-opacity"
                                            style="color:var(--color-error)">✕</button>
                                </template>
                            </div>
                        </template>
                    </div>
                    <!-- Restaurant presets -->
                    <template x-if="availableRestaurantPresets.length > 0">
                        <div class="flex gap-2 flex-wrap mb-3">
                            <span class="text-xs self-center" style="color:var(--color-portal-muted)">Add preset:</span>
                            <template x-for="preset in availableRestaurantPresets" :key="preset.key">
                                <button type="button" @click="addFlowPreset(preset)"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors"
                                        style="background:rgba(232,98,26,0.1);color:var(--color-brand);border:1px dashed rgba(232,98,26,0.4)">
                                    + <span x-text="preset.label"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                    <!-- Restaurant custom -->
                    <div class="flex gap-2">
                        <input type="text" x-model="customRestaurantLabel"
                               @keydown.enter.prevent="addRestaurantCustom()"
                               placeholder="Custom step, e.g. Quality Check"
                               class="portal-input flex-1 text-sm">
                        <button type="button" @click="addRestaurantCustom()"
                                :disabled="!customRestaurantLabel.trim()"
                                class="btn-portal text-sm px-4 disabled:opacity-50">+ Add</button>
                    </div>
                </div>

                <!-- Divider -->
                <div class="flex items-center gap-3 my-5">
                    <div class="flex-1 h-px" style="background:var(--color-portal-border)"></div>
                    <span class="text-xs" style="color:var(--color-portal-muted)">handoff to delivery partner</span>
                    <div class="flex-1 h-px" style="background:var(--color-portal-border)"></div>
                </div>

                <!-- ── Delivery Steps ── -->
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-semibold uppercase tracking-widest px-2 py-0.5 rounded-full" style="background:rgba(59,130,246,0.12);color:#3b82f6">Delivery</span>
                        <span class="text-xs" style="color:var(--color-portal-muted)">Steps the delivery partner manages</span>
                    </div>
                    <div class="flex flex-col gap-2 mb-4">
                        <template x-for="(step, idx) in deliverySteps" :key="step.key">
                            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl"
                                 style="background:var(--color-portal-surface-2);border:1px solid var(--color-portal-border)">
                                <span class="text-xs font-bold w-5 text-center flex-shrink-0"
                                      style="color:var(--color-portal-muted)" x-text="idx + 1"></span>
                                <div class="flex flex-col gap-0.5 flex-shrink-0">
                                    <button type="button" @click="moveFlowItem(step.key,'up')"
                                            :disabled="idx <= 0"
                                            class="text-xs leading-none px-1 rounded hover:bg-white/5 disabled:opacity-25"
                                            style="color:var(--color-portal-muted)">▲</button>
                                    <button type="button" @click="moveFlowItem(step.key,'down')"
                                            :disabled="idx >= deliverySteps.length - 1"
                                            class="text-xs leading-none px-1 rounded hover:bg-white/5 disabled:opacity-25"
                                            style="color:var(--color-portal-muted)">▼</button>
                                </div>
                                <input type="text" x-model="step.label"
                                       class="flex-1 text-sm font-medium bg-transparent outline-none border-b border-transparent focus:border-brand"
                                       style="color:var(--color-portal-text);border-bottom-color:transparent"
                                       @focus="$event.target.style.borderBottomColor='var(--color-brand)'"
                                       @blur="$event.target.style.borderBottomColor='transparent'">
                                <code class="text-xs px-1.5 py-0.5 rounded flex-shrink-0"
                                      style="background:rgba(59,130,246,0.1);color:#3b82f6" x-text="step.key"></code>
                                <button type="button" @click="removeFlowItem(step.key)"
                                        class="text-xs flex-shrink-0 opacity-50 hover:opacity-100 transition-opacity"
                                        style="color:var(--color-error)">✕</button>
                            </div>
                        </template>
                        <!-- Delivered — always last, locked -->
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl"
                             style="background:var(--color-portal-surface-2);border:1px solid var(--color-portal-border);opacity:0.75">
                            <span class="text-xs font-bold w-5 text-center flex-shrink-0" style="color:var(--color-portal-muted)"
                                  x-text="deliverySteps.length + 1"></span>
                            <div class="flex flex-col gap-0.5 flex-shrink-0">
                                <button type="button" disabled class="text-xs leading-none px-1 rounded disabled:opacity-25" style="color:var(--color-portal-muted)">▲</button>
                                <button type="button" disabled class="text-xs leading-none px-1 rounded disabled:opacity-25" style="color:var(--color-portal-muted)">▼</button>
                            </div>
                            <span class="flex-1 text-sm font-medium" style="color:var(--color-portal-text)"
                                  x-text="statusFlow.find(s => s.key === 'delivered')?.label ?? 'Delivered'"></span>
                            <code class="text-xs px-1.5 py-0.5 rounded flex-shrink-0"
                                  style="background:rgba(59,130,246,0.1);color:#3b82f6">delivered</code>
                            <span style="color:var(--color-portal-muted);font-size:0.75rem" title="Required">🔒</span>
                        </div>
                    </div>
                    <!-- Delivery presets -->
                    <template x-if="availableDeliveryPresets.length > 0">
                        <div class="flex gap-2 flex-wrap mb-3">
                            <span class="text-xs self-center" style="color:var(--color-portal-muted)">Add preset:</span>
                            <template x-for="preset in availableDeliveryPresets" :key="preset.key">
                                <button type="button" @click="addFlowPreset(preset)"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium transition-colors"
                                        style="background:rgba(59,130,246,0.1);color:#3b82f6;border:1px dashed rgba(59,130,246,0.4)">
                                    + <span x-text="preset.label"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                    <!-- Delivery custom -->
                    <div class="flex gap-2">
                        <input type="text" x-model="customDeliveryLabel"
                               @keydown.enter.prevent="addDeliveryCustom()"
                               placeholder="Custom delivery step, e.g. At Your Building"
                               class="portal-input flex-1 text-sm">
                        <button type="button" @click="addDeliveryCustom()"
                                :disabled="!customDeliveryLabel.trim()"
                                class="btn-portal text-sm px-4 disabled:opacity-50">+ Add</button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="saving" class="btn-portal flex items-center gap-2 px-8 py-3">
                    <span x-show="saving" class="spinner" style="width:14px;height:14px"></span>
                    <span x-text="saving ? 'Saving…' : (restaurant ? 'Save Changes' : 'Create Restaurant')"></span>
                </button>
            </div>
        </form>
    </template>
</div>
@endsection
