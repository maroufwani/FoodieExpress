@extends('layouts.app')
@section('title', 'Restaurant')

<style>
.cart-items-scroll::-webkit-scrollbar { width: 5px; }
.cart-items-scroll::-webkit-scrollbar-track { background: transparent; }
.cart-items-scroll::-webkit-scrollbar-thumb { background: var(--color-cream-3); border-radius: 99px; }
.cart-items-scroll::-webkit-scrollbar-thumb:hover { background: #ccc; }
.toolbar-btn { display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.65rem;border:1.5px solid #E8DECE;border-radius:7px;font-size:0.78rem;font-weight:600;cursor:pointer;transition:background 0.13s,color 0.13s,border-color 0.13s;white-space:nowrap;background:#FBF7F0;color:#7A6552; }
.toolbar-btn-active-orange { background:#E8621A !important;color:#fff !important;border-color:#E8621A !important; }
.toolbar-btn-active-green  { background:#e8f8e8 !important;color:#16a016 !important;border-color:#16a016 !important;box-shadow:0 0 0 3px rgba(34,167,34,0.12); }
</style>

@section('content')
<div x-data="restaurantDetail({{ $restaurantId }})">

    <!-- Skeleton / loading -->
    <div x-show="loading" class="max-w-5xl mx-auto px-4 py-12">
        <div class="skeleton h-64 w-full rounded-2xl mb-6"></div>
        <div class="skeleton h-8 w-1/3 rounded mb-3"></div>
        <div class="skeleton h-5 w-2/3 rounded mb-8"></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <template x-for="n in 6"><div class="skeleton h-28 rounded-xl"></div></template>
        </div>
    </div>

    <!-- Content -->
    <template x-if="!loading && restaurant">
        <div>
            <!-- Hero image -->
            <div class="relative h-56 md:h-80 overflow-hidden" style="background:var(--color-cream-2)">
                <img x-show="restaurant.image_path" :src="restaurant.image_path" :alt="restaurant.name"
                     class="w-full h-full object-cover">
                <div x-show="!restaurant.image_path" class="absolute inset-0 flex items-center justify-center" style="font-size:5rem">🍽️</div>
                <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 60%)"></div>
                <div class="absolute bottom-0 left-0 p-6 text-white">
                    <h1 style="font-family:var(--font-display);font-size:2rem;font-weight:800" x-text="restaurant.name"></h1>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <template x-for="c in (restaurant.cuisine_types||[])" :key="c">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:rgba(255,255,255,0.2)" x-text="c"></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Info bar -->
            <div style="background:#fff;border-bottom:1px solid var(--color-cream-2)">
                <div class="max-w-5xl mx-auto px-4 py-3 flex flex-wrap gap-4 text-sm" style="color:var(--color-warm-muted)">
                    <span>⭐ <strong x-text="parseFloat(restaurant.rating||0).toFixed(1)"></strong> rating</span>
                    <span>🕐 <strong x-text="restaurant.estimated_delivery_time + ' min'"></strong> delivery</span>
                    <span>💳 Min. <strong x-text="fmtCurrency(restaurant.min_order_amount)"></strong></span>
                    <template x-if="parseFloat(restaurant.delivery_fee) === 0">
                        <span>🛵 <strong style="color:var(--color-brand)">Free delivery</strong></span>
                    </template>
                    <template x-if="parseFloat(restaurant.delivery_fee) > 0">
                        <span>🛵 <strong x-text="fmtCurrency(restaurant.delivery_fee)"></strong> delivery fee</span>
                    </template>
                    <template x-if="!restaurant.is_active">
                        <span class="badge badge-warning ml-auto">Currently Closed</span>
                    </template>
                    <template x-if="restaurant.is_active">
                        <span class="badge badge-delivered ml-auto">Open Now</span>
                    </template>
                </div>
            </div>

            <!-- Menu + Cart summary side panel -->
            <div class="max-w-5xl mx-auto px-4 py-8 flex gap-8 items-start">
                <!-- Menu -->
                <div class="flex-1 min-w-0">

                    <!-- Search / Sort / Veg toolbar -->
                    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;margin-bottom:1.75rem;background:#fff;border:1px solid var(--color-cream-2);border-radius:14px;padding:0.5rem 0.65rem;box-shadow:0 1px 4px rgba(0,0,0,0.04)">
                        <!-- Search -->
                        <div style="position:relative;flex:1;min-width:140px">
                            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--color-warm-muted)" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" x-model="search" placeholder="Search menu…"
                                   style="width:100%;padding:0.45rem 0.75rem 0.45rem 2rem;border:1.5px solid transparent;border-radius:8px;font-size:0.875rem;outline:none;background:var(--color-cream);color:var(--color-warm-text);transition:border-color 0.15s"
                                   @focus="$el.style.borderColor='var(--color-brand)';$el.style.background='#fff'"
                                   @blur="$el.style.borderColor='transparent';$el.style.background='var(--color-cream)'">
                        </div>

                        <!-- Divider -->
                        <div style="width:1px;height:24px;background:var(--color-cream-2);flex-shrink:0"></div>

                        <!-- Sort: icon buttons -->
                        <div style="display:flex;align-items:center;gap:0.2rem;flex-shrink:0">
                            <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;color:var(--color-warm-muted);padding:0 0.25rem;white-space:nowrap">Sort</span>
                                <template x-for="opt in [{v:'default',label:'Default'},{v:'name',label:'A–Z'},{v:'price_asc',label:'↑ Price'},{v:'price_desc',label:'↓ Price'}]" :key="opt.v">
                                <button @click="sortBy = opt.v"
                                        class="toolbar-btn"
                                        :class="sortBy === opt.v ? 'toolbar-btn-active-orange' : ''"
                                        x-text="opt.label">
                                </button>
                            </template>
                        </div>

                        <!-- Divider -->
                        <div style="width:1px;height:24px;background:var(--color-cream-2);flex-shrink:0"></div>

                        <!-- Veg only toggle -->
                        <button @click="vegOnly = !vegOnly"
                                class="toolbar-btn" style="font-weight:700;flex-shrink:0"
                                :class="vegOnly ? 'toolbar-btn-active-green' : ''">
                            <span style="display:inline-flex;width:13px;height:13px;border:2px solid currentColor;border-radius:2px;align-items:center;justify-content:center;flex-shrink:0">
                                <svg width="6" height="6" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="currentColor"/></svg>
                            </span>
                            Veg Only
                        </button>
                    </div>

                    <template x-if="Object.keys(menu).length === 0">
                        <p class="text-center py-12" style="color:var(--color-warm-muted)">No menu items yet.</p>
                    </template>
                    <template x-if="Object.keys(menu).length > 0 && filteredMenuIsEmpty">
                        <p class="text-center py-12" style="color:var(--color-warm-muted)">No items match your search.</p>
                    </template>

                    <template x-for="(items, category) in filteredMenu" :key="category">
                        <div class="mb-10">
                            <h2 style="font-family:var(--font-display);font-size:1.375rem;font-weight:700;margin-bottom:1rem"
                                x-text="category"></h2>
                            <div class="flex flex-col gap-3">
                                <template x-for="item in items" :key="item.id">
                                    <div class="card p-4 flex gap-4 items-start"
                                         :class="!item.is_available ? 'opacity-50' : ''">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="font-semibold" x-text="item.name"></h3>
                                                <template x-if="item.is_vegetarian && !item.is_vegan">
                                                    <span title="Vegetarian" style="display:inline-flex;width:16px;height:16px;border:2px solid #22a722;border-radius:3px;align-items:center;justify-content:center;flex-shrink:0">
                                                        <svg width="8" height="8" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="#22a722"/></svg>
                                                    </span>
                                                </template>
                                                <template x-if="item.is_vegan">
                                                    <span title="Vegan" style="display:inline-flex;width:16px;height:16px;border:2px solid #22a722;border-radius:3px;align-items:center;justify-content:center;flex-shrink:0">
                                                        <svg width="8" height="8" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="#22a722"/></svg>
                                                    </span>
                                                </template>
                                                <template x-if="!item.is_vegetarian && !item.is_vegan">
                                                    <span title="Non-Vegetarian" style="display:inline-flex;width:16px;height:16px;border:2px solid #c8232c;border-radius:3px;align-items:center;justify-content:center;flex-shrink:0">
                                                        <svg width="8" height="8" viewBox="0 0 8 8"><polygon points="4,0.5 7.5,7.5 0.5,7.5" fill="#c8232c"/></svg>
                                                    </span>
                                                </template>
                                                <template x-if="item.is_gluten_free">
                                                    <span title="Gluten Free" class="text-xs px-1.5 py-0.5 rounded font-bold" style="background:#fef9c3;color:#854d0e">GF</span>
                                                </template>
                                            </div>
                                            <p class="text-sm mt-1" style="color:var(--color-warm-muted)" x-text="item.description"></p>

                                            <!-- Single price (no sizes) -->
                                            <template x-if="!item.sizes || item.sizes.length === 0">
                                                <p class="mt-2 font-bold" style="color:var(--color-brand)" x-text="fmtCurrency(item.price)"></p>
                                            </template>

                                            <!-- Size picker -->
                                            <template x-if="item.sizes && item.sizes.length > 0">
                                                <div class="mt-3">
                                                    <template x-if="!item.is_available">
                                                        <span class="text-xs" style="color:var(--color-warm-muted)">Unavailable</span>
                                                    </template>
                                                    <template x-if="item.is_available">
                                                        <div class="flex flex-wrap gap-2">
                                                            <template x-for="s in item.sizes" :key="s.label">
                                                                <div>
                                                                    <!-- Stepper when total qty > 0 -->
                                                                    <template x-if="$store.cart.totalQtyFor(item.id, s.label) > 0">
                                                                        <div class="flex items-center gap-1 btn-size" style="padding:0.25rem 0.5rem">
                                                                            <button @click="$store.cart.decrementAny(item.id, s.label)"
                                                                                    class="w-6 h-6 rounded-full flex items-center justify-center font-bold"
                                                                                    style="background:var(--color-brand);color:#fff;font-size:1rem;line-height:1">−</button>
                                                                            <span class="text-xs font-bold w-4 text-center" x-text="$store.cart.totalQtyFor(item.id, s.label)"></span>
                                                                            <button @click="tryAdd(item, s)"
                                                                                    class="w-6 h-6 rounded-full flex items-center justify-center font-bold"
                                                                                    style="background:var(--color-brand);color:#fff;font-size:1rem;line-height:1">+</button>
                                                                            <span class="ml-1 text-xs" x-text="s.label + ' — ' + fmtCurrency(s.price)"></span>
                                                                        </div>
                                                                    </template>
                                                                    <!-- Plain pill when qty === 0 -->
                                                                    <template x-if="$store.cart.totalQtyFor(item.id, s.label) === 0">
                                                                        <button @click="tryAdd(item, s)"
                                                                                class="btn-size"
                                                                                x-text="s.label + ' — ' + fmtCurrency(s.price)">
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <template x-if="item.image_path">
                                            <img :src="item.image_path" :alt="item.name" class="w-24 h-24 object-cover rounded-xl shrink-0">
                                        </template>
                                        <!-- Add button / stepper for items without sizes -->
                                        <template x-if="!item.sizes || item.sizes.length === 0">
                                            <div class="shrink-0 self-center">
                                                <template x-if="!item.is_available">
                                                    <span class="text-xs" style="color:var(--color-warm-muted)">Unavailable</span>
                                                </template>
                                                <template x-if="item.is_available && $store.cart.qtyFor(String(item.id)) === 0">
                                                    <button @click="tryAdd(item, null)" class="btn-brand px-4 py-2 text-sm">Add</button>
                                                </template>
                                                <template x-if="item.is_available && $store.cart.qtyFor(String(item.id)) > 0">
                                                    <div class="flex items-center gap-2">
                                                        <button @click="$store.cart.decrement(String(item.id))"
                                                                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white"
                                                                style="background:var(--color-brand)">−</button>
                                                        <span class="text-sm font-bold w-4 text-center" x-text="$store.cart.qtyFor(String(item.id))"></span>
                                                        <button @click="tryAdd(item, null)"
                                                                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white"
                                                                style="background:var(--color-brand)">+</button>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Cart summary (desktop) -->
                <div class="hidden lg:block w-72 shrink-0" x-data
                     style="position:sticky;top:6rem;height:calc(100vh - 7rem)">
                    <div class="card h-full flex flex-col" style="overflow:hidden">
                        <div style="padding:1rem 1rem 0.5rem;flex-shrink:0">
                            <h3 style="font-family:var(--font-display);font-weight:700">Your Order</h3>
                        </div>
                        <template x-if="$store.cart.isEmpty">
                            <p class="text-sm text-center py-6 px-4" style="color:var(--color-warm-muted)">Your cart is empty</p>
                        </template>
                        <template x-if="!$store.cart.isEmpty">
                            <div style="display:flex;flex-direction:column;flex:1 1 0;min-height:0">
                                <div class="cart-items-scroll" style="flex:1 1 0;min-height:0;overflow-y:auto;padding:0.5rem 1rem;display:flex;flex-direction:column;gap:0.5rem">
                                    <template x-for="item in $store.cart.items" :key="item.cartKey">
                                        <div style="background:#fff;border-radius:12px;border:1px solid var(--color-cream-2);box-shadow:0 1px 4px rgba(0,0,0,0.05);overflow:hidden;flex-shrink:0">
                                            <div style="padding:10px 12px 9px">
                                                <div class="flex items-start gap-2">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-1.5">
                                                            <template x-if="(item.is_vegetarian || item.is_vegan) && !(item.extras || []).some(e => !e.is_vegetarian && !e.is_vegan)">
                                                                <span title="Vegetarian" style="display:inline-flex;width:13px;height:13px;border:1.5px solid #22a722;border-radius:2px;align-items:center;justify-content:center;flex-shrink:0">
                                                                    <svg width="6" height="6" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="#22a722"/></svg>
                                                                </span>
                                                            </template>
                                                            <template x-if="(!item.is_vegetarian && !item.is_vegan) || (item.extras || []).some(e => !e.is_vegetarian && !e.is_vegan)">
                                                                <span title="Non-Veg" style="display:inline-flex;width:13px;height:13px;border:1.5px solid #c8232c;border-radius:2px;align-items:center;justify-content:center;flex-shrink:0">
                                                                    <svg width="6" height="6" viewBox="0 0 8 8"><polygon points="4,0.5 7.5,7.5 0.5,7.5" fill="#c8232c"/></svg>
                                                                </span>
                                                            </template>
                                                            <p class="text-sm font-semibold leading-snug" x-text="item.name"></p>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 mt-1">
                                                            <template x-if="item.size">
                                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:var(--color-cream-2);color:var(--color-warm-muted)" x-text="item.size"></span>
                                                            </template>
                                                            <span class="text-xs" style="color:var(--color-warm-muted)" x-text="fmtCurrency(item.price)"></span>
                                                        </div>
                                                    </div>
                                                    <div class="shrink-0">
                                                        <span class="text-sm font-bold" x-text="fmtCurrency((item.price + (item.extras||[]).reduce((s,e)=>s+e.price,0)) * item.quantity)"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <template x-if="item.extras && item.extras.length > 0">
                                                <div style="border-top:1px dashed var(--color-cream-3);padding:6px 12px 8px;display:flex;flex-direction:column;gap:3px">
                                                    <template x-for="(extra, ei) in item.extras" :key="ei">
                                                        <div class="flex items-center justify-between text-xs" style="color:var(--color-warm-muted)">
                                                            <div class="flex items-center gap-1">
                                                                <button @click="$store.cart.removeExtra(item.cartKey, ei)"
                                                                        class="opacity-40 hover:opacity-90 transition-opacity"
                                                                        style="font-size:0.85rem;line-height:1" title="Remove topping">&times;</button>
                                                                <span x-text="extra.name"></span>
                                                            </div>
                                                            <span x-text="'+' + fmtCurrency(extra.price)"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <div style="border-top:1px solid var(--color-cream-2);background:var(--color-cream);padding:6px 12px;border-radius:0 0 12px 12px;display:flex;align-items:center;justify-content:space-between">
                                                <button @click="$store.cart.remove(item.cartKey)"
                                                        class="flex items-center justify-center opacity-40 hover:opacity-90 transition-opacity"
                                                        style="color:#c8232c" title="Remove item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                </button>
                                                <div style="display:flex;align-items:center;gap:6px">
                                                    <button @click="$store.cart.decrement(item.cartKey)"
                                                            class="w-6 h-6 rounded-full border flex items-center justify-center font-bold hover:bg-white transition-colors"
                                                            style="border-color:var(--color-cream-3);font-size:0.85rem">−</button>
                                                    <span class="text-sm font-semibold w-4 text-center" x-text="item.quantity"></span>
                                                    <button @click="$store.cart.increment(item.cartKey)"
                                                            class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-white"
                                                            style="background:var(--color-brand);font-size:0.85rem">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div style="flex-shrink:0;padding:0.75rem 1rem 1rem;border-top:1px solid var(--color-cream-2)">
                                    <div class="flex justify-between text-sm mb-3">
                                        <span style="color:var(--color-warm-muted)">Subtotal</span>
                                        <span class="font-bold" x-text="fmtCurrency($store.cart.subtotal)"></span>
                                    </div>
                                    <a href="/checkout" class="btn-brand w-full text-center block">Checkout</a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Customize / Add-ons modal -->
    <div x-show="extrasModal.open" x-cloak class="modal-backdrop" style="z-index:160">
        <div class="modal-box max-w-md" style="padding:0;display:flex;flex-direction:column;max-height:88vh">

            <!-- Header -->
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--color-cream-2);flex-shrink:0">
                <div class="flex items-center justify-between">
                    <h3 style="font-family:var(--font-display);font-weight:700;font-size:1.15rem">Customize</h3>
                    <button @click="extrasModal.open=false" class="btn-ghost p-1" style="font-size:1.4rem;line-height:1">&times;</button>
                </div>
                <p class="text-sm mt-0.5" style="color:var(--color-warm-muted)">
                    <span x-text="extrasModal.item?.name"></span><span x-show="extrasModal.size" x-text="' (' + extrasModal.size?.label + ')'"></span>
                </p>
            </div>

            <!-- Scrollable body -->
            <div style="overflow-y:auto;flex:1;padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:1.25rem">

                <!-- Option groups (defined by restaurant owner) -->
                <template x-if="extrasModal.item && extrasModal.item.option_groups && extrasModal.item.option_groups.length > 0">
                    <div>
                        <template x-for="(group, gi) in extrasModal.item.option_groups" :key="gi">
                            <div style="margin-bottom:1.1rem">
                                <p class="text-sm font-semibold mb-2" x-text="group.heading || 'Add-ons'"></p>
                                <div class="flex flex-col gap-2">
                                    <template x-for="(opt, oi) in group.options" :key="oi">
                                        <label class="flex items-center justify-between gap-3 p-3 rounded-xl cursor-pointer transition-all"
                                               :style="isGroupOptionSelected(gi, oi)
                                                   ? 'background:rgba(232,98,26,0.07);border:1.5px solid var(--color-brand)'
                                                   : 'background:var(--color-cream);border:1.5px solid transparent'"
                                               @click.prevent="toggleGroupOption(gi, oi)">
                                            <div class="flex items-center gap-3">
                                                <div class="w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                                                     :style="isGroupOptionSelected(gi, oi)
                                                         ? 'background:var(--color-brand);border-color:var(--color-brand)'
                                                         : 'background:#fff;border-color:#ccc'">
                                                    <svg x-show="isGroupOptionSelected(gi, oi)" xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                </div>
                                                <span class="text-sm font-medium" x-text="opt.name"></span>
                                            </div>
                                            <span class="text-sm font-semibold shrink-0" style="color:var(--color-brand)"
                                                  x-text="parseFloat(opt.price || 0) > 0 ? '+' + fmtCurrency(opt.price) : 'Free'"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Extra Toppings (pizza items) -->
                <template x-if="isPizza(extrasModal.item) && toppings.length > 0">
                    <div>
                        <p class="text-sm font-semibold mb-2">
                            Extra Toppings
                            <span style="color:var(--color-warm-muted);font-weight:400"> — optional</span>
                        </p>
                        <div class="flex flex-col gap-2">
                            <template x-for="topping in toppings" :key="topping.id">
                                <label class="flex items-center justify-between gap-3 p-3 rounded-xl cursor-pointer transition-all"
                                       :style="isExtraSelected(topping.id)
                                           ? 'background:rgba(232,98,26,0.07);border:1.5px solid var(--color-brand)'
                                           : 'background:var(--color-cream);border:1.5px solid transparent'"
                                       @click.prevent="toggleExtra(topping)">
                                    <div class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                                             :style="isExtraSelected(topping.id)
                                                 ? 'background:var(--color-brand);border-color:var(--color-brand)'
                                                 : 'background:#fff;border-color:#ccc'">
                                            <svg x-show="isExtraSelected(topping.id)" xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <template x-if="topping.is_vegetarian || topping.is_vegan">
                                            <span title="Vegetarian" style="display:inline-flex;width:14px;height:14px;border:1.5px solid #22a722;border-radius:2px;align-items:center;justify-content:center;flex-shrink:0">
                                                <svg width="7" height="7" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="#22a722"/></svg>
                                            </span>
                                        </template>
                                        <template x-if="!topping.is_vegetarian && !topping.is_vegan">
                                            <span title="Non-Vegetarian" style="display:inline-flex;width:14px;height:14px;border:1.5px solid #c8232c;border-radius:2px;align-items:center;justify-content:center;flex-shrink:0">
                                                <svg width="7" height="7" viewBox="0 0 8 8"><polygon points="4,0.5 7.5,7.5 0.5,7.5" fill="#c8232c"/></svg>
                                            </span>
                                        </template>
                                        <span class="text-sm font-medium" x-text="topping.name"></span>
                                    </div>
                                    <span class="text-sm font-semibold shrink-0" style="color:var(--color-brand)"
                                          x-text="'+' + fmtCurrency(toppingPriceForPizzaSize(topping))"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--color-cream-2);flex-shrink:0;display:flex;gap:0.75rem">
                <button @click="confirmAddWithExtras(true)" class="btn-outline flex-1">Skip</button>
                <button @click="confirmAddWithExtras(false)" class="btn-brand flex-1">
                    Add to Cart
                    <template x-if="Object.keys(extrasModal.selected).length + Object.keys(extrasModal.groupSelected).length > 0">
                        <span x-text="' +' + (Object.keys(extrasModal.selected).length + Object.keys(extrasModal.groupSelected).length) + ' add-on' + ((Object.keys(extrasModal.selected).length + Object.keys(extrasModal.groupSelected).length) > 1 ? 's' : '')"></span>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm switch restaurant modal -->
    <div x-show="confirmSwitch" x-cloak class="modal-backdrop" style="z-index:150">
        <div class="modal-box max-w-sm">
            <h3 style="font-family:var(--font-display);font-weight:700;font-size:1.2rem;margin-bottom:0.5rem">Start a new order?</h3>
            <p class="text-sm mb-6" style="color:var(--color-warm-muted)">
                Your cart contains items from <strong x-text="$store.cart.restaurant?.name"></strong>.
                Starting a new order will clear your current cart.
            </p>
            <div class="flex gap-3">
                <button @click="confirmSwitch=false" class="btn-outline flex-1">Keep current</button>
                <button @click="confirmSwitchCart()" class="btn-brand flex-1">Start new</button>
            </div>
        </div>
    </div>

</div>
@endsection
