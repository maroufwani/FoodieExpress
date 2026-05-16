@extends('layouts.app')
@section('title', 'Order Details')

@section('content')
<div x-data="orderDetail({{ $orderId }})" class="max-w-2xl mx-auto px-4 py-10">

    <!-- Back -->
    <a href="/orders" class="inline-flex items-center gap-1 text-sm mb-8" style="color:var(--color-warm-muted);text-decoration:none">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Back to orders
    </a>

    <!-- Skeleton -->
    <div x-show="loading">
        <div class="skeleton h-8 w-1/2 rounded mb-4"></div>
        <div class="skeleton h-40 w-full rounded-xl mb-4"></div>
        <div class="skeleton h-32 w-full rounded-xl"></div>
    </div>

    <template x-if="!loading && order">
        <div>


            <!-- ── Delivery Partner Banner (shown when assigned) ──── -->
            <template x-if="order.delivery_partner">
                <div class="anim-fade-up mb-6"
                     style="border-radius:1.1rem;overflow:hidden;border:1px solid var(--color-cream-2);box-shadow:0 2px 12px rgba(45,31,14,0.07)">
                    <!-- Header strip -->
                    <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:linear-gradient(90deg,var(--color-brand),#f97316);color:#fff">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect x="9" y="11" width="14" height="10" rx="2"/><circle cx="12" cy="19" r="1"/><circle cx="20" cy="19" r="1"/></svg>
                        <span style="font-size:0.7rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase">Your Delivery Partner</span>
                    </div>
                    <!-- Body -->
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;background:#fff">
                        <div style="display:flex;align-items:center;gap:12px">
                            <!-- Avatar circle -->
                            <div style="width:46px;height:46px;border-radius:50%;background:var(--color-brand);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.1rem;flex-shrink:0"
                                 x-text="(order.delivery_partner.name||'D')[0].toUpperCase()"></div>
                            <div>
                                <p style="font-weight:600;font-size:0.9rem" x-text="order.delivery_partner.name"></p>
                                <template x-if="order.delivery_partner.phone">
                                    <p style="font-size:0.8rem;font-weight:500;color:var(--color-brand);margin-top:3px" x-text="order.delivery_partner.phone"></p>
                                </template>
                            </div>
                        </div>
                        <!-- Call & WhatsApp buttons -->
                        <template x-if="order.delivery_partner.phone">
                            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                                <a :href="'tel:' + order.delivery_partner.phone"
                                   style="display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:#22c55e;color:#fff;text-decoration:none;opacity:0.9;transition:opacity 0.15s"
                                   onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"
                                   title="Call">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 6 6l.95-.95a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.5 16z"/></svg>
                                </a>
                                <a :href="'https://wa.me/' + order.delivery_partner.phone.replace(/\D/g,'')"
                                   target="_blank" rel="noopener"
                                   style="display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:#25d366;color:#fff;text-decoration:none;opacity:0.9;transition:opacity 0.15s"
                                   onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"
                                   title="WhatsApp">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- ── Header ─────────────────────────────────────────── -->
            <div class="flex items-center justify-between mb-5 flex-wrap gap-3 anim-fade-up anim-delay-1">
                <div>
                    <h1 style="font-family:var(--font-display);font-size:1.75rem;font-weight:800">
                        Order #<span x-text="order.id"></span>
                    </h1>
                    <p class="text-sm mt-1" style="color:var(--color-warm-muted)">
                        <span x-text="order.restaurant?.name"></span> ·
                        <span x-text="fmtDateTime(order.created_at)"></span>
                    </p>
                </div>
                <span :class="'status-' + order.status" class="badge text-sm px-3 py-1"
                      x-text="customStatusLabel(order.status)"></span>
            </div>

            <!-- ── Current Status Hero ─────────────────────────────── -->
            <div class="card status-hero p-6 mb-6 anim-fade-up anim-delay-2">
                <div class="flex items-center gap-4">
                    <div class="status-icon-bob" style="color:var(--color-brand);flex-shrink:0" x-html="statusIcon(order.status)"></div>
                    <div class="flex-1 min-w-0">
                        <span class="font-bold" style="font-family:var(--font-display);font-size:1.1rem"
                              x-text="customStatusLabel(order.status)"></span>
                        <p class="text-sm mt-1" style="color:var(--color-warm-muted)"
                           x-text="statusDescription(order.status)"></p>
                        <div class="flex items-center gap-3 mt-3 flex-wrap">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg"
                                  style="background:rgba(232,98,26,0.1);color:var(--color-brand)">
                                Order #<span x-text="order.id"></span>
                            </span>
                            <span class="text-xs" style="color:var(--color-warm-muted)">
                                Placed <span x-text="fmtDateTime(order.created_at)"></span>
                            </span>
                            <template x-if="!['delivered','cancelled'].includes(order.status)">
                                <span class="text-xs font-medium" style="color:var(--color-brand)">Est. 30–45 min</span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <template x-if="order.status !== 'cancelled'">
                    <div class="mt-5">
                        <div class="flex justify-between text-xs font-medium mb-2" style="color:var(--color-warm-muted)">
                            <span>Order Placed</span>
                            <span>Delivered</span>
                        </div>
                        <div class="order-progress-track">
                            <div class="order-progress-fill" :style="'width:' + progressPercent + '%'"></div>
                        </div>
                        <template x-if="order.status === 'delivered'">
                            <p class="text-xs mt-2 text-right" style="color:var(--color-success);font-weight:600">✓ Delivered</p>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Items -->
            <div class="card p-6 mb-6 anim-fade-up anim-delay-4">
                <h2 class="font-semibold mb-4">Items</h2>
                <div class="flex flex-col gap-3">
                    <template x-for="item in order.items" :key="item.id">
                        <div class="flex justify-between text-sm">
                            <span x-text="item.quantity + '× ' + item.name"></span>
                            <span class="font-medium" x-text="fmtCurrency(item.price * item.quantity)"></span>
                        </div>
                    </template>
                    <hr style="border-color:var(--color-cream-2)">
                    <div class="flex justify-between text-sm">
                        <span style="color:var(--color-warm-muted)">Subtotal</span>
                        <span x-text="fmtCurrency(order.subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span style="color:var(--color-warm-muted)">Delivery fee</span>
                        <span x-text="fmtCurrency(order.delivery_fee)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span style="color:var(--color-warm-muted)">Tax</span>
                        <span x-text="fmtCurrency(order.tax)"></span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>Total</span>
                        <span x-text="fmtCurrency(order.total)"></span>
                    </div>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="card p-6 mb-6 anim-fade-up anim-delay-5">
                <h2 class="font-semibold mb-2">Delivery Address</h2>
                <p class="text-sm" style="color:var(--color-warm-muted)" x-text="
                    [order.delivery_address?.street, order.delivery_address?.city, order.delivery_address?.state, order.delivery_address?.zip_code]
                    .filter(Boolean).join(', ')
                "></p>
            </div>

            <!-- Cancel button -->
            <template x-if="['pending','confirmed'].includes(order.status)">
                <button @click="cancel()" :disabled="cancelling"
                        class="btn-outline w-full flex items-center justify-center gap-2"
                        style="border-color:var(--color-error);color:var(--color-error)">
                    <span x-show="cancelling" class="spinner"></span>
                    <span x-text="cancelling ? 'Cancelling…' : 'Cancel Order'"></span>
                </button>
            </template>

            <!-- Live refresh notice -->
            <p class="text-center text-xs mt-6 flex items-center justify-center gap-1.5" style="color:var(--color-warm-muted)">
                <template x-if="!['delivered','cancelled'].includes(order.status)">
                    <span class="live-dot"></span>
                </template>
                <span x-text="['delivered','cancelled'].includes(order.status) ? 'Order complete' : 'Auto-refreshes every 15 seconds'"></span>
            </p>
        </div>
    </template>

    <!-- ── Rating Modal ── -->
    <template x-teleport="body">
    <div x-show="ratingModal.open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.55)">
        <div @click.outside="ratingModal.open = false"
             class="card w-full max-w-sm"
             style="padding:1.75rem">

            <!-- Header -->
            <div class="text-center mb-5">
                <div class="text-4xl mb-2">🎉</div>
                <h2 class="text-lg font-semibold" style="color:var(--color-warm-text)">How was your order?</h2>
                <p class="text-sm mt-1" style="color:var(--color-warm-muted)">Your feedback helps us improve</p>
            </div>

            <!-- Food Rating -->
            <div class="mb-5">
                <p class="text-sm font-medium mb-2" style="color:var(--color-warm-text)">Food Quality</p>
                <div class="flex gap-1 justify-center">
                    <template x-for="star in [1,2,3,4,5]" :key="star">
                        <button type="button"
                                @click="ratingModal.foodRating = star"
                                class="text-3xl transition-transform hover:scale-110 focus:outline-none"
                                :class="star <= ratingModal.foodRating ? 'opacity-100' : 'opacity-30'">
                            ⭐
                        </button>
                    </template>
                </div>
                <p class="text-center text-xs mt-1" style="color:var(--color-warm-muted)"
                   x-text="['','Poor','Fair','Good','Great','Excellent!'][ratingModal.foodRating] || 'Tap to rate'"></p>
            </div>

            <!-- Delivery Rating (only if there is a delivery partner) -->
            <template x-if="order && order.delivery_partner">
                <div class="mb-5">
                    <p class="text-sm font-medium mb-2" style="color:var(--color-warm-text)">Delivery Experience</p>
                    <div class="flex gap-1 justify-center">
                        <template x-for="star in [1,2,3,4,5]" :key="star">
                            <button type="button"
                                    @click="ratingModal.deliveryRating = star"
                                    class="text-3xl transition-transform hover:scale-110 focus:outline-none"
                                    :class="star <= ratingModal.deliveryRating ? 'opacity-100' : 'opacity-30'">
                                ⭐
                            </button>
                        </template>
                    </div>
                    <p class="text-center text-xs mt-1" style="color:var(--color-warm-muted)"
                       x-text="['','Poor','Fair','Good','Great','Excellent!'][ratingModal.deliveryRating] || 'Tap to rate'"></p>
                </div>
            </template>

            <!-- Actions -->
            <div class="flex gap-3 mt-2">
                <button type="button"
                        @click="skipRating()"
                        class="btn-ghost flex-1 text-sm"
                        :disabled="ratingModal.submitting">
                    Skip
                </button>
                <button type="button"
                        @click="submitRating()"
                        class="btn-brand flex-1 text-sm flex items-center justify-center gap-2"
                        :disabled="ratingModal.submitting || ratingModal.foodRating === 0">
                    <span x-show="ratingModal.submitting" class="spinner !w-4 !h-4"></span>
                    <span x-text="ratingModal.submitting ? 'Submitting…' : 'Submit'"></span>
                </button>
            </div>
        </div>
    </div>
    </template>
</div>
@endsection
