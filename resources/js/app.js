import './bootstrap';
import Alpine from 'alpinejs';
import Persist from '@alpinejs/persist';

Alpine.plugin(Persist);

// ─── Axios defaults ────────────────────────────────────────────────────────
window.axios.defaults.baseURL = '/api';
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Inject Sanctum token on every request
window.axios.interceptors.request.use(cfg => {
    const token = localStorage.getItem('token');
    if (token) cfg.headers['Authorization'] = `Bearer ${token}`;
    return cfg;
});

// Auto-redirect to login on 401
window.axios.interceptors.response.use(
    res => res,
    err => {
        if (err.response?.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            if (!window.location.pathname.startsWith('/login')) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(err);
    }
);

// ─── Helper ────────────────────────────────────────────────────────────────
window.apiErr = (err) => {
    const data = err.response?.data;
    if (data?.errors) return Object.values(data.errors).flat().join(' ');
    return data?.message || 'Something went wrong.';
};

window.fmtCurrency = (v) => '₹' + parseFloat(v).toFixed(2);
window.fmtDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
window.fmtDateTime = (d) => new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

window.statusLabel = (s) => ({
    pending: 'Pending', confirmed: 'Confirmed', preparing: 'Preparing',
    ready: 'Ready', out_for_delivery: 'Out for Delivery', picked_up: 'At Your Doorstep',
    delivered: 'Delivered', cancelled: 'Cancelled',
}[s] || s);

// ─── Browser Push Notification helpers (restaurant new-order alerts) ────────
window._playOrderSound = () => {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        [[880, 0, 0.12], [1100, 0.13, 0.12], [880, 0.28, 0.18]].forEach(([freq, start, dur]) => {
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.type = 'sine'; osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.25, ctx.currentTime + start);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + dur);
            osc.start(ctx.currentTime + start);
            osc.stop(ctx.currentTime + start + dur + 0.01);
        });
    } catch(_) {}
};

window._notifyNewOrder = (order) => {
    window._playOrderSound();
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    const n = new Notification('New Order! 🛎️', {
        body: `Order #${order.id} · ${window.fmtCurrency(order.total)}`,
        icon: '/favicon.ico',
        tag: `order-${order.id}`,
        requireInteraction: true,
    });
    n.onclick = () => { window.focus(); window.location.href = '/restaurant/orders'; n.close(); };
};

// ─── Auth Store ────────────────────────────────────────────────────────────
Alpine.store('auth', {
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    token: localStorage.getItem('token') || null,

    get isLoggedIn() { return !!this.token; },
    get role() { return this.user?.role || null; },
    get isCustomer() { return this.role === 'customer'; },
    get isOwner() { return this.role === 'restaurant_owner'; },
    get isDelivery() { return this.role === 'delivery'; },
    get isAdmin() { return this.role === 'admin'; },

    setSession(user, token) {
        this.user = user; this.token = token;
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('token', token);
    },
    clearSession() {
        this.user = null; this.token = null;
        localStorage.removeItem('user');
        localStorage.removeItem('token');
    },
    async logout() {
        try { await window.axios.post('/auth/logout'); } catch (_) {}
        this.clearSession();
        Alpine.store('cart').clear();
        window.location.href = '/login';
    },
    portalHome() {
        const map = { admin: '/admin/dashboard', restaurant_owner: '/restaurant/dashboard', delivery: '/delivery/dashboard', customer: '/' };
        return map[this.role] || '/';
    },
});

// ─── Notifications Store ───────────────────────────────────────────────────
Alpine.store('notify', {
    toasts: [],
    _id: 0,
    add(msg, type = 'info', duration = 4000) {
        const id = ++this._id;
        this.toasts.push({ id, msg, type });
        setTimeout(() => this.remove(id), duration);
    },
    remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
    success(msg) { this.add(msg, 'success'); },
    error(msg)   { this.add(msg, 'error', 5000); },
    info(msg)    { this.add(msg, 'info'); },
});

// ─── Delivery Location Store ───────────────────────────────────────────────
Alpine.store('location', {
    addresses: [],
    selectedId: parseInt(localStorage.getItem('deliveryAddressId') || '0') || null,
    open: false,
    loaded: false,

    get selected() {
        return this.addresses.find(a => a.id === this.selectedId) || this.addresses[0] || null;
    },
    get label() {
        const a = this.selected;
        if (!a) return 'Select address';
        return a.label || a.recipient_name || 'Address';
    },
    get sublabel() {
        const a = this.selected;
        if (!a) return '';
        return [a.apartment, a.city].filter(Boolean).join(', ');
    },

    async load() {
        if (!Alpine.store('auth').isCustomer) return;
        try {
            const { data } = await window.axios.get('/auth/addresses');
            this.addresses = data;
            // If saved id no longer exists, fall back to first
            if (this.selectedId && !data.find(a => a.id === this.selectedId)) {
                this.selectedId = data[0]?.id || null;
            }
            if (!this.selectedId && data.length) {
                this.selectedId = data[0].id;
                localStorage.setItem('deliveryAddressId', this.selectedId);
            }
        } catch(_) {}
        finally { this.loaded = true; }
    },
    select(id) {
        this.selectedId = id;
        localStorage.setItem('deliveryAddressId', id);
        this.open = false;
    },
});

// ─── Cart Store ────────────────────────────────────────────────────────────
const CART_VERSION = '5'; // bump when cartKey format changes — forces stale carts to clear
if (localStorage.getItem('cart_version') !== CART_VERSION) {
    localStorage.removeItem('cart_items');
    localStorage.removeItem('cart_restaurant');
    localStorage.setItem('cart_version', CART_VERSION);
}

function buildCartKey(itemId, sizeLabel, extras) {
    const extrasKey = (extras && extras.length)
        ? '_x' + [...extras]
            .map(e => e.id != null ? String(e.id) : 'c' + String(e.name || '').replace(/\s+/g, '').toLowerCase())
            .sort()
            .join('-')
        : '';
    return itemId + (sizeLabel ? '_' + sizeLabel : '') + extrasKey;
}

Alpine.store('cart', {
    items: JSON.parse(localStorage.getItem('cart_items') || '[]').map(i => ({
        ...i,
        cartKey: buildCartKey(i.id, i.size, i.extras || []),
        is_vegetarian: i.is_vegetarian ?? false,
        is_vegan:      i.is_vegan      ?? false,
        extras: (i.extras || []).map(e => ({
            ...e,
            is_vegetarian: e.is_vegetarian ?? false,
            is_vegan:      e.is_vegan      ?? false,
        })),
    })),
    restaurant: JSON.parse(localStorage.getItem('cart_restaurant') || 'null'),
    open: false,

    get count() { return this.items.reduce((s, i) => s + i.quantity, 0); },
    get subtotal() {
        return this.items.reduce((s, i) => {
            const extrasPrice = (i.extras || []).reduce((es, e) => es + e.price, 0);
            return s + (i.price + extrasPrice) * i.quantity;
        }, 0);
    },
    get isEmpty() { return this.items.length === 0; },

    save() {
        localStorage.setItem('cart_items', JSON.stringify(this.items));
        localStorage.setItem('cart_restaurant', JSON.stringify(this.restaurant));
    },
    clear() {
        this.items = []; this.restaurant = null;
        localStorage.removeItem('cart_items');
        localStorage.removeItem('cart_restaurant');
    },
    addItem(restaurant, item, size = null, extras = []) {
        if (this.restaurant && this.restaurant.id !== restaurant.id) return false;
        if (!this.restaurant) this.restaurant = { id: restaurant.id, name: restaurant.name, delivery_fee: restaurant.delivery_fee };
        const cartKey = buildCartKey(item.id, size?.label || null, extras);
        const price   = size ? parseFloat(size.price) : parseFloat(item.price);
        const existing = this.items.find(i => i.cartKey === cartKey);
        if (existing) { existing.quantity++; }
        else this.items.push({ cartKey, id: item.id, name: item.name, price, quantity: 1, size: size?.label || null, extras, is_vegetarian: !!item.is_vegetarian, is_vegan: !!item.is_vegan });
        this.save(); return true;
    },
    switchRestaurant(restaurant, item, size = null, extras = []) {
        this.clear();
        this.addItem(restaurant, item, size, extras);
    },
    increment(cartKey) {
        const i = this.items.find(x => x.cartKey === cartKey);
        if (i) { i.quantity++; this.save(); }
    },
    decrement(cartKey) {
        const i = this.items.find(x => x.cartKey === cartKey);
        if (i) {
            i.quantity--;
            if (i.quantity <= 0) this.items = this.items.filter(x => x.cartKey !== cartKey);
            this.save();
        }
    },
    remove(cartKey) { this.items = this.items.filter(x => x.cartKey !== cartKey); this.save(); },
    removeExtra(cartKey, extraIndex) {
        const item = this.items.find(x => x.cartKey === cartKey);
        if (item) { item.extras = (item.extras || []).filter((_, i) => i !== extraIndex); this.save(); }
    },
    qtyFor(cartKey) { return this.items.find(x => x.cartKey === cartKey)?.quantity || 0; },
    totalQtyFor(itemId, sizeLabel) {
        const prefix = itemId + (sizeLabel ? '_' + sizeLabel : '');
        return this.items
            .filter(x => x.cartKey === prefix || x.cartKey.startsWith(prefix + '_x'))
            .reduce((s, x) => s + x.quantity, 0);
    },
    decrementAny(itemId, sizeLabel) {
        const prefix = itemId + (sizeLabel ? '_' + sizeLabel : '');
        const match = this.items.find(x => x.cartKey === prefix || x.cartKey.startsWith(prefix + '_x'));
        if (match) this.decrement(match.cartKey);
    },
});

