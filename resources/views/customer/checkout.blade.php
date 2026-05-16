@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div x-data="checkoutPage" class="max-w-5xl mx-auto px-4 py-10">
    <h1 style="font-family:var(--font-display);font-size:2rem;font-weight:800;margin-bottom:2rem">Checkout</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: address + options -->
        <div class="lg:col-span-2 flex flex-col gap-6">

            <!-- Saved Addresses -->
            <div class="card p-6">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display)">Delivery Address</h2>
                <div x-show="loadingAddresses" class="skeleton h-20 rounded-lg"></div>

                <div x-show="!loadingAddresses" class="flex flex-col gap-3">
                    <template x-for="addr in addresses" :key="addr.id">
                        <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :style="selectedAddress?.id === addr.id ? 'border-color:var(--color-brand);background:rgba(232,98,26,0.05)' : 'border-color:var(--color-cream-3)'">
                            <input type="radio" :value="addr.id" :checked="selectedAddress?.id === addr.id"
                                   @change="selectedAddress = addr; useGps = false" class="mt-1">
                            <div>
                                <p class="font-medium text-sm" x-text="addr.label"></p>
                                <p class="text-sm" style="color:var(--color-warm-muted)"
                                   x-text="[addr.street, addr.city, addr.state].filter(Boolean).join(', ')"></p>
                            </div>
                        </label>
                    </template>

                    <template x-if="addresses.length === 0">
                        <p class="text-sm" style="color:var(--color-warm-muted)">
                            No saved addresses. <a href="/profile" style="color:var(--color-brand)">Add one in your profile</a> or add below.
                        </p>
                    </template>
                </div>
            </div>

            <!-- Special instructions -->
            <div class="card p-6">
                <h2 class="font-semibold mb-3" style="font-family:var(--font-display)">Special Instructions</h2>
                <textarea x-model="specialInstructions" rows="3" class="input resize-none"
                          placeholder="Allergies, gate codes, extra napkins…"></textarea>
            </div>

            <!-- Payment method -->
            <div class="card p-6">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display)">Payment</h2>
                <div class="flex flex-col gap-3">
                    <template x-for="opt in [{v:'cash_on_delivery',label:'Cash on Delivery',icon:'💵'},{v:'card_on_delivery',label:'Card on Delivery',icon:'💳'}]" :key="opt.v">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :style="paymentMethod===opt.v ? 'border-color:var(--color-brand);background:rgba(232,98,26,0.05)' : 'border-color:var(--color-cream-3)'">
                            <input type="radio" :value="opt.v" x-model="paymentMethod">
                            <span x-text="opt.icon"></span>
                            <span class="font-medium text-sm" x-text="opt.label"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right: order summary -->
        <div class="flex flex-col gap-6">
            <div class="card p-6 sticky top-24">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display)">Order Summary</h2>
                <p class="text-sm font-medium mb-3" style="color:var(--color-warm-muted)" x-text="cart.restaurant?.name"></p>

                <div class="flex flex-col gap-3 mb-4 text-sm">
                    <template x-for="item in cart.items" :key="item.cartKey">
                        <div class="rounded-xl p-3" style="background:var(--color-cream);border:1px solid var(--color-cream-2)">
                                <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium leading-snug" x-text="item.name"></p>
                                            <p x-show="item.size" class="text-xs mt-0.5" style="color:var(--color-warm-muted)" x-text="item.size"></p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button @click="cart.decrement(item.cartKey)"
                                                    class="w-6 h-6 rounded-full border flex items-center justify-center font-bold hover:bg-white transition-colors"
                                                    style="border-color:var(--color-cream-3);font-size:0.9rem">−</button>
                                            <span class="font-semibold w-4 text-center" x-text="item.quantity"></span>
                                            <button @click="cart.increment(item.cartKey)"
                                                    class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-white transition-colors"
                                                    style="background:var(--color-brand);font-size:0.9rem">+</button>
                                        </div>
                                        <div class="flex items-start gap-2 shrink-0">
                                            <span class="font-semibold" x-text="fmtCurrency(item.price * item.quantity)"></span>
                                            <button @click="cart.remove(item.cartKey)"
                                                    class="opacity-30 hover:opacity-80 transition-opacity mt-0.5"
                                                    style="font-size:1rem;line-height:1" title="Remove">&times;</button>
                                        </div>
                                    </div>
                                    <!-- Extras -->
                                    <template x-if="item.extras && item.extras.length > 0">
                                        <ul class="mt-2 flex flex-col gap-1 pt-2" style="border-top:1px dashed var(--color-cream-3)">
                                            <template x-for="extra in item.extras" :key="extra.id">
                                                <li class="flex items-center justify-between text-xs" style="color:var(--color-warm-muted)">
                                                    <span class="flex items-center gap-1">
                                                        <button @click="cart.removeExtra(item.cartKey, extra.id)"
                                                                class="opacity-40 hover:opacity-90 transition-opacity"
                                                                style="font-size:0.9rem;line-height:1" title="Remove topping">&times;</button>
                                                        <span x-text="extra.name"></span>
                                                    </span>
                                                    <span x-text="'+' + fmtCurrency(extra.price * item.quantity)"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </template>
                        </div>
                    </template>
                </div>

                <hr style="border-color:var(--color-cream-2);margin-bottom:1rem">

                <div class="flex flex-col gap-1.5 text-sm mb-4">
                    <div class="flex justify-between">
                        <span style="color:var(--color-warm-muted)">Subtotal</span>
                        <span x-text="fmtCurrency(cart.subtotal)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--color-warm-muted)">Delivery fee</span>
                        <span x-text="fmtCurrency(cart.restaurant?.delivery_fee || 0)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--color-warm-muted)">Tax (10%)</span>
                        <span x-text="fmtCurrency(tax)"></span>
                    </div>
                    <hr style="border-color:var(--color-cream-2);margin:0.5rem 0">
                    <div class="flex justify-between font-bold text-base">
                        <span>Total</span>
                        <span x-text="fmtCurrency(total)"></span>
                    </div>
                </div>

                <button @click="submit()" :disabled="submitting"
                        class="btn-brand w-full flex items-center justify-center gap-2 py-3">
                    <span x-show="submitting" class="spinner"></span>
                    <span x-text="submitting ? 'Placing order…' : 'Place Order'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
