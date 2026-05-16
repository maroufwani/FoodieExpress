<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FoodieExpress') — FoodieExpress</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>

<!-- ─── Toast Notifications ────────────────────────────────────────────── -->
<div x-cloak class="fixed top-4 right-4 z-300 flex flex-col gap-2"
     x-data x-show="$store.notify.toasts.length > 0">
    <template x-for="toast in $store.notify.toasts" :key="toast.id">
        <div class="toast flex items-start gap-3 animate-in"
             :class="'toast-' + toast.type"
             style="animation: slideIn 0.25s ease">
            <span class="text-lg leading-none mt-0.5">
                <span x-show="toast.type==='success'">✅</span>
                <span x-show="toast.type==='error'">❌</span>
                <span x-show="toast.type==='info'">ℹ️</span>
            </span>
            <p class="text-sm font-medium text-gray-800 flex-1" x-text="toast.msg"></p>
            <button @click="$store.notify.remove(toast.id)" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>
    </template>
</div>

<!-- ─── Navbar ──────────────────────────────────────────────────────────── -->
<nav class="navbar flex items-center px-3 sm:px-6 gap-2 sm:gap-4" x-data x-init="$store.location.load()">
    <a href="/" class="flex items-center gap-2 shrink-0" style="text-decoration:none">
        <span style="font-size:1.6rem">🍕</span>
        <span class="navbar-brand-text" style="font-family:var(--font-display);font-size:1.375rem;font-weight:700;color:var(--color-brand)">FoodieExpress</span>
    </a>

    <!-- ── Delivery Location Pill (customers only) ── -->
    <template x-if="$store.auth.isCustomer">
        <div class="relative mr-auto ml-2 sm:ml-4 min-w-0 shrink" x-data>
            <button @click="$store.location.open = !$store.location.open"
                    class="flex items-center gap-1.5 sm:gap-2 rounded-xl px-2 sm:px-3 py-2 text-left transition-colors hover:bg-gray-50"
                    style="border:1px solid var(--color-cream-2);max-width:160px;min-width:0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--color-brand);shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <div class="min-w-0">
                    <p class="text-xs font-semibold leading-none truncate" style="color:var(--color-brand)"
                       x-text="$store.location.label || 'Select address'"></p>
                    <p class="text-xs mt-0.5 truncate" style="color:var(--color-warm-muted)"
                       x-show="$store.location.sublabel"
                       x-text="$store.location.sublabel"></p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:#9ca3af;shrink:0"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <!-- Dropdown -->
            <div x-show="$store.location.open" @click.outside="$store.location.open=false" x-cloak
                 class="absolute left-0 mt-2 rounded-xl shadow-xl overflow-hidden"
                 style="background:#fff;border:1px solid var(--color-cream-2);z-index:300;min-width:260px;top:100%">
                <p class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide" style="color:var(--color-warm-muted)">Deliver to</p>
                <template x-if="$store.location.addresses.length === 0 && $store.location.loaded">
                    <p class="px-4 py-3 text-sm" style="color:var(--color-warm-muted)">No saved addresses yet.</p>
                </template>
                <template x-for="addr in $store.location.addresses" :key="addr.id">
                    <button @click="$store.location.select(addr.id)"
                            class="w-full text-left px-4 py-3 flex items-start gap-3 hover:bg-gray-50 transition-colors"
                            style="border-top:1px solid var(--color-cream-2)">
                        <span class="mt-0.5 shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                              :style="$store.location.selectedId === addr.id ? 'background:var(--color-brand);color:#fff' : 'background:var(--color-cream);color:var(--color-brand)'"
                              x-text="(addr.label||'?')[0].toUpperCase()"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold leading-tight" x-text="addr.label"></p>
                            <p class="text-xs mt-0.5 truncate" style="color:var(--color-warm-muted)"
                               x-text="[addr.recipient_name, addr.apartment].filter(Boolean).join(', ')"></p>
                        </div>
                        <svg x-show="$store.location.selectedId === addr.id" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="color:var(--color-brand);margin-top:2px;shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                </template>
                <div style="border-top:1px solid var(--color-cream-2)">
                    <a href="/profile" @click="$store.location.open=false"
                       class="flex items-center gap-2 px-4 py-3 text-sm font-semibold hover:bg-gray-50 transition-colors"
                       style="color:var(--color-brand)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        Add new address
                    </a>
                </div>
            </div>
        </div>
    </template>

    <!-- Push right for non-customers -->
    <template x-if="!$store.auth.isCustomer">
        <span class="mr-auto"></span>
    </template>

    <template x-if="!$store.auth.isLoggedIn">
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <a href="/login" class="btn-ghost">Sign in</a>
            <a href="/register" class="btn-brand navbar-get-started" style="padding:0.5rem 1.25rem;font-size:0.875rem">Get Started</a>
        </div>
    </template>

    <template x-if="$store.auth.isLoggedIn">
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Cart button (customers only) -->
            <template x-if="$store.auth.isCustomer">
                <button @click="$store.cart.open = !$store.cart.open"
                        class="relative flex items-center gap-2 btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <template x-if="$store.cart.count > 0">
                        <span class="absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center rounded-full text-white text-xs font-bold"
                              style="background:var(--color-brand)" x-text="$store.cart.count"></span>
                    </template>
                </button>
            </template>

            <!-- Portal link -->
            <template x-if="!$store.auth.isCustomer">
                <a :href="$store.auth.portalHome()" class="btn-ghost text-sm">My Portal</a>
            </template>

            <!-- My Orders (customers) -->
            <template x-if="$store.auth.isCustomer">
                <a href="/orders" class="btn-ghost text-sm navbar-my-orders">My Orders</a>
            </template>

            <!-- User menu -->
            <div x-data="{open:false}" class="relative">
                <button @click="open=!open" class="flex items-center gap-2 btn-ghost">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold"
                          style="background:var(--color-brand)"
                          x-text="($store.auth.user?.name||'U')[0].toUpperCase()"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open" @click.outside="open=false" x-cloak
                     class="absolute right-0 mt-2 w-44 rounded-xl shadow-lg overflow-hidden"
                     style="background:#fff;border:1px solid var(--color-cream-2);z-index:200">
                    <a href="/profile" class="block px-4 py-2.5 text-sm hover:bg-gray-50">Profile & Addresses</a>
                    <template x-if="$store.auth.isCustomer">
                        <a href="/orders" class="block px-4 py-2.5 text-sm hover:bg-gray-50">My Orders</a>
                    </template>
                    <hr style="border-color:var(--color-cream-2)">
                    <button @click="$store.auth.logout()" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50" style="color:var(--color-error)">Sign Out</button>
                </div>
            </div>
        </div>
    </template>