// ─── Page Components ───────────────────────────────────────────────────────

// ── Home ──
Alpine.data('homePage', () => ({
    restaurants: [], loading: false,
    search: '', cuisine: '', sort: 'rating',
    lat: null, lng: null, locationDenied: false, manualLocation: '', locating: false,

    get cuisineOptions() {
        const seen = new Set();
        this.restaurants.forEach(r => (r.cuisine_types || []).forEach(c => seen.add(c)));
        return [...seen].sort();
    },

    async init() {
        await this.detectLocation();
        await this.load();
    },
    async detectLocation() {
        if (localStorage.getItem('userLat')) {
            this.lat = parseFloat(localStorage.getItem('userLat'));
            this.lng = parseFloat(localStorage.getItem('userLng'));
            return;
        }
        if (!navigator.geolocation) return;
        await new Promise(resolve => {
            navigator.geolocation.getCurrentPosition(pos => {
                this.lat = pos.coords.latitude;
                this.lng = pos.coords.longitude;
                localStorage.setItem('userLat', this.lat);
                localStorage.setItem('userLng', this.lng);
                resolve();
            }, () => { this.locationDenied = true; resolve(); });
        });
    },
    async locateMe() {
        if (!navigator.geolocation) { Alpine.store('notify').error('Geolocation not supported by your browser.'); return; }
        this.locating = true;
        localStorage.removeItem('userLat');
        localStorage.removeItem('userLng');
        await new Promise(resolve => {
            navigator.geolocation.getCurrentPosition(pos => {
                this.lat = pos.coords.latitude;
                this.lng = pos.coords.longitude;
                this.locationDenied = false;
                localStorage.setItem('userLat', this.lat);
                localStorage.setItem('userLng', this.lng);
                resolve();
            }, () => { this.locationDenied = true; resolve(); }, { enableHighAccuracy: true, timeout: 10000 });
        });
        this.locating = false;
        await this.load();
        if (!this.locationDenied) Alpine.store('notify').success('Location updated — showing nearby restaurants.');
    },
    async load() {
        this.loading = true;
        try {
            const params = { sort: this.sort };
            if (this.search) params.search = this.search;
            if (this.cuisine) params.cuisine = this.cuisine;
            const { data } = await window.axios.get('/restaurants', { params });
            this.restaurants = data;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    setCuisine(c) { this.cuisine = this.cuisine === c ? '' : c; this.load(); },
}));

// ── Restaurant Detail ──
Alpine.data('restaurantDetail', (id) => ({
    restaurant: null, menu: {}, loading: true,
    confirmSwitch: false, pendingItem: null, pendingSize: null,
    extrasModal: { open: false, item: null, size: null, selected: {}, groupSelected: {} },
    search: '', sortBy: 'default', vegOnly: false,

    get filteredMenu() {
        const q = this.search.toLowerCase().trim();
        const result = {};
        for (const [cat, items] of Object.entries(this.menu)) {
            let filtered = items.filter(item => {
                if (this.vegOnly && !item.is_vegetarian) return false;
                if (q && !item.name.toLowerCase().includes(q) && !(item.description||'').toLowerCase().includes(q)) return false;
                return true;
            });
            if (this.sortBy === 'price_asc') filtered = [...filtered].sort((a,b) => parseFloat(a.price)-parseFloat(b.price));
            else if (this.sortBy === 'price_desc') filtered = [...filtered].sort((a,b) => parseFloat(b.price)-parseFloat(a.price));
            else if (this.sortBy === 'name') filtered = [...filtered].sort((a,b) => a.name.localeCompare(b.name));
            if (filtered.length) result[cat] = filtered;
        }
        return result;
    },
    get filteredMenuIsEmpty() { return Object.keys(this.filteredMenu).length === 0; },

    get toppings() { return this.menu['Extra Toppings'] || []; },
    isPizza(item) { return item && item.sizes && item.sizes.length > 0 && !item.category?.toLowerCase().includes('topping'); },
    hasOptionGroups(item) { return !!(item.option_groups && item.option_groups.length > 0); },

    async init() {
        try {
            const { data } = await window.axios.get(`/restaurants/${id}`);
            this.restaurant = data.restaurant;
            this.menu = data.menu;
            // Remove any cart items that are now disabled
            const cart = Alpine.store('cart');
            if (cart.restaurant?.id === data.restaurant.id && !cart.isEmpty) {
                const allItems = Object.values(data.menu).flat();
                const availableIds = new Set(allItems.filter(m => m.is_available).map(m => String(m.id)));
                const unavailable = cart.items.filter(ci => !availableIds.has(String(ci.id)));
                if (unavailable.length) {
                    unavailable.forEach(ci => cart.remove(ci.cartKey));
                    const names = unavailable.map(ci => ci.name).join(', ');
                    Alpine.store('notify').error(`Removed from cart (no longer available): ${names}`);
                }
            }
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    tryAdd(item, size = null) {
        if (!Alpine.store('auth').isLoggedIn) { window.location.href = '/login'; return; }
        const cart = Alpine.store('cart');
        if (cart.restaurant && cart.restaurant.id !== this.restaurant.id) {
            this.pendingItem = item; this.pendingSize = size; this.confirmSwitch = true; return;
        }
        if (this.hasOptionGroups(item) || (this.isPizza(item) && this.toppings.length > 0)) {
            this.extrasModal = { open: true, item, size, selected: {}, groupSelected: {} };
            return;
        }
        cart.addItem(this.restaurant, item, size);
        const label = size ? `${item.name} (${size.label})` : item.name;
        Alpine.store('notify').success(`${label} added to cart`);
    },
    toggleExtra(topping) {
        const key = topping.id;
        if (this.extrasModal.selected[key]) {
            const updated = { ...this.extrasModal.selected };
            delete updated[key];
            this.extrasModal.selected = updated;
        } else {
            this.extrasModal.selected = { ...this.extrasModal.selected, [key]: topping };
        }
    },
    isExtraSelected(toppingId) {
        return !!this.extrasModal.selected[toppingId];
    },
    toggleGroupOption(gi, oi) {
        const key = `${gi}:${oi}`;
        if (this.extrasModal.groupSelected[key]) {
            const updated = { ...this.extrasModal.groupSelected };
            delete updated[key];
            this.extrasModal.groupSelected = updated;
        } else {
            const group = this.extrasModal.item.option_groups[gi];
            const opt   = group.options[oi];
            this.extrasModal.groupSelected = {
                ...this.extrasModal.groupSelected,
                [key]: { group: group.heading, name: opt.name, price: parseFloat(opt.price || 0) },
            };
        }
    },
    isGroupOptionSelected(gi, oi) {
        return !!(this.extrasModal.groupSelected[`${gi}:${oi}`]);
    },
    toppingPriceForPizzaSize(topping) {
        const pizzaSize = this.extrasModal.size;
        if (!pizzaSize || !topping.sizes) return topping.price;
        const match = topping.sizes.find(s => s.label === pizzaSize.label);
        return match ? match.price : topping.price;
    },
    confirmAddWithExtras(skipExtras = false) {
        const cart = Alpine.store('cart');
        const { item, size, selected, groupSelected } = this.extrasModal;
        // Toppings
        const toppingExtras = skipExtras ? [] : Object.values(selected).map(topping => {
            const toppingSize = size && topping.sizes
                ? topping.sizes.find(s => s.label === size.label) || null
                : null;
            const price = toppingSize ? parseFloat(toppingSize.price) : parseFloat(topping.price);
            return { id: topping.id, name: topping.name, price, size: size?.label || null, is_vegetarian: !!topping.is_vegetarian, is_vegan: !!topping.is_vegan };
        });
        // Option group selections
        const groupExtras = skipExtras ? [] : Object.values(groupSelected).map(opt => ({
            id: null, name: opt.name, price: opt.price, group: opt.group, is_vegetarian: true, is_vegan: false,
        }));
        const allExtras = [...toppingExtras, ...groupExtras];
        cart.addItem(this.restaurant, item, size, allExtras);
        const label = size ? `${item.name} (${size.label})` : item.name;
        const count = allExtras.length;
        Alpine.store('notify').success(label + (count > 0 ? ` + ${count} add-on${count > 1 ? 's' : ''}` : '') + ' added to cart');
        this.extrasModal = { open: false, item: null, size: null, selected: {}, groupSelected: {} };
    },
    confirmSwitchCart() {
        Alpine.store('cart').clear();
        Alpine.store('cart').restaurant = { id: this.restaurant.id, name: this.restaurant.name, delivery_fee: this.restaurant.delivery_fee };
        this.confirmSwitch = false;
        if (this.hasOptionGroups(this.pendingItem) || (this.isPizza(this.pendingItem) && this.toppings.length > 0)) {
            this.extrasModal = { open: true, item: this.pendingItem, size: this.pendingSize, selected: {}, groupSelected: {} };
        } else {
            Alpine.store('cart').addItem(this.restaurant, this.pendingItem, this.pendingSize);
            const label = this.pendingSize ? `${this.pendingItem.name} (${this.pendingSize.label})` : this.pendingItem.name;
            Alpine.store('notify').success(`${label} added to cart`);
        }
        this.pendingItem = null; this.pendingSize = null;
    },
}));

// ── Login ──
Alpine.data('loginPage', () => ({
    email: '', password: '', loading: false, showPwd: false,
    async submit() {
        this.loading = true;
        try {
            const { data } = await window.axios.post('/auth/login', { email: this.email, password: this.password });
            Alpine.store('auth').setSession(data.user, data.token);
            window.location.href = Alpine.store('auth').portalHome();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
}));

// ── Register ──
Alpine.data('registerPage', () => ({
    name: '', email: '', password: '', phone: '', loading: false, showPwd: false,
    async submit() {
        this.loading = true;
        try {
            const payload = { name: this.name, email: this.email, password: this.password, phone: this.phone, role: 'customer' };
            const { data } = await window.axios.post('/auth/register', payload);
            Alpine.store('auth').setSession(data.user, data.token);
            window.location.href = Alpine.store('auth').portalHome();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
}));

// ── Customer Orders ──
Alpine.data('customerOrders', () => ({
    orders: [], loading: false, filter: 'all', page: 1, lastPage: 1,

    async init() {
        this._requireRole('customer');
        await this.load();
    },
    async load() {
        this.loading = true;
        try {
            const { data } = await window.axios.get('/orders', { params: { filter: this.filter, page: this.page } });
            this.orders = data.data; this.lastPage = data.last_page;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    setFilter(f) { this.filter = f; this.page = 1; this.load(); },
    _requireRole(role) {
        const auth = Alpine.store('auth');
        if (!auth.isLoggedIn) { window.location.href = '/login'; return; }
        if (auth.role !== role) { window.location.href = auth.portalHome(); }
    },
}));

// ── Customer Order Detail ──
Alpine.data('orderDetail', (id) => ({
    order: null, loading: true, cancelling: false,
    refreshTimer: null,
    ratingModal: { open: false, foodRating: 0, deliveryRating: 0, submitting: false },

    get customSteps() {
        const flow = this.order?.restaurant?.status_flow;
        if (!flow?.length) return ['pending','confirmed','preparing','ready','out_for_delivery','picked_up','delivered'];
        return flow.map(s => s.key);
    },
    customStatusLabel(s) {
        const found = this.order?.restaurant?.status_flow?.find(f => f.key === s);
        return found ? found.label : window.statusLabel(s);
    },

    async init() {
        await this.load();
        this.refreshTimer = setInterval(() => this.load(), 15000);
    },
    destroy() { clearInterval(this.refreshTimer); },
    async load() {
        try {
            const { data } = await window.axios.get(`/orders/${id}`);
            this.order = data;
            // Auto-open rating modal once when order is delivered and not yet rated
            if (
                data.status === 'delivered' &&
                data.food_rating == null &&
                !localStorage.getItem(`order_rating_skipped_${id}`) &&
                !this.ratingModal.open
            ) {
                this.ratingModal.open = true;
            }
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    stepIndex(status) {
        const idx = this.customSteps.indexOf(status);
        return idx >= 0 ? idx : (status === 'cancelled' ? -1 : this.customSteps.length);
    },
    statusDescription(status) {
        const d = {
            pending:          'Waiting for the restaurant to accept your order.',
            confirmed:        'The restaurant has confirmed your order and is getting ready to prepare it.',
            preparing:        'The kitchen is cooking your meal right now.',
            ready:            'Your order is packed and waiting for a delivery partner.',
            out_for_delivery: 'A delivery partner has picked up your order and is heading your way.',
            picked_up:        'Your delivery partner is almost at your doorstep.',
            delivered:        'Your order has been successfully delivered. Enjoy your meal!',
            cancelled:        'This order was cancelled.',
        };
        // For custom statuses, fall back to a generic message
        return d[status] || 'Your order status has been updated.';
    },
    statusIcon(status) {
        const icons = {
            pending:          `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
            confirmed:        `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
            preparing:        `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 0 1 10 10"/><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>`,
            ready:            `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>`,
            out_for_delivery: `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`,
            picked_up:        `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="8 12 12 16 16 12"/><line x1="12" y1="8" x2="12" y2="16"/></svg>`,
            delivered:        `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
            cancelled:        `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
        };
        return icons[status] || icons.pending;
    },
    get progressPercent() {
        if (!this.order || this.order.status === 'cancelled') return 0;
        const idx   = this.stepIndex(this.order.status);
        const total = this.customSteps.length;
        return Math.max(4, Math.round(idx / (total - 1) * 100));
    },
    async cancel() {
        if (!confirm('Cancel this order?')) return;
        this.cancelling = true;
        try {
            await window.axios.put(`/orders/${id}/cancel`);
            Alpine.store('notify').success('Order cancelled.');
            await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.cancelling = false; }
    },
    async submitRating() {
        if (this.ratingModal.foodRating === 0) {
            Alpine.store('notify').error('Please select a food rating.'); return;
        }
        this.ratingModal.submitting = true;
        try {
            const payload = { food_rating: this.ratingModal.foodRating };
            if (this.order.delivery_partner && this.ratingModal.deliveryRating > 0) {
                payload.delivery_rating = this.ratingModal.deliveryRating;
            }
            await window.axios.post(`/orders/${id}/rate`, payload);
            Alpine.store('notify').success('Thank you for your feedback!');
            this.ratingModal.open = false;
            await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.ratingModal.submitting = false; }
    },
    skipRating() {
        localStorage.setItem(`order_rating_skipped_${id}`, '1');
        this.ratingModal.open = false;
    },
}));

// ── Checkout ──
Alpine.data('checkoutPage', () => ({
    addresses: [], selectedAddress: null, useGps: false, gpsDenied: false,
    specialInstructions: '', paymentMethod: 'cash_on_delivery',
    loading: false, loadingAddresses: true, submitting: false,

    get cart() { return Alpine.store('cart'); },
    get tax() { return this.cart.subtotal * 0.10; },
    get total() { return this.cart.subtotal + parseFloat(this.cart.restaurant?.delivery_fee || 0) + this.tax; },

    async init() {
        if (!Alpine.store('auth').isLoggedIn) { window.location.href = '/login'; return; }
        if (Alpine.store('cart').isEmpty) { window.location.href = '/'; return; }
        try {
            const { data } = await window.axios.get('/auth/addresses');
            this.addresses = data;
            if (data.length) this.selectedAddress = data[0];
        } catch(e) {}
        finally { this.loadingAddresses = false; }
    },
    async submit() {
        if (!this.selectedAddress && !this.useGps) {
            Alpine.store('notify').error('Please select a delivery address.'); return;
        }
        this.submitting = true;
        const cart = Alpine.store('cart');
        // Validate all cart items are still available before submitting
        try {
            const { data } = await window.axios.get(`/restaurants/${cart.restaurant.id}`);
            const allItems = Object.values(data.menu).flat();
            const availableIds = new Set(allItems.filter(m => m.is_available).map(m => String(m.id)));
            const unavailable = cart.items.filter(ci => !availableIds.has(String(ci.id)));
            if (unavailable.length) {
                unavailable.forEach(ci => cart.remove(ci.cartKey));
                const names = unavailable.map(ci => ci.name).join(', ');
                Alpine.store('notify').error(`Some items are no longer available and were removed: ${names}. Please review your cart.`);
                this.submitting = false;
                return;
            }
        } catch(_) { /* non-fatal — server will re-validate */ }
        const items = cart.items.map(i => ({
            menu_item_id: i.id,
            quantity: i.quantity,
            ...(i.size ? { size: i.size } : {}),
            ...(i.extras && i.extras.length ? { extras: i.extras } : {}),
        }));
        const addr = this.selectedAddress || { label: 'GPS', recipient_name: Alpine.store('auth').user?.name || '', phone: '', street: '', city: '', state: '', zip_code: '' };
        try {
            const { data } = await window.axios.post('/orders', {
                restaurant_id: cart.restaurant.id,
                items, delivery_address: addr,
                payment_method: this.paymentMethod,
                special_instructions: this.specialInstructions,
            });
            cart.clear();
            Alpine.store('notify').success('Order placed!');
            window.location.href = `/orders/${data.id}`;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.submitting = false; }
    },
}));

// ── Profile ──
Alpine.data('profilePage', () => ({
    user: null, addresses: [], editing: false, saving: false,
    name: '', phone: '',
    showAddressModal: false, editingAddress: null, addrStep: 1,
    addrForm: { label:'', recipient_name:'', phone:'', street:'', apartment:'', landmark:'', delivery_instructions:'', city:'', state:'', zip_code:'', latitude:'', longitude:'' },
    map: null, marker: null, mapSearch: '', geocodeResults: [], geocoding: false, locatingGps: false, gpsBlocked: false,

    // LPU Lawgate default
    DEFAULT_LAT: 31.254405, DEFAULT_LNG: 75.695150,

    async init() {
        if (!Alpine.store('auth').isLoggedIn) { window.location.href = '/login'; return; }
        const u = Alpine.store('auth').user;
        this.user = u; this.name = u.name; this.phone = u.phone || '';
        try { const { data } = await window.axios.get('/auth/addresses'); this.addresses = data; } catch(_){}
        this.$watch('showAddressModal', v => {
            if (v) setTimeout(() => this.initMap(), 80);
            else {                if (this.map) { this.map.remove(); this.map = null; this.marker = null; }
                this.geocodeResults = []; this.mapSearch = '';
            }
        });
        this.$watch('addrStep', v => {
            if (v === 1) setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 80);
        });
    },
    async saveProfile() {
        this.saving = true;
        try {
            const { data } = await window.axios.put('/auth/profile', { name: this.name, phone: this.phone });
            Alpine.store('auth').user = data;
            localStorage.setItem('user', JSON.stringify(data));
            this.user = data; this.editing = false;
            Alpine.store('notify').success('Profile updated.');
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.saving = false; }
    },
    openAddressModal(addr = null) {
        if (addr) {
            this.editingAddress = addr;
            this.addrForm = { apartment:'', landmark:'', delivery_instructions:'', ...addr };
            this.addrStep = 2; // editing: go straight to details
        } else {
            this.editingAddress = null;
            this.addrForm = { label:'', recipient_name:'', phone:'', street:'', apartment:'', landmark:'', delivery_instructions:'', city:'', state:'', zip_code:'', latitude:'', longitude:'' };
            this.addrStep = 1;
        }
        this.showAddressModal = true;
    },
    _placeMarker(lat, lng) {
        this.addrForm.latitude = lat.toFixed(7);
        this.addrForm.longitude = lng.toFixed(7);
        if (!this.marker) {
            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', async (ev) => {
                const pos = ev.target.getLatLng();
                this.addrForm.latitude = pos.lat.toFixed(7);
                this.addrForm.longitude = pos.lng.toFixed(7);
                await this.reverseGeocode(pos.lat, pos.lng);
            });
        } else {
            this.marker.setLatLng([lat, lng]);
        }
    },
    async initMap() {
        if (typeof L === 'undefined') return;
        if (this.map) { this.map.remove(); this.map = null; this.marker = null; }
        const isNew = !this.addrForm.latitude;
        let centerLat = parseFloat(this.addrForm.latitude) || this.DEFAULT_LAT;
        let centerLng = parseFloat(this.addrForm.longitude) || this.DEFAULT_LNG;
        let grantedGps = false;
        if (isNew && navigator.geolocation && navigator.permissions) {
            try {
                const status = await navigator.permissions.query({ name: 'geolocation' });
                if (status.state === 'granted') {
                    const pos = await new Promise((res, rej) =>
                        navigator.geolocation.getCurrentPosition(res, rej, { enableHighAccuracy: true, timeout: 5000 })
                    );
                    centerLat = pos.coords.latitude;
                    centerLng = pos.coords.longitude;
                    grantedGps = true;
                    this.gpsBlocked = false;
                } else if (status.state === 'denied') {
                    this.gpsBlocked = true;
                }
            } catch (_) {}
        }
        const zoom = grantedGps ? 17 : (isNew ? 15 : 16);
        this.map = L.map('addr-map').setView([centerLat, centerLng], zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(this.map);
        // Drop pin immediately when editing or when GPS was already granted
        if (!isNew || grantedGps) {
            this._placeMarker(centerLat, centerLng);
            if (grantedGps) await this.reverseGeocode(centerLat, centerLng);
        }
        this.map.on('click', async (e) => {
            const { lat, lng } = e.latlng;
            this._placeMarker(lat, lng);
            await this.reverseGeocode(lat, lng);
        });
    },
    async reverseGeocode(lat, lng) {
        try {
            const r = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1&zoom=18`,
                { headers: { 'Accept-Language': 'en' } }
            );
            const data = await r.json();
            if (data.address) {
                const a = data.address;
                const roadParts = [a.house_number, a.road || a.pedestrian || a.footway || a.quarter].filter(Boolean);
                this.addrForm.street = roadParts.join(' ')
                    || a.suburb || a.neighbourhood || a.amenity || a.building || a.tourism || '';
                // Prefer specific city names over administrative divisions
                this.addrForm.city = a.city || a.town || a.municipality || a.city_district
                    || a.village || a.hamlet || a.county || '';
                this.addrForm.state = a.state || '';
                this.addrForm.zip_code = a.postcode || '';
            }
        } catch(_) {}
    },
    async geocodeSearch() {
        if (!this.mapSearch.trim()) return;
        this.geocoding = true;
        try {
            const r = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.mapSearch)}&addressdetails=1&limit=5`,
                { headers: { 'Accept-Language': 'en' } }
            );
            this.geocodeResults = await r.json();
        } catch(_) {}
        finally { this.geocoding = false; }
    },
    selectGeocode(result) {
        const lat = parseFloat(result.lat);
        const lng = parseFloat(result.lon);
        this.addrForm.latitude = lat.toFixed(7);
        this.addrForm.longitude = lng.toFixed(7);
        this.geocodeResults = [];
        this.mapSearch = '';
        if (this.map && this.marker) {
            this.map.setView([lat, lng], 17);
            this.marker.setLatLng([lat, lng]);
        }
        if (result.address) {
            const a = result.address;
            const roadParts = [a.house_number, a.road || a.pedestrian || a.footway || a.quarter].filter(Boolean);
            this.addrForm.street = roadParts.join(' ')
                || a.suburb || a.neighbourhood || a.amenity || a.building || a.tourism || '';
            this.addrForm.city = a.city || a.town || a.municipality || a.city_district
                || a.village || a.hamlet || a.county || '';
            this.addrForm.state = a.state || '';
            this.addrForm.zip_code = a.postcode || '';
        }
    },
    async useMyLocation() {
        if (!navigator.geolocation) { Alpine.store('notify').error('Geolocation not supported.'); return; }
        this.locatingGps = true;
        await new Promise(resolve => {
            navigator.geolocation.getCurrentPosition(async pos => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (this.map) {
                    this.map.setView([lat, lng], 17);
                    this._placeMarker(lat, lng);
                }
                await this.reverseGeocode(lat, lng);
                resolve();
            }, () => {
                Alpine.store('notify').error('Location access denied.');
                resolve();
            }, { enableHighAccuracy: true, timeout: 10000 });
        });
        this.locatingGps = false;
    },
    async saveAddress() {
        try {
            if (this.editingAddress) {
                const { data } = await window.axios.put(`/auth/addresses/${this.editingAddress.id}`, this.addrForm);
                this.addresses = this.addresses.map(a => a.id === data.id ? data : a);
            } else {
                const { data } = await window.axios.post('/auth/addresses', this.addrForm);
                this.addresses.push(data);
            }
            this.showAddressModal = false;
            Alpine.store('notify').success('Address saved.');
            Alpine.store('location').load(); // refresh navbar picker
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async deleteAddress(id) {
        if (!confirm('Delete this address?')) return;
        try {
            await window.axios.delete(`/auth/addresses/${id}`);
            this.addresses = this.addresses.filter(a => a.id !== id);
            Alpine.store('notify').success('Address deleted.');
            Alpine.store('location').load(); // refresh navbar picker
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

// ─── Restaurant Portal ─────────────────────────────────────────────────────

Alpine.data('restaurantDeliveryPartners', () => ({
    partners: [], loading: true,
    modal: { open: false, editing: false, id: null, name: '', email: '', phone: '', vehicle_type: '', password: '', is_active: true, saving: false },

    async init() {
        _requirePortalRole('restaurant_owner');
        await this.load();
    },
    async load() {
        this.loading = true;
        try {
            const { data } = await window.axios.get('/restaurant/delivery-partners');
            this.partners = data;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    openAdd() {
        this.modal = { open: true, editing: false, id: null, name: '', email: '', phone: '', vehicle_type: '', password: '', is_active: true, saving: false };
    },
    openEdit(p) {
        this.modal = { open: true, editing: true, id: p.id, name: p.name, email: p.email, phone: p.phone, vehicle_type: p.vehicle_type || '', password: '', is_active: p.is_active, saving: false };
    },
    async save() {
        this.modal.saving = true;
        try {
            const payload = { name: this.modal.name, phone: this.modal.phone, vehicle_type: this.modal.vehicle_type };
            if (this.modal.editing) {
                payload.is_active = this.modal.is_active;
                if (this.modal.password) payload.password = this.modal.password;
                await window.axios.put(`/restaurant/delivery-partners/${this.modal.id}`, payload);
                Alpine.store('notify').success('Partner updated.');
            } else {
                payload.email = this.modal.email;
                payload.password = this.modal.password;
                await window.axios.post('/restaurant/delivery-partners', payload);
                Alpine.store('notify').success('Delivery partner added.');
            }
            this.modal.open = false;
            await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.modal.saving = false; }
    },
    async remove(p) {
        if (!confirm(`Remove ${p.name}? They will lose access immediately.`)) return;
        try {
            await window.axios.delete(`/restaurant/delivery-partners/${p.id}`);
            Alpine.store('notify').success('Partner removed.');
            await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

// ── Notification permission banner ──
Alpine.data('notifBanner', () => ({
    show: false,
    init() {
        this.$nextTick(() => {
            if (!('Notification' in window)) return;
            if (Notification.permission !== 'default') return;
            if (this.$store.auth.role !== 'restaurant_owner') return;
            this.show = true;
        });
    },
    async enable() {
        const result = await Notification.requestPermission();
        if (result !== 'default') this.show = false;
    },
}));

Alpine.data('restaurantDashboard', () => ({
    stats: null, restaurant: null, recentOrders: [], loading: true, refreshTimer: null,
    advancingId: null, _seenOrderIds: null,

    async init() {
        _requirePortalRole('restaurant_owner');
        await this.load();
        this.refreshTimer = setInterval(() => this.load(), 15000);
    },
    destroy() { clearInterval(this.refreshTimer); },
    async load() {
        try {
            const { data } = await window.axios.get('/restaurant/dashboard');
            const orders = data.recent_orders || [];

            if (this._seenOrderIds !== null) {
                for (const order of orders) {
                    if (order.status === 'pending' && !this._seenOrderIds.has(order.id)) {
                        window._notifyNewOrder(order);
                    }
                }
            }
            this._seenOrderIds = new Set(orders.map(o => o.id));

            this.stats = data.stats; this.restaurant = data.restaurant; this.recentOrders = orders;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    async advance(order) {
        if (this.advancingId !== null) return;
        this.advancingId = order.id;
        try {
            await window.axios.put(`/restaurant/orders/${order.id}/advance`);
            Alpine.store('notify').success('Order advanced.');
            await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.advancingId = null; }
    },
}));

Alpine.data('restaurantOrders', () => ({
    orders: [], loading: false, status: '', page: 1, lastPage: 1, refreshTimer: null,
    selectedOrder: null, _seenOrderIds: null,
    statusFlow: [
        { key:'pending',          label:'Pending',          by_delivery: false },
        { key:'confirmed',        label:'Confirmed',        by_delivery: false },
        { key:'preparing',        label:'Preparing',        by_delivery: false },
        { key:'ready',            label:'Ready',            by_delivery: false },
        { key:'out_for_delivery', label:'Out for Delivery', by_delivery: true  },
        { key:'picked_up',        label:'At Your Door',     by_delivery: true  },
        { key:'delivered',        label:'Delivered',        by_delivery: true  },
    ],
    get allTabs() {
        const flowTabs = this.statusFlow.map(s => ({ v: s.key, l: s.label }));
        return [{ v:'', l:'All' }, ...flowTabs, { v:'cancelled', l:'Cancelled' }];
    },
    nextStatusFor(order) {
        const idx = this.statusFlow.findIndex(s => s.key === order.status);
        if (idx < 0 || idx >= this.statusFlow.length - 1) return null;
        const next = this.statusFlow[idx + 1];
        // Restaurant can only advance restaurant-controlled steps
        if (next && next.by_delivery) return null;
        return next;
    },
    isRestaurantControlled(status) {
        const step = this.statusFlow.find(s => s.key === status);
        return step ? !step.by_delivery : false;
    },
    flowLabel(status) {
        const found = this.statusFlow.find(s => s.key === status);
        return found ? found.label : window.statusLabel(status);
    },

    async init() {
        _requirePortalRole('restaurant_owner');
        await this.load();
        this.refreshTimer = setInterval(() => this.load(), 15000);
    },
    destroy() { clearInterval(this.refreshTimer); },
    async load() {
        this.loading = true;
        try {
            const params = { page: this.page };
            if (this.status) params.status = this.status;
            const { data } = await window.axios.get('/restaurant/orders', { params });
            const orders = data.data || [];

            // Fire notifications for new pending orders (only when showing all or pending tab)
            if (!this.status || this.status === 'pending') {
                if (this._seenOrderIds !== null) {
                    for (const order of orders) {
                        if (order.status === 'pending' && !this._seenOrderIds.has(order.id)) {
                            window._notifyNewOrder(order);
                        }
                    }
                }
                if (this._seenOrderIds === null) {
                    this._seenOrderIds = new Set(orders.filter(o => o.status === 'pending').map(o => o.id));
                } else {
                    for (const order of orders) {
                        if (order.status === 'pending') this._seenOrderIds.add(order.id);
                    }
                }
            }

            this.orders = orders; this.lastPage = data.last_page;
            if (data.status_flow?.length) this.statusFlow = data.status_flow;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    async advance(order) {
        const next = this.nextStatusFor(order);
        const label = next ? next.label : 'next status';
        try {
            await window.axios.put(`/restaurant/orders/${order.id}/advance`);
            Alpine.store('notify').success(`Order moved to "${label}".`); await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async cancel(order) {
        if (!confirm('Cancel this order?')) return;
        try {
            await window.axios.put(`/restaurant/orders/${order.id}/cancel`);
            Alpine.store('notify').success('Order cancelled.'); await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async viewOrder(order) {
        const { data } = await window.axios.get(`/restaurant/orders/${order.id}`);
        this.selectedOrder = data;
    },
    setStatus(s) { this.status = s; this.page = 1; this.load(); },
}));

Alpine.data('restaurantMenu', () => ({
    items: [], loading: false, showModal: false, saving: false,
    form: { name:'', description:'', price:'', category:'', spice_level:'none', preparation_time:15, is_vegetarian:true, is_vegan:false, is_gluten_free:false, is_available:true, sizes_heading:'Size', sizes:[], option_groups:[] },
    editingId: null, imageFile: null,

    async init() {
        _requirePortalRole('restaurant_owner');
        await this.load();
    },
    async load() {
        this.loading = true;
        try { const { data } = await window.axios.get('/restaurant/menu'); this.items = data; }
        catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    openCreate() {
        this.form = { name:'', description:'', price:'', category:'', spice_level:'none', preparation_time:15, is_vegetarian:true, is_vegan:false, is_gluten_free:false, is_available:true, sizes_heading:'Size', sizes:[], option_groups:[] };
        this.editingId = null; this.imageFile = null; this.showModal = true;
    },
    openEdit(item) {
        this.form = {
            name: item.name, description: item.description||'', price: item.price,
            category: item.category, spice_level: item.spice_level,
            preparation_time: item.preparation_time, is_vegetarian: item.is_vegetarian,
            is_vegan: item.is_vegan, is_gluten_free: item.is_gluten_free, is_available: item.is_available,
            sizes_heading: item.sizes_heading || 'Size',
            sizes: item.sizes ? JSON.parse(JSON.stringify(item.sizes)) : [],
            option_groups: item.option_groups ? JSON.parse(JSON.stringify(item.option_groups)) : [],
        };
        this.editingId = item.id; this.imageFile = null; this.showModal = true;
    },
    async save() {
        this.saving = true;
        try {
            const fd = new FormData();
            fd.set('name', this.form.name);
            fd.set('description', this.form.description || '');
            fd.set('price', this.form.price);
            fd.set('category', this.form.category);
            fd.set('spice_level', this.form.spice_level);
            fd.set('preparation_time', this.form.preparation_time);
            fd.set('is_vegetarian', this.form.is_vegetarian ? 1 : 0);
            fd.set('is_vegan', this.form.is_vegan ? 1 : 0);
            fd.set('is_gluten_free', this.form.is_gluten_free ? 1 : 0);
            fd.set('is_available', this.form.is_available ? 1 : 0);
            fd.set('sizes_heading', this.form.sizes_heading || 'Size');
            fd.set('sizes', JSON.stringify(this.form.sizes));
            fd.set('option_groups', JSON.stringify(this.form.option_groups));
            if (this.imageFile) fd.append('image', this.imageFile);
            if (this.editingId) {
                fd.append('_method', 'PUT');
                await window.axios.post(`/restaurant/menu/${this.editingId}`, fd, { headers: {'Content-Type':'multipart/form-data'} });
                Alpine.store('notify').success('Item updated.');
            } else {
                await window.axios.post('/restaurant/menu', fd, { headers: {'Content-Type':'multipart/form-data'} });
                Alpine.store('notify').success('Item created.');
            }
            this.showModal = false; await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.saving = false; }
    },
    // Sizes helpers
    addSize() { this.form.sizes.push({ label: '', price: '' }); },
    removeSize(i) { this.form.sizes.splice(i, 1); },
    // Option group helpers
    addGroup() { this.form.option_groups.push({ heading: '', options: [{ name: '', price: '' }] }); },
    removeGroup(gi) { this.form.option_groups.splice(gi, 1); },
    addOption(gi) { this.form.option_groups[gi].options.push({ name: '', price: '' }); },
    removeOption(gi, oi) { this.form.option_groups[gi].options.splice(oi, 1); },
    async deleteItem(item) {
        if (!confirm(`Delete "${item.name}"?`)) return;
        try { await window.axios.delete(`/restaurant/menu/${item.id}`); Alpine.store('notify').success('Deleted.'); await this.load(); }
        catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async toggleAvailable(item) {
        try {
            const fd = new FormData(); fd.append('_method','PUT'); fd.append('is_available', item.is_available ? 0 : 1);
            await window.axios.post(`/restaurant/menu/${item.id}`, fd, { headers:{'Content-Type':'multipart/form-data'} });
            item.is_available = !item.is_available;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

Alpine.data('restaurantSettings', () => ({
    restaurant: null, loading: true, saving: false, creating: false,
    form: { name:'', description:'', street:'', city:'', state:'', zip_code:'', latitude:'', longitude:'', delivery_radius:5, delivery_fee:2.99, min_order_amount:15, phone:'', email:'', estimated_delivery_time:30, cuisine_types:[] },
    cuisineInput: '', imageFile: null,
    days: ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],
    hours: { monday:{open:'09:00',close:'22:00'}, tuesday:{open:'09:00',close:'22:00'}, wednesday:{open:'09:00',close:'22:00'}, thursday:{open:'09:00',close:'22:00'}, friday:{open:'09:00',close:'23:00'}, saturday:{open:'10:00',close:'23:00'}, sunday:{open:'10:00',close:'21:00'} },
    // Status flow
    statusFlow: [
        { key:'pending',          label:'Pending',          by_delivery: false },
        { key:'confirmed',        label:'Confirmed',        by_delivery: false },
        { key:'preparing',        label:'Preparing',        by_delivery: false },
        { key:'ready',            label:'Ready',            by_delivery: false },
        { key:'out_for_delivery', label:'Out for Delivery', by_delivery: true  },
        { key:'picked_up',        label:'At Your Door',     by_delivery: true  },
        { key:'delivered',        label:'Delivered',        by_delivery: true  },
    ],
    customRestaurantLabel: '',
    customDeliveryLabel: '',
    RESTAURANT_PRESETS: [
        { key:'confirmed',     label:'Confirmed',     by_delivery: false },
        { key:'preparing',     label:'Preparing',     by_delivery: false },
        { key:'quality_check', label:'Quality Check', by_delivery: false },
        { key:'packaging',     label:'Packaging',     by_delivery: false },
        { key:'ready',         label:'Ready',         by_delivery: false },
    ],
    DELIVERY_PRESETS: [
        { key:'out_for_delivery', label:'Out for Delivery', by_delivery: true },
        { key:'picked_up',        label:'At Your Door',     by_delivery: true },
    ],
    get restaurantSteps() {
        return this.statusFlow.filter(s => !s.by_delivery);
    },
    get deliverySteps() {
        return this.statusFlow.filter(s => s.by_delivery && s.key !== 'delivered');
    },
    get availableRestaurantPresets() {
        const existing = this.statusFlow.map(s => s.key);
        return this.RESTAURANT_PRESETS.filter(p => !existing.includes(p.key));
    },
    get availableDeliveryPresets() {
        const existing = this.statusFlow.map(s => s.key);
        return this.DELIVERY_PRESETS.filter(p => !existing.includes(p.key));
    },
    // Map state
    _map: null, _marker: null, _circle: null,
    mapSearch: '', geocodeResults: [], geocoding: false, locatingGps: false,
    DEFAULT_LAT: 20.5937, DEFAULT_LNG: 78.9629, // center of India

    async init() {
        _requirePortalRole('restaurant_owner');
        try {
            const { data } = await window.axios.get('/restaurant/settings');
            this.restaurant = data;
            this.form = { name: data.name, description: data.description||'', street: data.street, city: data.city, state: data.state, zip_code: data.zip_code, latitude: data.latitude||'', longitude: data.longitude||'', delivery_radius: data.delivery_radius, delivery_fee: data.delivery_fee, min_order_amount: data.min_order_amount, phone: data.phone||'', email: data.email||'', estimated_delivery_time: data.estimated_delivery_time, cuisine_types: data.cuisine_types||[] };
            if (data.opening_hours) this.hours = data.opening_hours;
            if (data.status_flow?.length) this.statusFlow = data.status_flow;
        } catch(err) {
            if (err.response?.status === 404) this.restaurant = null;
            else Alpine.store('notify').error(apiErr(err));
        }
        finally { this.loading = false; }
        this.$nextTick(() => this.initMap());
    },

    // ── Status flow management ──
    _slugify(label) {
        return label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '').replace(/^_+|_+$/g, '') || 'custom';
    },
    addFlowPreset(preset) {
        const item = { key: preset.key, label: preset.label, by_delivery: preset.by_delivery };
        if (preset.by_delivery) {
            // Insert before delivered
            const deliveredIdx = this.statusFlow.findIndex(s => s.key === 'delivered');
            if (deliveredIdx >= 0) this.statusFlow.splice(deliveredIdx, 0, item);
            else this.statusFlow.push(item);
        } else {
            // Insert before first delivery step
            const firstDelivIdx = this.statusFlow.findIndex(s => s.by_delivery);
            if (firstDelivIdx >= 0) this.statusFlow.splice(firstDelivIdx, 0, item);
            else this.statusFlow.push(item);
        }
    },
    addRestaurantCustom() {
        const label = this.customRestaurantLabel.trim();
        if (!label) return;
        const key = this._slugify(label);
        if (this.statusFlow.find(s => s.key === key)) {
            Alpine.store('notify').error('A status with this key already exists.');
            return;
        }
        const firstDelivIdx = this.statusFlow.findIndex(s => s.by_delivery);
        const item = { key, label, by_delivery: false };
        if (firstDelivIdx >= 0) this.statusFlow.splice(firstDelivIdx, 0, item);
        else this.statusFlow.push(item);
        this.customRestaurantLabel = '';
    },
    addDeliveryCustom() {
        const label = this.customDeliveryLabel.trim();
        if (!label) return;
        const key = this._slugify(label);
        if (this.statusFlow.find(s => s.key === key)) {
            Alpine.store('notify').error('A status with this key already exists.');
            return;
        }
        const deliveredIdx = this.statusFlow.findIndex(s => s.key === 'delivered');
        const item = { key, label, by_delivery: true };
        if (deliveredIdx >= 0) this.statusFlow.splice(deliveredIdx, 0, item);
        else this.statusFlow.push(item);
        this.customDeliveryLabel = '';
    },
    removeFlowItem(key) {
        if (key === 'pending' || key === 'delivered') return;
        this.statusFlow = this.statusFlow.filter(s => s.key !== key);
    },
    moveFlowItem(key, dir) {
        if (key === 'pending' || key === 'delivered') return;
        const item = this.statusFlow.find(s => s.key === key);
        if (!item) return;
        // Only move within the same section (restaurant vs delivery)
        const section = this.statusFlow.filter(s => s.by_delivery === item.by_delivery && s.key !== 'pending' && s.key !== 'delivered');
        const secIdx = section.findIndex(s => s.key === key);
        if (dir === 'up' && secIdx <= 0) return;
        if (dir === 'down' && secIdx >= section.length - 1) return;
        const mainIdx  = this.statusFlow.findIndex(s => s.key === key);
        const swapKey  = dir === 'up' ? section[secIdx - 1].key : section[secIdx + 1].key;
        const swapIdx  = this.statusFlow.findIndex(s => s.key === swapKey);
        const arr = [...this.statusFlow];
        [arr[mainIdx], arr[swapIdx]] = [arr[swapIdx], arr[mainIdx]];
        this.statusFlow = arr;
    },

    async initMap() {
        await new Promise(r => { const t = setInterval(() => { if (window.L && document.getElementById('restaurant-map')) { clearInterval(t); r(); } }, 50); });
        const hasPin = this.form.latitude && this.form.longitude;
        const lat = hasPin ? parseFloat(this.form.latitude) : this.DEFAULT_LAT;
        const lng = hasPin ? parseFloat(this.form.longitude) : this.DEFAULT_LNG;
        const zoom = hasPin ? 16 : 5;
        this._map = L.map('restaurant-map').setView([lat, lng], zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap contributors' }).addTo(this._map);
        if (hasPin) this._placeMarker(lat, lng);
        this._map.on('click', e => { this._placeMarker(e.latlng.lat, e.latlng.lng); this._reverseGeocode(e.latlng.lat, e.latlng.lng); });
    },

    _placeMarker(lat, lng) {
        this.form.latitude = lat; this.form.longitude = lng;
        if (this._marker) { this._marker.setLatLng([lat, lng]); }
        else {
            this._marker = L.marker([lat, lng], { draggable: true }).addTo(this._map);
            this._marker.on('dragend', e => { const p = e.target.getLatLng(); this.form.latitude = p.lat; this.form.longitude = p.lng; this._reverseGeocode(p.lat, p.lng); });
        }
        this.updateRadiusCircle();
    },

    updateRadiusCircle() {
        if (!this._map || !this.form.latitude) return;
        const radiusM = parseFloat(this.form.delivery_radius) * 1000;
        if (this._circle) { this._circle.setLatLng([this.form.latitude, this.form.longitude]).setRadius(radiusM); }
        else { this._circle = L.circle([this.form.latitude, this.form.longitude], { radius: radiusM, color: '#e8621a', fillColor: '#e8621a', fillOpacity: 0.08, weight: 2 }).addTo(this._map); }
    },

    async _reverseGeocode(lat, lng) {
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&accept-language=en`, { headers: { 'Accept-Language': 'en' } });
            const d = await r.json();
            const a = d.address || {};
            const street = a.road || a.pedestrian || a.footway || a.quarter || a.suburb || a.neighbourhood || a.amenity || a.building || a.tourism || '';
            const city = a.city || a.town || a.municipality || a.city_district || a.village || a.hamlet || a.county || '';
            if (street) this.form.street = street;
            if (city) this.form.city = city;
            if (a.state) this.form.state = a.state;
            if (a.postcode) this.form.zip_code = a.postcode;
        } catch(e) { /* silent */ }
    },

    async geocodeSearch() {
        if (!this.mapSearch.trim()) return;
        this.geocoding = true; this.geocodeResults = [];
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.mapSearch)}&limit=5&accept-language=en`);
            this.geocodeResults = await r.json();
        } catch(e) { Alpine.store('notify').error('Search failed.'); }
        finally { this.geocoding = false; }
    },

    selectGeocode(r) {
        const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
        this.geocodeResults = []; this.mapSearch = '';
        this._map.setView([lat, lng], 16);
        this._placeMarker(lat, lng);
        this._reverseGeocode(lat, lng);
    },

    async useMyLocation() {
        this.locatingGps = true;
        try {
            const pos = await new Promise((res, rej) => navigator.geolocation.getCurrentPosition(res, rej, { timeout: 10000 }));
            const lat = pos.coords.latitude, lng = pos.coords.longitude;
            this._map.setView([lat, lng], 17);
            this._placeMarker(lat, lng);
            this._reverseGeocode(lat, lng);
        } catch(e) { Alpine.store('notify').error('Could not get your location.'); }
        finally { this.locatingGps = false; }
    },
    addCuisine() {
        const c = this.cuisineInput.trim();
        if (c && !this.form.cuisine_types.includes(c)) this.form.cuisine_types.push(c);
        this.cuisineInput = '';
    },
    removeCuisine(c) { this.form.cuisine_types = this.form.cuisine_types.filter(x => x !== c); },
    async save() {
        this.saving = true;
        try {
            const fd = new FormData();
            Object.entries(this.form).forEach(([k,v]) => {
                if (k === 'cuisine_types') v.forEach(c => fd.append('cuisine_types[]', c));
                else fd.append(k, v);
            });
            this.days.forEach(day => {
                fd.append(`opening_hours[${day}][open]`,  this.hours[day]?.open  ?? '');
                fd.append(`opening_hours[${day}][close]`, this.hours[day]?.close ?? '');
            });
            this.statusFlow.forEach((s, i) => {
                fd.append(`status_flow[${i}][key]`,         s.key);
                fd.append(`status_flow[${i}][label]`,       s.label);
                fd.append(`status_flow[${i}][by_delivery]`, s.by_delivery ? '1' : '0');
            });
            if (this.imageFile) fd.append('image', this.imageFile);
            if (this.restaurant) {
                fd.append('_method', 'PUT');
                const { data } = await window.axios.post('/restaurant/settings', fd, { headers:{'Content-Type':'multipart/form-data'} });
                this.restaurant = data;
                if (data.status_flow?.length) this.statusFlow = data.status_flow;
            } else {
                const { data } = await window.axios.post('/restaurant', fd, { headers:{'Content-Type':'multipart/form-data'} });
                this.restaurant = data;
            }
            Alpine.store('notify').success('Settings saved.');
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.saving = false; }
    },
}));

// ─── Delivery Portal ───────────────────────────────────────────────────────

Alpine.data('deliveryDashboard', () => ({
    stats: null, availableOrders: [], activeDeliveries: [], loading: true, refreshTimer: null,
    acceptingId: null,

    async init() {
        _requirePortalRole('delivery');
        await this.load();
        this.refreshTimer = setInterval(() => this.load(), 15000);
    },
    destroy() { clearInterval(this.refreshTimer); },
    async load() {
        try {
            const { data } = await window.axios.get('/delivery/dashboard');
            this.stats = data.stats; this.availableOrders = data.available_orders; this.activeDeliveries = data.active_deliveries;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    async toggleAvailability() {
        try {
            const { data } = await window.axios.put('/delivery/availability');
            if (this.stats) this.stats.is_available = data.is_available;
            Alpine.store('notify').info(data.message);
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async accept(order) {
        if (!this.stats?.is_available) {
            Alpine.store('notify').error('You must go Online before accepting orders.');
            return;
        }
        this.acceptingId = order.id;
        try {
            await window.axios.post(`/delivery/orders/${order.id}/accept`);
            Alpine.store('notify').success('Order accepted!'); await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.acceptingId = null; }
    },
    async advance(order) {
        try {
            await window.axios.put(`/delivery/orders/${order.id}/status`);
            Alpine.store('notify').success('Status updated.'); await this.load();
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

Alpine.data('deliveryHistory', () => ({
    orders: [], loading: false, page: 1, lastPage: 1,

    async init() {
        _requirePortalRole('delivery');
        await this.load();
    },
    async load() {
        this.loading = true;
        try {
            const { data } = await window.axios.get('/delivery/history', { params: { page: this.page } });
            this.orders = data.data; this.lastPage = data.last_page;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
}));

// ─── Admin Portal ──────────────────────────────────────────────────────────

Alpine.data('adminDashboard', () => ({
    stats: null, pendingApprovals: 0, recentOrders: [], loading: true,

    async init() {
        _requirePortalRole('admin');
        try {
            const { data } = await window.axios.get('/admin/dashboard');
            this.stats = data.stats; this.pendingApprovals = data.pending_approvals; this.recentOrders = data.recent_orders;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
}));

Alpine.data('adminUsers', () => ({
    users: [], loading: false, search: '', role: '', page: 1, lastPage: 1,
    showModal: false, saving: false, editingUser: null,
    form: { name:'', email:'', password:'', phone:'', role:'customer', is_active: true },

    async init() {
        _requirePortalRole('admin'); await this.load();
    },
    async load() {
        this.loading = true;
        try {
            const params = { page: this.page };
            if (this.search) params.search = this.search;
            if (this.role) params.role = this.role;
            const { data } = await window.axios.get('/admin/users', { params });
            this.users = data.data; this.lastPage = data.last_page;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    openCreate() {
        this.editingUser = null;
        this.form = { name:'', email:'', password:'', phone:'', role:'customer', is_active: true };
        this.showModal = true;
    },
    openEdit(user) {
        this.editingUser = user;
        this.form = { name: user.name, email: user.email, password:'', phone: user.phone||'', role: user.role, is_active: user.is_active };
        this.showModal = true;
    },
    async save() {
        this.saving = true;
        try {
            const payload = { ...this.form };
            if (!payload.password) delete payload.password;
            if (this.editingUser) {
                const { data } = await window.axios.put(`/admin/users/${this.editingUser.id}`, payload);
                this.users = this.users.map(u => u.id === data.id ? data : u);
            } else {
                const { data } = await window.axios.post('/admin/users', payload);
                this.users.unshift(data);
            }
            this.showModal = false; Alpine.store('notify').success('Saved.');
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.saving = false; }
    },
    async deleteUser(user) {
        if (!confirm(`Delete user ${user.name}?`)) return;
        try { await window.axios.delete(`/admin/users/${user.id}`); this.users = this.users.filter(u => u.id !== user.id); Alpine.store('notify').success('Deleted.'); }
        catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

Alpine.data('adminRestaurants', () => ({
    restaurants: [], loading: false, approved: '', page: 1, lastPage: 1,

    async init() { _requirePortalRole('admin'); await this.load(); },
    async load() {
        this.loading = true;
        try {
            const params = { page: this.page };
            if (this.approved !== '') params.approved = this.approved;
            const { data } = await window.axios.get('/admin/restaurants', { params });
            this.restaurants = data.data; this.lastPage = data.last_page;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    async approve(r) {
        try {
            await window.axios.put(`/admin/restaurants/${r.id}/approve`);
            r.is_approved = true; Alpine.store('notify').success('Restaurant approved.');
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async toggle(r) {
        try {
            const { data } = await window.axios.put(`/admin/restaurants/${r.id}/toggle`);
            r.is_active = data.is_active; Alpine.store('notify').info(data.message);
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async deleteRest(r) {
        if (!confirm(`Delete "${r.name}"? This cannot be undone.`)) return;
        try { await window.axios.delete(`/admin/restaurants/${r.id}`); this.restaurants = this.restaurants.filter(x => x.id !== r.id); Alpine.store('notify').success('Deleted.'); }
        catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

Alpine.data('adminOrders', () => ({
    orders: [], loading: false, status: '', page: 1, lastPage: 1,

    async init() { _requirePortalRole('admin'); await this.load(); },
    async load() {
        this.loading = true;
        try {
            const params = { page: this.page };
            if (this.status) params.status = this.status;
            const { data } = await window.axios.get('/admin/orders', { params });
            this.orders = data.data; this.lastPage = data.last_page;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    async cancel(order) {
        if (!confirm('Cancel this order?')) return;
        try { await window.axios.put(`/admin/orders/${order.id}/cancel`); Alpine.store('notify').success('Cancelled.'); await this.load(); }
        catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

Alpine.data('adminDeliveryPartners', () => ({
    partners: [], loading: false, verified: '', page: 1, lastPage: 1,

    async init() { _requirePortalRole('admin'); await this.load(); },
    async load() {
        this.loading = true;
        try {
            const params = { page: this.page };
            if (this.verified !== '') params.verified = this.verified;
            const { data } = await window.axios.get('/admin/delivery-partners', { params });
            this.partners = data.data; this.lastPage = data.last_page;
        } catch(e) { Alpine.store('notify').error(apiErr(e)); }
        finally { this.loading = false; }
    },
    async verify(p) {
        try { await window.axios.put(`/admin/delivery-partners/${p.id}/verify`); p.is_verified = true; Alpine.store('notify').success('Partner verified.'); }
        catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
    async toggleAvail(p) {
        try { const { data } = await window.axios.put(`/admin/delivery-partners/${p.id}/toggle-availability`); p.is_available = data.is_available; Alpine.store('notify').info(data.message); }
        catch(e) { Alpine.store('notify').error(apiErr(e)); }
    },
}));

// ─── Helpers ───────────────────────────────────────────────────────────────
function _requirePortalRole(role) {
    const auth = Alpine.store('auth');
    if (!auth.isLoggedIn) { window.location.href = '/login'; return; }
    if (role && auth.role !== role) { window.location.href = auth.portalHome(); }
}

window.Alpine = Alpine;
Alpine.start();
