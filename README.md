# FoodieExpress — Laravel API

A multi-sided food delivery marketplace REST API built with Laravel 11 + Sanctum.

## Roles
| Role | Description |
|---|---|
| `customer` | Browse restaurants, place & track orders |
| `restaurant_owner` | Manage restaurant, menu, and incoming orders |
| `delivery` | Accept and fulfill deliveries |
| `admin` | Full platform control |

## Setup

```bash
# 1. Install dependencies
composer install

# 2. Copy and configure environment
cp .env.example .env
# Edit .env → set DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Generate app key (already set if you used create-project)
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. Seed demo data (admin / owner / customer / delivery accounts + sample restaurant)
php artisan db:seed

# 6. Link storage for image uploads
php artisan storage:link

# 7. Start development server
php artisan serve
```

## Demo Credentials (after seeding)
| Role | Email | Password |
|---|---|---|
| Admin | admin@foodieexpress.com | password |
| Restaurant Owner | owner@foodieexpress.com | password |
| Customer | customer@foodieexpress.com | password |
| Delivery Partner | delivery@foodieexpress.com | password |

## Authentication
All protected routes require a `Bearer` token in the `Authorization` header.

```
POST /api/auth/register   — Register (role: customer | restaurant_owner | delivery)
POST /api/auth/login      — Login → returns token (valid 30 days)
POST /api/auth/logout     — Invalidate token
```

## Key API Endpoints

### Customer
```
GET  /api/restaurants                    — List approved restaurants (supports ?latitude=&longitude=&cuisine=&search=&sort=)
GET  /api/restaurants/{id}               — Restaurant detail + grouped menu
POST /api/orders                         — Place order (prices re-verified server-side)
GET  /api/orders                         — Order history (?filter=all|active|completed|cancelled)
GET  /api/orders/{id}                    — Order detail with status timeline
PUT  /api/orders/{id}/cancel             — Cancel order (before pickup)
GET  /api/auth/addresses                 — Saved addresses
POST /api/auth/addresses                 — Add address
PUT  /api/auth/addresses/{id}            — Update address
DELETE /api/auth/addresses/{id}          — Delete address
```

### Restaurant Owner
```
POST /api/restaurant                     — Create restaurant (one per owner)
GET  /api/restaurant/dashboard           — Stats + recent orders
GET  /api/restaurant/settings            — Restaurant profile
PUT  /api/restaurant/settings            — Update profile
GET  /api/restaurant/menu                — List menu items
POST /api/restaurant/menu                — Add menu item (multipart/form-data for image)
PUT  /api/restaurant/menu/{id}           — Update menu item
DELETE /api/restaurant/menu/{id}         — Delete menu item
GET  /api/restaurant/orders              — Order list (?status=pending|confirmed|...)
PUT  /api/restaurant/orders/{id}/advance — Advance: pending→confirmed→preparing→ready
PUT  /api/restaurant/orders/{id}/cancel  — Cancel order
```

### Delivery Partner
```
GET  /api/delivery/dashboard             — Stats + available orders + active deliveries
PUT  /api/delivery/availability          — Toggle online/offline
POST /api/delivery/orders/{id}/accept    — Accept a ready order
PUT  /api/delivery/orders/{id}/status    — Advance: out_for_delivery→picked_up→delivered
GET  /api/delivery/history               — Completed deliveries
```

### Admin
```
GET  /api/admin/dashboard                — Platform-wide stats
GET  /api/admin/users                    — List all users (?role=&search=)
POST /api/admin/users                    — Create user
PUT  /api/admin/users/{id}               — Update user (role, active status)
DELETE /api/admin/users/{id}             — Delete user
GET  /api/admin/restaurants              — List restaurants (?approved=&active=)
PUT  /api/admin/restaurants/{id}/approve — Approve restaurant
PUT  /api/admin/restaurants/{id}/toggle  — Toggle active/inactive
DELETE /api/admin/restaurants/{id}       — Delete restaurant (cascades menu items)
GET  /api/admin/orders                   — All orders (?status=)
PUT  /api/admin/orders/{id}/cancel       — Cancel any order
GET  /api/admin/delivery-partners        — List delivery partners
PUT  /api/admin/delivery-partners/{id}/verify             — Verify partner
PUT  /api/admin/delivery-partners/{id}/toggle-availability — Toggle availability
```

## Order Status Pipeline
```
Pending → Confirmed → Preparing → Ready → Out for Delivery → Picked Up → Delivered
                                                                        ↘ Cancelled
```

## Business Rules
- Prices are **always re-verified server-side** — client prices are ignored
- Tax is fixed at **10%** of subtotal
- Total = Subtotal + Delivery Fee + Tax
- Orders can only be cancelled before `picked_up` or `delivered`
- Restaurant delivery radius is enforced via Haversine distance calculation
- One restaurant per owner account
- Delivery partners must be verified by admin before accepting orders


<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