</nav>

<!-- ─── Cart Drawer ──────────────────────────────────────────────────────── -->
<div x-cloak x-data x-show="$store.cart.open" class="fixed inset-0 z-90" style="background:rgba(0,0,0,0.3)" @click="$store.cart.open=false"></div>
<div x-cloak x-data x-show="$store.cart.open" class="cart-drawer">
    <div class="flex items-center justify-between p-5 border-b" style="border-color:var(--color-cream-2)">
        <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:700">Your Cart</h2>
        <button @click="$store.cart.open=false" class="btn-ghost p-1">&times;</button>
    </div>

    <template x-if="$store.cart.isEmpty">
        <div class="flex flex-col items-center justify-center flex-1 p-8 text-center">
            <span style="font-size:3rem">🛒</span>
            <p class="mt-3 font-medium" style="color:var(--color-warm-muted)">Your cart is empty</p>
            <a href="/" class="btn-brand mt-4" @click="$store.cart.open=false">Browse Restaurants</a>
        </div>
    </template>

    <template x-if="!$store.cart.isEmpty">
        <div class="flex flex-col flex-1 overflow-hidden">
            <p class="px-5 pt-3 pb-1 text-sm font-semibold" style="color:var(--color-warm-muted)"
               x-text="$store.cart.restaurant?.name"></p>
            <div class="flex-1 overflow-y-auto px-5 py-2 flex flex-col gap-3">
                <template x-for="item in $store.cart.items" :key="item.cartKey">
                    <div style="background:#fff;border-radius:14px;border:1px solid var(--color-cream-2);box-shadow:0 1px 5px rgba(0,0,0,0.06);overflow:hidden">
                        <div style="padding:11px 13px 10px">
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
                                        <p class="font-semibold text-sm leading-snug" x-text="item.name"></p>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1.5">
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
                            <div style="border-top:1px dashed var(--color-cream-3);padding:6px 13px 8px;display:flex;flex-direction:column;gap:4px">
                                <template x-for="(extra, ei) in item.extras" :key="ei">
                                    <div class="flex items-center justify-between text-xs" style="color:var(--color-warm-muted)">
                                        <div class="flex items-center gap-1">
                                            <button @click="$store.cart.removeExtra(item.cartKey, ei)"
                                                    class="opacity-40 hover:opacity-100 transition-opacity"
                                                    style="font-size:0.85rem;line-height:1"
                                                    title="Remove topping">&times;</button>
                                            <span x-text="extra.name"></span>
                                        </div>
                                        <span x-text="'+' + fmtCurrency(extra.price)"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div style="border-top:1px solid var(--color-cream-2);background:var(--color-cream);padding:7px 13px;border-radius:0 0 14px 14px;display:flex;align-items:center;justify-content:space-between">
                            <button @click="$store.cart.remove(item.cartKey)"
                                    class="flex items-center justify-center opacity-40 hover:opacity-90 transition-opacity"
                                    style="color:#c8232c" title="Remove item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                            <div style="display:flex;align-items:center;gap:8px">
                                <button @click="$store.cart.decrement(item.cartKey)"
                                        class="w-7 h-7 rounded-full border flex items-center justify-center text-sm font-bold hover:bg-white transition-colors"
                                        style="border-color:var(--color-cream-3)">−</button>
                                <span class="text-sm font-semibold w-5 text-center" x-text="item.quantity"></span>
                                <button @click="$store.cart.increment(item.cartKey)"
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold text-white"
                                        style="background:var(--color-brand)">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="border-t p-5" style="border-color:var(--color-cream-2)">
                <div class="flex justify-between text-sm mb-1">
                    <span style="color:var(--color-warm-muted)">Subtotal</span>
                    <span class="font-medium" x-text="fmtCurrency($store.cart.subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm mb-3">
                    <span style="color:var(--color-warm-muted)">Delivery fee</span>
                    <span class="font-medium" x-text="fmtCurrency($store.cart.restaurant?.delivery_fee || 0)"></span>
                </div>
                <a href="/checkout" class="btn-brand w-full text-center" @click="$store.cart.open=false">
                    Checkout — <span x-text="fmtCurrency($store.cart.subtotal + parseFloat($store.cart.restaurant?.delivery_fee||0))"></span>
                </a>
            </div>
        </div>
    </template>
</div>

<!-- ─── Main Content ─────────────────────────────────────────────────────── -->
<main>
    @yield('content')
</main>

<style>
@keyframes slideIn { from { opacity:0; transform: translateX(20px); } to { opacity:1; transform: translateX(0); } }
</style>

@stack('scripts')
</body>
</html>
