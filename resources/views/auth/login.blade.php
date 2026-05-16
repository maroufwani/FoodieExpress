@extends('layouts.app')
@section('title', 'Sign In')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-16" style="background:var(--color-cream)">
    <div class="w-full max-w-md">

        <!-- Brand -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2" style="text-decoration:none">
                <span style="font-size:2.5rem">🍕</span>
                <span style="font-family:var(--font-display);font-size:1.75rem;font-weight:700;color:var(--color-brand)">FoodieExpress</span>
            </a>
            <p class="mt-2" style="color:var(--color-warm-muted)">Welcome back — sign in to continue</p>
        </div>

        <!-- Card -->
        <div class="card p-8" x-data="loginPage">
            <form @submit.prevent="submit" novalidate>
                <div class="flex flex-col gap-5">

                    <div>
                        <label class="label">Email address</label>
                        <input type="email" x-model="email" required
                               class="input" placeholder="you@example.com"
                               autocomplete="email">
                    </div>

                    <div>
                        <label class="label">Password</label>
                        <div class="relative">
                            <input :type="showPwd ? 'text' : 'password'" x-model="password" required
                                   class="input pr-12" placeholder="••••••••"
                                   autocomplete="current-password">
                            <button type="button" @click="showPwd=!showPwd"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-sm"
                                    style="color:var(--color-warm-muted)">
                                <span x-text="showPwd ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-brand w-full flex items-center justify-center gap-2"
                            :disabled="loading">
                        <span x-show="loading" class="spinner"></span>
                        <span x-text="loading ? 'Signing in…' : 'Sign In'"></span>
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm" style="color:var(--color-warm-muted)">
                No account?
                <a href="/register" style="color:var(--color-brand);font-weight:600">Create one free</a>
            </p>
        </div>
    </div>
</div>
@endsection
