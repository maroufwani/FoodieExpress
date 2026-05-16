@extends('layouts.portal')
@section('title', 'Users')
@section('page-title', 'Users')
@section('admin-nav-users', 'active')

@section('sidebar-nav')
    @include('admin._sidebar')
@endsection

@section('page-actions')
    <button x-data @click="$dispatch('open-user-modal')" class="btn-portal">+ Create User</button>
@endsection

@section('content')
<div x-data="adminUsers" @open-user-modal.window="openCreate()">

    <!-- Filters -->
    <div class="flex gap-3 mb-6 flex-wrap">
        <input type="text" x-model="search" @input.debounce.400ms="page=1; load()"
               placeholder="Search name or email…" class="portal-input" style="width:240px">
        <select x-model="role" @change="page=1; load()" class="portal-input" style="width:180px">
            <option value="">All roles</option>
            <option value="customer">Customer</option>
            <option value="restaurant_owner">Restaurant Owner</option>
            <option value="delivery">Delivery</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <!-- Table -->
    <div class="portal-card overflow-x-auto">
        <div x-show="loading" class="p-6">
            <div class="flex flex-col gap-3">
                <template x-for="n in 5"><div class="skeleton h-12 rounded-lg"></div></template>
            </div>
        </div>

        <table x-show="!loading" class="w-full text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--color-portal-border)">
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Name</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Email</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Role</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Status</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Joined</th>
                    <th class="text-left p-4" style="color:var(--color-portal-muted);font-weight:600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="users.length === 0">
                    <tr><td colspan="6" class="p-6 text-center" style="color:var(--color-portal-muted)">No users found.</td></tr>
                </template>
                <template x-for="user in users" :key="user.id">
                    <tr style="border-bottom:1px solid var(--color-portal-border)" class="hover:opacity-80 transition-opacity">
                        <td class="p-4 font-medium" style="color:var(--color-portal-text)">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                     style="background:var(--color-brand)"
                                     x-text="(user.name||'U')[0].toUpperCase()"></div>
                                <span x-text="user.name"></span>
                            </div>
                        </td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="user.email"></td>
                        <td class="p-4">
                            <span class="badge badge-pending text-xs capitalize" x-text="user.role.replace('_',' ')"></span>
                        </td>
                        <td class="p-4">
                            <span :class="user.is_active ? 'badge-delivered' : 'badge-cancelled'" class="badge"
                                  x-text="user.is_active ? 'Active' : 'Inactive'"></span>
                        </td>
                        <td class="p-4" style="color:var(--color-portal-muted)" x-text="fmtDate(user.created_at)"></td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <button @click="openEdit(user)" class="btn-portal-ghost text-xs px-3 py-1.5">Edit</button>
                                <button @click="deleteUser(user)" class="btn-portal-ghost text-xs px-3 py-1.5" style="color:var(--color-error)">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div x-show="lastPage > 1" class="flex justify-center gap-2 mt-6">
        <button @click="page--; load()" :disabled="page<=1" class="btn-portal-ghost px-4 py-2">← Prev</button>
        <span class="px-4 py-2 text-sm" style="color:var(--color-portal-muted)">
            Page <span x-text="page"></span> / <span x-text="lastPage"></span>
        </span>
        <button @click="page++; load()" :disabled="page>=lastPage" class="btn-portal-ghost px-4 py-2">Next →</button>
    </div>

    <!-- User modal -->
    <div x-show="showModal" x-cloak class="modal-backdrop" style="z-index:150">
        <div class="portal-modal-box">
            <div class="flex items-center justify-between mb-5">
                <h3 style="font-family:var(--font-display);font-weight:700;color:var(--color-portal-text)"
                    x-text="editingUser ? 'Edit User' : 'New User'"></h3>
                <button @click="showModal=false" class="btn-portal-ghost px-3">&times;</button>
            </div>
            <form @submit.prevent="save()" class="flex flex-col gap-4">
                <div>
                    <label class="label">Full Name *</label>
                    <input type="text" x-model="form.name" required class="portal-input">
                </div>
                <div>
                    <label class="label">Email *</label>
                    <input type="email" x-model="form.email" required class="portal-input">
                </div>
                <div>
                    <label class="label">Password <span x-show="editingUser" style="color:var(--color-portal-muted)">(leave blank to keep)</span></label>
                    <input type="password" x-model="form.password" :required="!editingUser" class="portal-input" autocomplete="new-password">
                </div>
                <div>
                    <label class="label">Phone</label>
                    <input type="tel" x-model="form.phone" class="portal-input">
                </div>
                <div>
                    <label class="label">Role *</label>
                    <select x-model="form.role" required class="portal-input">
                        <option value="customer">Customer</option>
                        <option value="restaurant_owner">Restaurant Owner</option>
                        <option value="delivery">Delivery</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm" style="color:var(--color-portal-text)">
                    <input type="checkbox" x-model="form.is_active"> Active
                </label>
                <div class="flex gap-3 justify-end mt-2">
                    <button type="button" @click="showModal=false" class="btn-portal-ghost">Cancel</button>
                    <button type="submit" :disabled="saving" class="btn-portal flex items-center gap-2">
                        <span x-show="saving" class="spinner" style="width:14px;height:14px"></span>
                        <span x-text="saving ? 'Saving…' : 'Save User'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
