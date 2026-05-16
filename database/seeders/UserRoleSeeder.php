<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── Additional Admins ────────────────────────────────
            [
                'name'      => 'Sarah Admin',
                'email'     => 'sarah@foodieexpress.com',
                'password'  => 'password',
                'phone'     => '+1000000001',
                'role'      => 'admin',
                'is_active' => true,
            ],

            // ── Restaurant Owners ────────────────────────────────
            [
                'name'      => 'Lin Wei',
                'email'     => 'linwei@foodieexpress.com',
                'password'  => 'password',
                'phone'     => '+1111111112',
                'role'      => 'restaurant_owner',
                'is_active' => true,
            ],
            [
                'name'      => 'Priya Sharma',
                'email'     => 'priya@foodieexpress.com',
                'password'  => 'password',
                'phone'     => '+1111111113',
                'role'      => 'restaurant_owner',
                'is_active' => true,
            ],
            [
                'name'      => 'Carlos Mendez',
                'email'     => 'carlos@foodieexpress.com',
                'password'  => 'password',
                'phone'     => '+1111111114',
                'role'      => 'restaurant_owner',
                'is_active' => false, // inactive owner for testing
            ],

            // ── Customers ────────────────────────────────────────
            [
                'name'      => 'Alice Johnson',
                'email'     => 'alice@example.com',
                'password'  => 'password',
                'phone'     => '+1222222223',
                'role'      => 'customer',
                'is_active' => true,
            ],
            [
                'name'      => 'Bob Williams',
                'email'     => 'bob@example.com',
                'password'  => 'password',
                'phone'     => '+1222222224',
                'role'      => 'customer',
                'is_active' => true,
            ],
            [
                'name'      => 'Carol Martinez',
                'email'     => 'carol@example.com',
                'password'  => 'password',
                'phone'     => '+1222222225',
                'role'      => 'customer',
                'is_active' => true,
            ],
            [
                'name'      => 'David Kim',
                'email'     => 'david@example.com',
                'password'  => 'password',
                'phone'     => '+1222222226',
                'role'      => 'customer',
                'is_active' => false, // inactive customer for testing
            ],

            // ── Delivery Partners ─────────────────────────────────
            [
                'name'         => 'Sara Cyclist',
                'email'        => 'sara.delivery@foodieexpress.com',
                'password'     => 'password',
                'phone'        => '+1333333334',
                'role'         => 'delivery',
                'is_active'    => true,
                'is_verified'  => true,
                'is_available' => false, // verified but offline
                'vehicle_type' => 'bicycle',
                'rating'       => 4.80,
            ],
            [
                'name'         => 'Tom Trucker',
                'email'        => 'tom.delivery@foodieexpress.com',
                'password'     => 'password',
                'phone'        => '+1333333335',
                'role'         => 'delivery',
                'is_active'    => true,
                'is_verified'  => false, // unverified — pending approval
                'is_available' => false,
                'vehicle_type' => 'car',
                'rating'       => 0.00,
            ],
            [
                'name'         => 'Nina Scooter',
                'email'        => 'nina.delivery@foodieexpress.com',
                'password'     => 'password',
                'phone'        => '+1333333336',
                'role'         => 'delivery',
                'is_active'    => true,
                'is_verified'  => true,
                'is_available' => true, // verified and online
                'vehicle_type' => 'scooter',
                'rating'       => 4.60,
            ],
        ];

        foreach ($users as $data) {
            // Skip if email already exists
            if (User::where('email', $data['email'])->exists()) {
                continue;
            }
            User::create($data);
        }

        $this->command->info('UserRoleSeeder: seeded ' . count($users) . ' additional test users.');
    }
}
