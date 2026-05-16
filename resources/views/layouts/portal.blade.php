<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal') — FoodieExpress</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="portal-body">

<!-- ─── Toast Notifications ────────────────────────────────────────────── -->
<div x-cloak class="fixed top-4 right-4 z-300 flex flex-col gap-2"
     x-data x-show="$store.notify.toasts.length > 0">
    <template x-for="toast in $store.notify.toasts" :key="toast.id">
        <div class="toast flex items-start gap-3" :class="'toast-' + toast.type"
             style="animation: slideIn 0.25s ease">
            <span class="text-lg leading-none mt-0.5">
                <template x-if="toast.type==='success'">✅</template>
                <template x-if="toast.type==='error'">❌</template>
                <template x-if="toast.type==='info'">ℹ️</template>
            </span>
            <p class="text-sm font-medium flex-1" style="color:#1f2937" x-text="toast.msg"></p>
            <button @click="$store.notify.remove(toast.id)" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>
    </template>
</div>

<!-- ─── Mobile sidebar toggle ───────────────────────────────────────────── -->
<button class="md:hidden fixed top-4 left-4 z-60 w-10 h-10 rounded-xl flex items-center justify-center"
        style="background:var(--color-portal-surface);border:1px solid var(--color-portal-border)"
        x-data @click="document.querySelector('.portal-sidebar').classList.toggle('open')">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-portal-text)"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>

<!-- ─── Sidebar ──────────────────────────────────────────────────────────── -->
<aside class="portal-sidebar" x-data>
    <div class="p-5 border-b" style="border-color:var(--color-portal-border)">
        <a href="/" class="flex items-center gap-2" style="text-decoration:none">
            <span style="font-size:1.5rem">🍕</span>
            <div>
                <p style="font-family:var(--font-display);font-weight:700;color:var(--color-brand);font-size:1.1rem;line-height:1.2">FoodieExpress</p>
                <p style="font-size:0.7rem;color:var(--color-portal-muted);text-transform:uppercase;letter-spacing:0.05em" x-text="($store.auth.role||'').replace('_',' ')"></p>
            </div>
        </a>
    </div>

    <nav class="flex-1 py-4 overflow-y-auto">
        @yield('sidebar-nav')
    </nav>

    <div class="p-4 border-t" style="border-color:var(--color-portal-border)" x-data>
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
                 style="background:var(--color-brand)"
                 x-text="($store.auth.user?.name||'U')[0].toUpperCase()"></div>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate" style="color:var(--color-portal-text)" x-text="$store.auth.user?.name"></p>
                <p class="text-xs truncate" style="color:var(--color-portal-muted)" x-text="$store.auth.user?.email"></p>
            </div>
        </div>
        <button @click="$store.auth.logout()" class="w-full text-left btn-portal-ghost justify-center" style="color:var(--color-error)">
            Sign Out
        </button>
    </div>
</aside>

<!-- ─── Main Content ─────────────────────────────────────────────────────── -->
<div class="portal-main">

    <!-- Notification permission banner (restaurant owners only) -->
    <div x-data="notifBanner" x-show="show"
         style="display:none;background:#fff7ed;border-bottom:1px solid #fed7aa;color:#9a3412"
         class="flex items-center gap-3 px-5 py-3 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="flex-1">Enable browser notifications to be alerted instantly when new orders arrive.</span>
        <button @click="enable()" class="shrink-0 font-semibold underline underline-offset-2 hover:opacity-75">Enable</button>
        <button @click="show = false" class="shrink-0 text-xl leading-none hover:opacity-75 ml-1" aria-label="Dismiss">&times;</button>
    </div>

    <!-- Top bar -->
    <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color:var(--color-portal-border)">
        <h1 style="font-family:var(--font-display);font-size:1.375rem;font-weight:700;color:var(--color-portal-text)">
            @yield('page-title', 'Dashboard')
        </h1>
        @yield('page-actions')
    </div>
    <div class="p-6">
        @yield('content')
    </div>
</div>

<style>
@keyframes slideIn { from { opacity:0; transform: translateX(20px); } to { opacity:1; transform: translateX(0); } }
</style>

@stack('scripts')
</body>
</html>
