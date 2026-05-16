@extends('layouts.app')
@section('title', 'FoodieExpress — Food delivery near you')

@section('content')
<div x-data="homePage" x-init="init()">

    <!-- Hero -->
    <section class="py-20 px-4 text-center" style="background:linear-gradient(135deg,var(--color-cream) 0%,#FFF3E8 100%)">
        <h1 style="font-family:var(--font-display);font-size:clamp(2rem,5vw,3.5rem);font-weight:800;color:#1a0a00;line-height:1.15">
            Great food,<br><span style="color:var(--color-brand)">delivered fast</span>
        </h1>
        <p class="mt-4 text-lg max-w-md mx-auto" style="color:var(--color-warm-muted)">
            Explore hundreds of restaurants in your neighbourhood and get it to your door.
        </p>

        <!-- Search -->
        <form @submit.prevent="load()" class="mt-8 flex max-w-xl mx-auto gap-2">
            <input x-model="search" type="text"
                   placeholder="Search restaurants or cuisines…"
                   class="input flex-1 text-base shadow-sm">
            <button type="submit" class="btn-brand px-6">Search</button>
        </form>
    </section>

    <!-- Cuisine filter pills -->
    <section class="max-w-6xl mx-auto px-4 pt-10">
        <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
            <button @click="setCuisine('')"
                    :class="cuisine==='' ? 'btn-brand' : 'btn-outline'"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-all">
                All
            </button>
            <template x-for="c in cuisineOptions" :key="c">
                <button @click="setCuisine(c)"
                        :class="cuisine===c ? 'btn-brand' : 'btn-outline'"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-all"
                        x-text="c"></button>
            </template>
        </div>

        <!-- Sort -->
        <div class="flex items-center justify-between mt-6 mb-4">
            <h2 style="font-family:var(--font-display);font-size:1.375rem;font-weight:700">
                <span x-show="!loading" x-text="restaurants.length + ' restaurants'"></span>
                <span x-show="loading">Finding restaurants…</span>
            </h2>
            <select x-model="sort" @change="load()" class="input" style="width:auto;padding:0.4rem 0.75rem;font-size:0.875rem">
                <option value="rating">Top rated</option>
                <option value="distance">Nearest</option>
                <option value="delivery_time">Fastest</option>
                <option value="delivery_fee">Lowest fee</option>
            </select>
        </div>
    </section>

    <!-- Restaurant grid -->
    <section class="max-w-6xl mx-auto px-4 pb-20">

        <!-- Skeleton -->
        <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="n in 6">
                <div class="card overflow-hidden">
                    <div class="skeleton h-44 w-full"></div>
                    <div class="p-4 flex flex-col gap-2">
                        <div class="skeleton h-5 w-3/4 rounded"></div>
                        <div class="skeleton h-4 w-1/2 rounded"></div>
                        <div class="skeleton h-4 w-full rounded"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Results -->
        <div x-show="!loading && restaurants.length > 0"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="r in restaurants" :key="r.id">
                <a :href="'/restaurants/'+r.id" class="card card-hover overflow-hidden block" style="text-decoration:none;color:inherit">
                    <!-- Image -->
                    <div class="relative h-44 overflow-hidden" style="background:var(--color-cream-2)">
                        <img x-show="r.image_path" :src="r.image_path" :alt="r.name"
                             class="w-full h-full object-cover">
                        <div x-show="!r.image_path" class="absolute inset-0 flex items-center justify-center" style="font-size:3.5rem">🍽️</div>

                        <template x-if="!r.is_active">
                            <span class="absolute top-3 left-3 badge badge-warning">Closed</span>
                        </template>
                        <template x-if="r.is_active && r.estimated_delivery_time">
                            <span class="absolute top-3 right-3 rounded-full px-3 py-1 text-xs font-bold text-white"
                                  style="background:rgba(0,0,0,0.55)"
                                  x-text="r.estimated_delivery_time + ' min'"></span>
                        </template>
                    </div>

                    <div class="p-4">
                        <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:0.25rem" x-text="r.name"></h3>

                        <div class="flex flex-wrap gap-1 mb-2">
                            <template x-for="c in (r.cuisine_types||[]).slice(0,3)" :key="c">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      style="background:var(--color-cream-2);color:var(--color-warm-muted)"
                                      x-text="c"></span>
                            </template>
                        </div>

                        <div class="flex items-center gap-3 text-sm" style="color:var(--color-warm-muted)">
                            <span class="flex items-center gap-1">
                                ⭐ <span x-text="parseFloat(r.rating||0).toFixed(1)"></span>
                            </span>
                            <span>•</span>
                            <template x-if="parseFloat(r.delivery_fee) === 0">
                                <span class="font-semibold" style="color:var(--color-brand)">Free delivery</span>
                            </template>
                            <template x-if="parseFloat(r.delivery_fee) > 0">
                                <span x-text="fmtCurrency(r.delivery_fee) + ' delivery'"></span>
                            </template>
                            <template x-if="r.distance_km != null">
                                <span>• <span x-text="parseFloat(r.distance_km).toFixed(1) + ' km'"></span></span>
                            </template>
                        </div>
                    </div>
                </a>
            </template>
        </div>

        <!-- Empty -->
        <div x-show="!loading && restaurants.length === 0" class="text-center py-20">
            <span style="font-size:4rem">🍽️</span>
            <p class="mt-4 text-lg font-semibold" style="color:var(--color-warm-muted)">No restaurants found</p>
            <p class="text-sm" style="color:var(--color-warm-muted)">Try a different search or remove filters.</p>
            <button @click="search='';cuisine='';load()" class="btn-brand mt-4">Clear filters</button>
        </div>

    </section>
</div>
@endsection
