@extends('layouts.app')
@section('title', 'My Profile')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('content')
<div x-data="profilePage" class="max-w-3xl mx-auto px-4 py-10">
    <h1 style="font-family:var(--font-display);font-size:2rem;font-weight:800;margin-bottom:2rem">My Profile</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Sidebar avatar / quick info -->
        <div class="md:col-span-1">
            <div class="card p-6 text-center">
                <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4"
                     style="background:var(--color-brand)"
                     x-text="(user?.name||'U')[0].toUpperCase()"></div>
                <p class="font-bold" x-text="user?.name"></p>
                <p class="text-sm" style="color:var(--color-warm-muted)" x-text="user?.email"></p>
                <p class="text-xs mt-1 capitalize" style="color:var(--color-warm-muted)" x-text="user?.role?.replace('_',' ')"></p>
            </div>
        </div>

        <!-- Right panels -->
        <div class="md:col-span-2 flex flex-col gap-6">

            <!-- Profile form -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 style="font-family:var(--font-display);font-weight:700">Personal Info</h2>
                    <button @click="editing = !editing" class="btn-ghost text-sm"
                            x-text="editing ? 'Cancel' : 'Edit'"></button>
                </div>

                <div x-show="!editing" class="flex flex-col gap-3 text-sm">
                    <div class="flex justify-between">
                        <span style="color:var(--color-warm-muted)">Name</span>
                        <span x-text="user?.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--color-warm-muted)">Email</span>
                        <span x-text="user?.email"></span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--color-warm-muted)">Phone</span>
                        <span x-text="user?.phone || '—'"></span>
                    </div>
                </div>

                <form x-show="editing" @submit.prevent="saveProfile()" class="flex flex-col gap-4">
                    <div>
                        <label class="label">Name</label>
                        <input type="text" x-model="name" required class="input">
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="tel" x-model="phone" class="input">
                    </div>
                    <button type="submit" :disabled="saving" class="btn-brand flex items-center justify-center gap-2 self-start px-6">
                        <span x-show="saving" class="spinner"></span>
                        <span x-text="saving ? 'Saving…' : 'Save Changes'"></span>
                    </button>
                </form>
            </div>

            <!-- Addresses -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 style="font-family:var(--font-display);font-weight:700">Saved Addresses</h2>
                    <button @click="openAddressModal()" class="btn-brand px-4 py-2 text-sm">+ Add</button>
                </div>

                <template x-if="addresses.length === 0">
                    <p class="text-sm" style="color:var(--color-warm-muted)">No saved addresses yet.</p>
                </template>

                <div class="flex flex-col gap-3">
                    <template x-for="addr in addresses" :key="addr.id">
                        <div class="flex items-start justify-between p-3 rounded-xl" style="background:var(--color-cream)">
                            <div>
                                <p class="font-semibold text-sm" x-text="addr.label"></p>
                                <p class="text-sm" style="color:var(--color-warm-muted)"
                                   x-text="[addr.recipient_name, addr.apartment, addr.phone ? '••••' + addr.phone.slice(-4) : ''].filter(Boolean).join(', ')"></p>
                            </div>
                            <div class="flex gap-2 shrink-0 ml-3">
                                <button @click="openAddressModal(addr)" class="btn-ghost px-3 py-1 text-xs">Edit</button>
                                <button @click="deleteAddress(addr.id)" class="btn-ghost px-3 py-1 text-xs" style="color:var(--color-error)">Delete</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Address Modal -->
    <div x-show="showAddressModal" x-cloak class="modal-backdrop" style="z-index:150">
        <div class="modal-box" style="max-width:700px">

            <!-- Header -->
            <div class="flex items-center justify-between mb-1">
                <h3 style="font-family:var(--font-display);font-weight:700;font-size:1.2rem"
                    x-text="editingAddress ? 'Edit Address' : 'New Address'"></h3>
                <button @click="showAddressModal=false" class="btn-ghost">&times;</button>
            </div>

            <!-- Step indicators -->
            <div class="flex items-center gap-3 mb-5">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                         :style="addrStep===1 ? 'background:var(--color-brand);color:#fff' : 'background:#e5e7eb;color:#6b7280'">1</div>
                    <span class="text-sm font-semibold" :style="addrStep===1 ? 'color:var(--color-brand)' : 'color:#9ca3af'">Pin Location</span>
                </div>
                <div class="flex-1 h-px" style="background:#e5e7eb"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                         :style="addrStep===2 ? 'background:var(--color-brand);color:#fff' : 'background:#e5e7eb;color:#6b7280'">2</div>
                    <span class="text-sm font-semibold" :style="addrStep===2 ? 'color:var(--color-brand)' : 'color:#9ca3af'">Address Details</span>
                </div>
            </div>

            <!-- ── STEP 1: Map ── -->
            <div x-show="addrStep===1">
                <!-- Search row -->
                <div class="relative mb-3">
                    <div class="flex gap-2">
                        <input type="text" x-model="mapSearch"
                               @keydown.enter.prevent="geocodeSearch()"
                               class="input flex-1" placeholder="Search a place or address…">
                        <button type="button" @click="geocodeSearch()" :disabled="geocoding"
                                class="btn-brand px-4 shrink-0 flex items-center gap-2" style="min-width:110px;justify-content:center">
                            <span x-show="geocoding" class="spinner" style="width:14px;height:14px;border-width:2px"></span>
                            <span x-show="!geocoding">Search</span>
                        </button>
                        <button type="button" @click="useMyLocation()" :disabled="locatingGps"
                                class="btn-outline px-4 shrink-0 flex items-center gap-2" style="min-width:145px;justify-content:center">
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
                    <!-- Autocomplete results -->
                    <div x-show="geocodeResults.length > 0"
                         class="absolute left-0 right-0 mt-1 rounded-xl shadow-xl overflow-hidden"
                         style="background:#fff;border:1px solid var(--color-cream-2);z-index:1500;top:100%">
                        <!-- Close button -->
                        <div class="flex justify-end px-2 pt-1.5 pb-0.5">
                            <button type="button" @click="geocodeResults = []"
                                    class="text-xs font-semibold flex items-center gap-1 px-2 py-0.5 rounded-md"
                                    style="color:var(--color-warm-muted);background:var(--color-cream-2)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Close
                            </button>
                        </div>
                        <template x-for="(r, i) in geocodeResults" :key="i">
                            <button type="button" @click="selectGeocode(r)"
                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50"
                                    style="border-bottom:1px solid var(--color-cream-2)"
                                    x-text="r.display_name"></button>
                        </template>
                    </div>
                </div>

                <!-- GPS blocked warning -->
                <div x-show="gpsBlocked" class="flex items-start gap-2 rounded-xl px-3 py-2.5 mb-3 text-sm"
                     style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Location permission is blocked. Enable it in your browser settings, or tap the map to pin your address manually.</span>
                </div>

                <!-- Map -->
                <div id="addr-map" style="height:320px;border-radius:0.75rem;overflow:hidden;border:1px solid var(--color-cream-2)"></div>

                <!-- Detected address preview -->
                <div x-show="addrForm.street || addrForm.city" class="mt-3 flex items-start gap-2 text-sm px-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--color-brand);margin-top:2px;shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span style="color:var(--color-warm-muted)"
                          x-text="[addrForm.street, addrForm.city, addrForm.state].filter(Boolean).join(', ')"></span>
                </div>
                <p class="text-xs mt-2 px-1" style="color:var(--color-warm-muted)">Click on the map or drag the pin to fine-tune your delivery location.</p>

                <div class="flex gap-3 justify-end mt-4">
                    <button type="button" @click="showAddressModal=false" class="btn-outline">Cancel</button>
                    <button type="button" @click="addrStep=2"
                            :disabled="!addrForm.latitude"
                            class="btn-brand px-6">
                        Next: Add Details →
                    </button>
                </div>
            </div>

            <!-- ── STEP 2: Details ── -->
            <div x-show="addrStep===2">
                <form @submit.prevent="saveAddress()" class="flex flex-col gap-4">
                    <!-- Detected location summary -->
                    <div class="flex items-center gap-2 text-sm px-4 py-3 rounded-xl" style="background:var(--color-cream)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--color-brand);shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span style="color:var(--color-warm-muted)"
                              x-text="[addrForm.street, addrForm.city, addrForm.state].filter(Boolean).join(', ') || 'Custom pin location'"></span>
                        <button type="button" @click="addrStep=1" class="ml-auto text-xs underline shrink-0" style="color:var(--color-brand)">Change</button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Your name <span style="color:var(--color-error)">*</span></label>
                            <input type="text" x-model="addrForm.recipient_name" required class="input" placeholder="Full name">
                        </div>
                        <div>
                            <label class="label">Phone number <span style="color:var(--color-error)">*</span></label>
                            <input type="tel" x-model="addrForm.phone" required pattern="[6-9][0-9]{9}" maxlength="10" class="input" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-span-2">
                            <label class="label">Save as <span style="color:var(--color-error)">*</span></label>
                            <div class="flex gap-2 flex-wrap">
                                <template x-for="tag in ['Home','Work','Hotel','Other']" :key="tag">
                                    <button type="button"
                                            @click="tag !== 'Other' ? addrForm.label = tag : (addrForm.label = ['Home','Work','Hotel'].includes(addrForm.label) ? 'Other' : addrForm.label)"
                                            :class="(tag === 'Other' ? (addrForm.label && !['Home','Work','Hotel'].includes(addrForm.label)) : addrForm.label === tag) ? 'btn-brand' : 'btn-outline'"
                                            class="px-4 py-1.5 rounded-full text-sm font-semibold"
                                            x-text="tag"></button>
                                </template>
                            </div>
                            <input type="text"
                                   x-model="addrForm.label"
                                   @input="/* typing auto-selects Other via :class binding */"
                                   required class="input mt-2" placeholder="Or type a custom label">
                        </div>
                        <div>
                            <label class="label">Flat / Apartment / Building <span style="color:var(--color-error)">*</span></label>
                            <input type="text" x-model="addrForm.apartment" required class="input" placeholder="e.g. Flat 4B, Tower C">
                        </div>
                        <div>
                            <label class="label">Landmark</label>
                            <input type="text" x-model="addrForm.landmark" class="input" placeholder="e.g. Near main gate">
                        </div>
                        <div class="col-span-2">
                            <label class="label">Delivery instructions</label>
                            <textarea x-model="addrForm.delivery_instructions" class="input" rows="2"
                                      placeholder="e.g. Ring bell twice, leave at door, call on arrival…" style="resize:none"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end mt-2">
                        <button type="button" @click="addrStep=1" class="btn-outline">← Back</button>
                        <button type="submit" class="btn-brand px-6">Save Address</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
