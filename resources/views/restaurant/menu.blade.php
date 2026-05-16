@extends('layouts.portal')
@section('title', 'Menu')
@section('page-title', 'Menu')

@section('page-actions')
    <button x-data @click="$dispatch('open-menu-modal')" class="btn-portal">+ Add Item</button>
@endsection

@section('sidebar-nav')
    <a href="/restaurant/dashboard" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>
    <a href="/restaurant/orders" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Orders
    </a>
    <a href="/restaurant/menu" class="portal-nav-link active">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
        Menu
    </a>
    <a href="/restaurant/delivery-partners" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        Delivery Partners
    </a>
    <a href="/restaurant/settings" class="portal-nav-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Settings
    </a>
@endsection

@section('content')
<div x-data="restaurantMenu" @open-menu-modal.window="openCreate()">

    <!-- Skeleton -->
    <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <template x-for="n in 6"><div class="skeleton h-56 rounded-2xl"></div></template>
    </div>

    <!-- Grid -->
    <div x-show="!loading && items.length > 0"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <template x-for="item in items" :key="item.id">
            <div class="portal-card p-0 flex flex-col transition-opacity duration-200"
                 :class="!item.is_available ? 'opacity-50' : ''">

                <!-- Image -->
                <template x-if="item.image_path">
                    <div class="relative">
                        <img :src="item.image_path" :alt="item.name"
                             class="w-full h-44 object-cover">
                        <template x-if="!item.is_available">
                            <div class="absolute inset-0 flex items-center justify-center"
                                 style="background:rgba(0,0,0,0.45)">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                      style="background:var(--color-portal-border);color:var(--color-portal-muted)">Unavailable</span>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Body -->
                <div class="flex flex-col gap-3 p-4 flex-1">

                    <!-- Name + price row -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-base leading-snug"
                               style="color:var(--color-portal-text)" x-text="item.name"></p>
                            <span class="inline-block text-xs mt-1 px-2 py-0.5 rounded-full font-medium"
                                  style="background:rgba(232,98,26,0.12);color:var(--color-brand)"
                                  x-text="item.category"></span>
                        </div>
                        <p class="text-xl font-bold shrink-0"
                           style="color:var(--color-brand);font-family:var(--font-display)"
                           x-text="fmtCurrency(item.price)"></p>
                    </div>

                    <!-- Description -->
                    <p x-show="item.description"
                       class="text-xs leading-relaxed line-clamp-2"
                       style="color:var(--color-portal-muted)"
                       x-text="item.description"></p>

                    <!-- Meta row: dietary badges + prep time -->
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex gap-1.5 flex-wrap">
                            <template x-if="item.is_vegetarian">
                                <span title="Vegetarian" style="display:inline-flex;width:18px;height:18px;border:2px solid #22a722;border-radius:3px;align-items:center;justify-content:center;flex-shrink:0">
                                    <svg width="9" height="9" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="#22a722"/></svg>
                                </span>
                            </template>
                            <template x-if="!item.is_vegetarian">
                                <span title="Non-Vegetarian" style="display:inline-flex;width:18px;height:18px;border:2px solid #c8232c;border-radius:3px;align-items:center;justify-content:center;flex-shrink:0">
                                    <svg width="9" height="9" viewBox="0 0 8 8"><polygon points="4,0.5 7.5,7.5 0.5,7.5" fill="#c8232c"/></svg>
                                </span>
                            </template>
                            <template x-if="item.spice_level && item.spice_level !== 'none'">
                                <span class="text-xs px-2 py-0.5 rounded-full"
                                      style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.2)"
                                      x-text="item.spice_level === 'extra_hot' ? '🌶🌶 Extra Hot' : item.spice_level === 'hot' ? '🌶 Hot' : item.spice_level === 'medium' ? '🌶 Medium' : '🌶 Mild'"></span>
                            </template>
                        </div>
                        <span x-show="item.preparation_time" class="text-xs shrink-0"
                              style="color:var(--color-portal-muted)">
                            ⏱ <span x-text="item.preparation_time"></span> min
                        </span>
                    </div>

                    <!-- Spacer -->
                    <div class="flex-1"></div>

                    <!-- Action footer -->
                    <div class="flex gap-2 pt-3 mt-1" style="border-top:1px solid var(--color-portal-border)">
                        <button @click="openEdit(item)"
                                class="btn-portal-ghost text-xs flex-1 py-2">
                            ✏️ Edit
                        </button>
                        <button @click="toggleAvailable(item)"
                                class="btn-portal-ghost text-xs flex-1 py-2"
                                x-text="item.is_available ? '⏸ Disable' : '▶ Enable'"></button>
                        <button @click="deleteItem(item)"
                                class="btn-portal-ghost text-xs px-3 py-2"
                                style="color:var(--color-error);border-color:rgba(239,68,68,0.2)"
                                title="Delete">
                            🗑
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <template x-if="!loading && items.length === 0">
        <div class="text-center py-20">
            <span style="font-size:3rem">🍽️</span>
            <p class="mt-4" style="color:var(--color-portal-muted)">No menu items yet.</p>
            <button @click="openCreate()" class="btn-portal mt-4">Add first item</button>
        </div>
    </template>

    <!-- Item Modal -->
    <div x-show="showModal" x-cloak class="modal-backdrop" style="z-index:150">
        <div class="portal-modal-box" style="max-width:680px;padding:0;display:flex;flex-direction:column;max-height:92vh">

            <!-- Sticky header -->
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--color-portal-border);flex-shrink:0;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h3 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text);font-size:1.05rem" x-text="editingId ? 'Edit Item' : 'New Menu Item'"></h3>
                    <p style="font-size:0.75rem;color:var(--color-portal-muted);margin-top:2px">Define basic details, size variants, and add-on groups.</p>
                </div>
                <button @click="showModal=false" class="btn-portal-ghost" style="padding:0.3rem 0.6rem;font-size:1.1rem;line-height:1">&times;</button>
            </div>

            <!-- Form — wraps scrollable body + footer so submit works -->
            <form @submit.prevent="save()" style="display:flex;flex-direction:column;flex:1;overflow:hidden">

                <!-- Scrollable body -->
                <div style="overflow-y:auto;flex:1;padding:1.5rem;display:flex;flex-direction:column;gap:1.75rem">

                    <!-- ── Basic Details ─────────────────────────────── -->
                    <div>
                        <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1rem">
                            <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.09em;text-transform:uppercase;color:var(--color-portal-muted);white-space:nowrap">Basic Details</span>
                            <div style="flex:1;height:1px;background:var(--color-portal-border)"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.875rem">
                            <div style="grid-column:1/-1">
                                <label class="label">Name *</label>
                                <input type="text" x-model="form.name" required class="portal-input" placeholder="e.g. Margherita Pizza">
                            </div>
                            <div style="grid-column:1/-1">
                                <label class="label">Description</label>
                                <textarea x-model="form.description" rows="2" class="portal-input resize-none" placeholder="Brief description shown to customers…"></textarea>
                            </div>
                            <div>
                                <label class="label">Category *</label>
                                <input type="text" x-model="form.category" required class="portal-input" placeholder="Mains, Desserts, Sides…">
                            </div>
                            <div>
                                <label class="label">Base Price *</label>
                                <div style="position:relative">
                                    <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);font-size:0.85rem;color:var(--color-portal-muted);pointer-events:none">₹</span>
                                    <input type="number" step="0.01" min="0" x-model="form.price" required class="portal-input" style="padding-left:1.75rem" placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label class="label">Spice Level</label>
                                <select x-model="form.spice_level" class="portal-input">
                                    <option value="none">None</option>
                                    <option value="mild">🌶 Mild</option>
                                    <option value="medium">🌶 Medium</option>
                                    <option value="hot">🌶🌶 Hot</option>
                                    <option value="extra_hot">🌶🌶 Extra Hot</option>
                                </select>
                            </div>
                            <div>
                                <label class="label">Prep time (min)</label>
                                <input type="number" min="1" x-model="form.preparation_time" class="portal-input">
                            </div>
                            <div style="grid-column:1/-1">
                                <label class="label">Image</label>
                                <input type="file" accept="image/*" @change="imageFile = $event.target.files[0]" class="portal-input" style="cursor:pointer">
                            </div>
                        </div>
                    </div>

                    <!-- ── Dietary & Availability ────────────────────── -->
                    <div>
                        <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:1rem">
                            <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.09em;text-transform:uppercase;color:var(--color-portal-muted);white-space:nowrap">Dietary & Availability</span>
                            <div style="flex:1;height:1px;background:var(--color-portal-border)"></div>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:1.25rem">
                            <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer;color:var(--color-portal-text)">
                                <input type="checkbox" :checked="!form.is_vegetarian" @change="form.is_vegetarian = !$event.target.checked" style="accent-color:#c8232c;width:15px;height:15px"> Non-Vegetarian
                            </label>
                            <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer;color:var(--color-portal-text)">
                                <input type="checkbox" x-model="form.is_available" style="accent-color:var(--color-brand);width:15px;height:15px"> Available
                            </label>
                        </div>
                    </div>

                    <!-- ── Sizes ─────────────────────────────────────── -->
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:1rem">
                            <div style="display:flex;align-items:center;gap:0.6rem;flex:1;min-width:0">
                                <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.09em;text-transform:uppercase;color:var(--color-portal-muted);white-space:nowrap">Sizes</span>
                                <div style="flex:1;height:1px;background:var(--color-portal-border)"></div>
                            </div>
                            <button type="button" @click="addSize()" class="btn-portal-ghost" style="font-size:0.75rem;padding:0.3rem 0.75rem;white-space:nowrap;flex-shrink:0">
                                + Add Size
                            </button>
                        </div>

                        <template x-if="form.sizes.length > 0">
                            <div style="margin-bottom:0.75rem">
                                <label class="label">Section Heading</label>
                                <input type="text" x-model="form.sizes_heading" class="portal-input" placeholder="Size, Crust Type, Portion…" style="max-width:240px">
                            </div>
                        </template>

                        <div style="display:flex;flex-direction:column;gap:0.5rem">
                            <template x-for="(size, i) in form.sizes" :key="i">
                                <div style="display:flex;align-items:center;gap:0.5rem">
                                    <input type="text" x-model="size.label" class="portal-input" placeholder="e.g. Small" style="flex:1">
                                    <div style="position:relative;flex:1">
                                        <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);font-size:0.85rem;color:var(--color-portal-muted);pointer-events:none">₹</span>
                                        <input type="number" step="0.01" min="0" x-model="size.price" class="portal-input" style="padding-left:1.75rem" placeholder="0.00">
                                    </div>
                                    <button type="button" @click="removeSize(i)" class="btn-portal-ghost" style="padding:0.45rem 0.6rem;color:var(--color-error);border-color:rgba(239,68,68,0.25);flex-shrink:0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <template x-if="form.sizes.length === 0">
                            <p style="font-size:0.8rem;color:var(--color-portal-muted);text-align:center;padding:0.875rem;border:1px dashed var(--color-portal-border);border-radius:0.625rem">
                                No sizes defined — customers order at the base price.
                            </p>
                        </template>
                    </div>

                    <!-- ── Add-on Groups ──────────────────────────────── -->
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:1rem">
                            <div style="display:flex;align-items:center;gap:0.6rem;flex:1;min-width:0">
                                <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.09em;text-transform:uppercase;color:var(--color-portal-muted);white-space:nowrap">Add-on Groups</span>
                                <div style="flex:1;height:1px;background:var(--color-portal-border)"></div>
                            </div>
                            <button type="button" @click="addGroup()" class="btn-portal-ghost" style="font-size:0.75rem;padding:0.3rem 0.75rem;white-space:nowrap;flex-shrink:0">
                                + Add Group
                            </button>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:0.875rem">
                            <template x-for="(group, gi) in form.option_groups" :key="gi">
                                <div style="border:1px solid var(--color-portal-border);border-radius:0.75rem;padding:1rem;background:var(--color-portal-surface-2)">
                                    <!-- Group heading row -->
                                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.875rem">
                                        <input type="text" x-model="group.heading" class="portal-input" placeholder="Group name — e.g. Toppings, Sauce, Extras…" style="flex:1;font-weight:600">
                                        <button type="button" @click="removeGroup(gi)" class="btn-portal-ghost" style="padding:0.45rem 0.6rem;color:var(--color-error);border-color:rgba(239,68,68,0.25);flex-shrink:0" title="Remove group">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    </div>
                                    <!-- Options -->
                                    <div style="display:flex;flex-direction:column;gap:0.45rem;margin-bottom:0.75rem">
                                        <template x-for="(opt, oi) in group.options" :key="oi">
                                            <div style="display:flex;align-items:center;gap:0.5rem">
                                                <input type="text" x-model="opt.name" class="portal-input" placeholder="Option name" style="flex:1">
                                                <div style="position:relative;width:130px;flex-shrink:0">
                                                    <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);font-size:0.85rem;color:var(--color-portal-muted);pointer-events:none">₹</span>
                                                    <input type="number" step="0.01" min="0" x-model="opt.price" class="portal-input" style="padding-left:1.75rem" placeholder="0.00">
                                                </div>
                                                <button type="button" @click="removeOption(gi, oi)" class="btn-portal-ghost" style="padding:0.45rem 0.6rem;flex-shrink:0" title="Remove option">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="addOption(gi)" class="btn-portal-ghost" style="font-size:0.75rem;padding:0.3rem 0.7rem">
                                        + Add Option
                                    </button>
                                </div>
                            </template>
                        </div>

                        <template x-if="form.option_groups.length === 0">
                            <p style="font-size:0.8rem;color:var(--color-portal-muted);text-align:center;padding:0.875rem;border:1px dashed var(--color-portal-border);border-radius:0.625rem">
                                No add-on groups — add groups like "Toppings" or "Extras" with individual options.
                            </p>
                        </template>
                    </div>

                </div><!-- /scrollable body -->

                <!-- Sticky footer -->
                <div style="padding:1rem 1.5rem;border-top:1px solid var(--color-portal-border);flex-shrink:0;display:flex;justify-content:flex-end;gap:0.75rem;background:var(--color-portal-surface)">
                    <button type="button" @click="showModal=false" class="btn-portal-ghost">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-portal" style="min-width:120px">
                        <span x-show="saving" class="spinner" style="width:14px;height:14px"></span>
                        <span x-text="saving ? 'Saving…' : (editingId ? 'Update Item' : 'Add Item')"></span>
                    </button>
                </div>

            </form><!-- /form -->
        </div>
    </div>
</div>
@endsection
