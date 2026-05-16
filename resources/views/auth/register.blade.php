@extends('layouts.app')
@section('title', 'Create Account')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-16" style="background:var(--color-cream)">
    <div class="w-full max-w-md">

        <!-- Brand -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2" style="text-decoration:none">
                <span style="font-size:2.5rem">🍕</span>
                <span style="font-family:var(--font-display);font-size:1.75rem;font-weight:700;color:var(--color-brand)">FoodieExpress</span>
            </a>
            <p class="mt-2" style="color:var(--color-warm-muted)">Create your customer account</p>
        </div>

        <!-- Card -->
        <div class="card p-8" x-data="registerPage">
            <form @submit.prevent="submit" novalidate>
                <div class="flex flex-col gap-5">

                    <div>
                        <label class="label">Full name</label>
                        <input type="text" x-model="name" required class="input" placeholder="Jane Doe">
                    </div>

                    <div>
                        <label class="label">Email address</label>
                        <input type="email" x-model="email" required class="input" placeholder="you@example.com">
                    </div>

                    <div>
                        <label class="label">Phone number</label>
                        <input type="tel" x-model="phone" class="input" placeholder="+1 555 000 0000">
                    </div>

                    <div>
                        <label class="label">Password</label>
                        <div class="relative">
                            <input :type="showPwd ? 'text' : 'password'" x-model="password" required
                                   class="input pr-12" placeholder="Min. 8 characters">
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
                        <span x-text="loading ? 'Creating account…' : 'Create Account'"></span>
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm" style="color:var(--color-warm-muted)">
                Already have an account?
                <a href="/login" style="color:var(--color-brand);font-weight:600">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
