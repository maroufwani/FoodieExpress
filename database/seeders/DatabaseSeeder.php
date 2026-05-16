<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'      => 'Platform Admin',
            'email'     => 'admin@foodieexpress.com',
            'password'  => 'password',
            'phone'     => '+1000000000',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // Restaurant owner
        $owner = User::create([
            'name'      => 'Pizza World Owner',
            'email'     => 'owner@foodieexpress.com',
            'password'  => 'password',
            'phone'     => '+1111111111',
            'role'      => 'restaurant_owner',
            'is_active' => true,
        ]);

        // Customer
        User::create([
            'name'      => 'Jane Doe',
            'email'     => 'customer@foodieexpress.com',
            'password'  => 'password',
            'phone'     => '+1222222222',
            'role'      => 'customer',
            'is_active' => true,
        ]);

        // Delivery partner
        User::create([
            'name'         => 'Mike Driver',
            'email'        => 'delivery@foodieexpress.com',
            'password'     => 'password',
            'phone'        => '+1333333333',
            'role'         => 'delivery',
            'is_active'    => true,
            'is_verified'  => true,
            'is_available' => true,
            'vehicle_type' => 'motorcycle',
        ]);

        // Restaurant
        $restaurant = Restaurant::create([
            'owner_id'                => $owner->id,
            'name'                    => "Pizza's World",
            'description'             => 'The best pizzas in town – from classic veg to premium non-veg specialties, freshly made to order.',
            'cuisine_types'           => ['Pizza', 'Fast Food', 'Italian'],
            'street'                  => '123 Main St',
            'city'                    => 'New York',
            'state'                   => 'NY',
            'zip_code'                => '10001',
            'latitude'                => 40.7128,
            'longitude'               => -74.0060,
            'delivery_radius'         => 10.0,
            'delivery_fee'            => 2.99,
            'min_order_amount'        => 15.00,
            'phone'                   => '+1444444444',
            'email'                   => 'info@pizzasworld.com',
            'estimated_delivery_time' => 30,
            'rating'                  => 4.50,
            'is_active'               => true,
            'is_approved'             => true,
            'opening_hours'           => [
                'monday'    => ['open' => '09:00', 'close' => '22:00'],
                'tuesday'   => ['open' => '09:00', 'close' => '22:00'],
                'wednesday' => ['open' => '09:00', 'close' => '22:00'],
                'thursday'  => ['open' => '09:00', 'close' => '22:00'],
                'friday'    => ['open' => '09:00', 'close' => '23:00'],
                'saturday'  => ['open' => '10:00', 'close' => '23:00'],
                'sunday'    => ['open' => '10:00', 'close' => '21:00'],
            ],
        ]);

        // Menu items – each entry has sizes [Small, Medium, Large] => price
        $menuItems = [

            // ── Veg Pizza ──────────────────────────────────────────────────
            ['name' => 'Margherita Pizza',         'description' => 'Classic tomato base with mozzarella.',                                           'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [70,  140, 200]],
            ['name' => 'Capsicum Pizza',            'description' => 'Fresh capsicum on a tangy tomato base.',                                         'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [80,  150, 210]],
            ['name' => 'Tomato Pizza',              'description' => 'Loaded with juicy tomatoes and herbs.',                                          'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [80,  150, 210]],
            ['name' => 'Onion Pizza',               'description' => 'Caramelised onions on a classic tomato base.',                                   'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [80,  150, 210]],
            ['name' => 'Corn Pizza',                'description' => 'Sweet corn kernels on a creamy base.',                                           'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [90,  170, 230]],
            ['name' => 'Onion Capsicum Pizza',      'description' => 'Onion and capsicum on a tangy tomato sauce.',                                    'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [100, 180, 250]],
            ['name' => 'Corn Onion Pizza',          'description' => 'Sweet corn and caramelised onions.',                                             'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [100, 180, 250]],
            ['name' => 'Mushroom Tomato Pizza',     'description' => 'Sliced mushrooms with fresh tomatoes.',                                          'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [100, 180, 250]],
            ['name' => 'Paneer Mushroom Pizza',     'description' => 'Paneer cubes with sliced mushrooms.',                                            'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'mild',   'sizes' => [110, 190, 270]],
            ['name' => 'Corn Paneer Mushroom Pizza','description' => 'Sweet corn, paneer and mushrooms.',                                              'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [110, 190, 270]],
            ['name' => 'Corn Fresh Pizza',          'description' => 'Corn, capsicum and tomato.',                                                     'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [120, 200, 280]],
            ['name' => 'Golden Veg Pizza',          'description' => 'Corn, capsicum and red paprika.',                                                'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [120, 200, 280]],
            ['name' => 'Vegetarian Pizza',          'description' => 'Onion, capsicum and tomato.',                                                    'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [120, 200, 280]],
            ['name' => 'Hot Chilli Pizza',          'description' => 'Green chilli, red paprika, capsicum and onion.',                                 'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'hot',    'sizes' => [120, 200, 280]],
            ['name' => 'Paneer Lover Pizza',        'description' => 'Paneer, onion, capsicum and red paprika.',                                       'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'mild',   'sizes' => [140, 210, 300]],
            ['name' => 'Tandoori Paneer Pizza',     'description' => 'Paneer with tandoori sauce, onion and capsicum.',                                'category' => 'Veg Pizza',              'is_vegetarian' => true,  'spice_level' => 'medium', 'sizes' => [140, 210, 300]],

            // ── Premium Range Pizza ────────────────────────────────────────
            ['name' => 'Vegetable King Pizza',      'description' => 'Onion, tomato, capsicum, mushroom and black olives.',                            'category' => 'Premium Range Pizza',    'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [150, 230, 320]],
            ['name' => 'Maxican Pizza',             'description' => 'Onion, capsicum, tomato, jalapeno and red paprika.',                             'category' => 'Premium Range Pizza',    'is_vegetarian' => true,  'spice_level' => 'medium', 'sizes' => [150, 230, 320]],
            ['name' => 'Farm House Pizza',          'description' => 'Mushroom, tomato, onion and capsicum.',                                          'category' => 'Premium Range Pizza',    'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [150, 230, 320]],
            ['name' => 'Makhni Paneer Pizza',       'description' => 'Paneer, onion, capsicum, red paprika and olive.',                                'category' => 'Premium Range Pizza',    'is_vegetarian' => true,  'spice_level' => 'mild',   'sizes' => [160, 250, 330]],
            ['name' => 'Tandoori Veggies Pizza',    'description' => 'Onion, capsicum, mushroom, tomato, paneer, jalapeno, tandoori sauce and mint mayo.', 'category' => 'Premium Range Pizza','is_vegetarian' => true,  'spice_level' => 'medium', 'sizes' => [170, 260, 340]],
            ['name' => 'Veg Queen Pizza',           'description' => 'Onion, tomato, capsicum, mushroom, paneer, corn, black olives, red paprika and jalapeno.', 'category' => 'Premium Range Pizza', 'is_vegetarian' => true, 'spice_level' => 'medium', 'sizes' => [180, 270, 360]],

            // ── Non Veg Pizza ──────────────────────────────────────────────
            ['name' => 'Masala Chicken Pizza',      'description' => 'Spiced masala chicken on a classic pizza base.',                                 'category' => 'Non Veg Pizza',          'is_vegetarian' => false, 'spice_level' => 'medium', 'sizes' => [120, 220, 320]],
            ['name' => 'Peri Peri Pizza',           'description' => 'Fiery peri peri chicken with a kick.',                                           'category' => 'Non Veg Pizza',          'is_vegetarian' => false, 'spice_level' => 'hot',    'sizes' => [130, 230, 330]],
            ['name' => 'BBQ Chicken Pizza',         'description' => 'Smoky BBQ chicken with mozzarella.',                                             'category' => 'Non Veg Pizza',          'is_vegetarian' => false, 'spice_level' => 'mild',   'sizes' => [130, 230, 330]],
            ['name' => 'Chicken Sausage Pizza',     'description' => 'Juicy chicken sausage slices on tomato base.',                                   'category' => 'Non Veg Pizza',          'is_vegetarian' => false, 'spice_level' => 'mild',   'sizes' => [130, 230, 330]],
            ['name' => 'Murg Makhni Pizza',         'description' => 'Tender chicken in rich makhni sauce.',                                           'category' => 'Non Veg Pizza',          'is_vegetarian' => false, 'spice_level' => 'medium', 'sizes' => [140, 240, 340]],

            // ── Premium Range Non Pizza ────────────────────────────────────
            ['name' => 'Chicken Keema Pizza',       'description' => 'Keema, onion, capsicum, paprika and jalapeno.',                                  'category' => 'Premium Range Non Pizza','is_vegetarian' => false, 'spice_level' => 'hot',    'sizes' => [170, 270, 350]],
            ['name' => 'Chicken Seekh Kabab Pizza', 'description' => 'Seekh kabab, tomato, jalapeno, olive and onion.',                                'category' => 'Premium Range Non Pizza','is_vegetarian' => false, 'spice_level' => 'medium', 'sizes' => [180, 280, 360]],
            ['name' => 'Roasted Chicken Pizza',     'description' => 'Roast chicken, paprika, capsicum, onion and jalapeno.',                          'category' => 'Premium Range Non Pizza','is_vegetarian' => false, 'spice_level' => 'medium', 'sizes' => [180, 280, 360]],
            ['name' => 'Chicken Ultimate',          'description' => 'Roasted chicken, chicken keema, peri peri chicken, corn, onion, capsicum and olive.', 'category' => 'Premium Range Non Pizza', 'is_vegetarian' => false, 'spice_level' => 'hot', 'sizes' => [190, 280, 370]],
            ['name' => "Pizza World Spl.",          'description' => "Our signature house special pizza – the best of Pizza's World.",                  'category' => 'Premium Range Non Pizza','is_vegetarian' => false, 'spice_level' => 'hot',    'sizes' => [200, 290, 390]],

            // ── Extra Pizza Toppings ───────────────────────────────────────
            ['name' => 'Cheese Topping',            'description' => 'Extra cheese topping.',                                                          'category' => 'Extra Toppings',         'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [40,  80,  120]],
            ['name' => 'Vegetables Topping',        'description' => 'Extra mixed vegetables topping.',                                                'category' => 'Extra Toppings',         'is_vegetarian' => true,  'spice_level' => 'none',   'sizes' => [40,  60,  80]],
            ['name' => 'Chicken Topping',           'description' => 'Extra chicken topping.',                                                         'category' => 'Extra Toppings',         'is_vegetarian' => false, 'spice_level' => 'none',   'sizes' => [50,  100, 150]],
        ];

        $sizeLabels = ['Small', 'Medium', 'Large'];

        foreach ($menuItems as $item) {
            $sizes = array_map(
                fn ($price, $index) => ['label' => $sizeLabels[$index], 'price' => $price],
                $item['sizes'],
                array_keys($item['sizes'])
            );
            $restaurant->menuItems()->create([
                'name'             => $item['name'],
                'description'      => $item['description'],
                'price'            => $item['sizes'][0], // starting (minimum) price
                'sizes'            => $sizes,
                'category'         => $item['category'],
                'is_vegetarian'    => $item['is_vegetarian'],
                'spice_level'      => $item['spice_level'],
                'preparation_time' => 20,
                'is_available'     => true,
            ]);
        }

        $this->call(UserRoleSeeder::class);
    }
}

