# TravelDesk — Laravel + Vue Practice Project

A full source-code practice application for Laravel live coding: external API integration, normalized hotel listings, search/filter/pagination, booking creation, validation, server-side price calculation, database transactions, cancellation, and Vue 3 + Inertia UI.

**Stack:** Laravel 12, PHP 8.2+, Vue 3, TypeScript, Inertia 2, MySQL, Laravel HTTP Client, JSONPlaceholder.

> ZIP contains the Laravel application source, core configuration, public entry point, and frontend source, but not generated Composer `vendor/` or npm `node_modules/` dependencies. Install dependencies locally. The demo provider is JSONPlaceholder `/posts`; records are transformed into fictional hotels. This is a learning project, not a production reservation/payment system.

## Requirements
PHP 8.2+, Composer 2, Node 20+, npm, MySQL 8+/MariaDB. Check with `php -v`, `composer -V`, `node -v`, `npm -v`, `mysql --version`.

## Installation
From this extracted project folder:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create a database:

```sql
CREATE DATABASE traveldesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Set DB credentials in `.env`, then:

```bash
php artisan migrate
npm install
```

Run backend and frontend in separate terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

Open http://127.0.0.1:8000.

## Main flow
1. `HotelPageController` loads records through `HotelApiService`.
2. Service calls JSONPlaceholder using Laravel HTTP Client, applies timeout/retry/cache, maps generic posts into hotel-shaped data, and logs provider failures.
3. Vue displays the listings and calls `/api/hotels` for search/filter.
4. Booking form sends `POST /api/bookings`.
5. Laravel validates input, verifies the hotel ID, calculates the total on the server, and writes the booking inside `DB::transaction()`.
6. Bookings page loads local bookings and can cancel a confirmed booking.

## API endpoints
| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/hotels` | List hotels |
| GET | `/api/hotels?search=beach` | Search |
| GET | `/api/hotels?city=Dhaka` | City filter |
| GET | `/api/hotels?min_price=2000&max_price=6000` | Price filter |
| GET | `/api/hotels?per_page=5&page=2` | Pagination |
| GET | `/api/hotels/{id}` | Hotel detail |
| GET | `/api/bookings` | List bookings |
| POST | `/api/bookings` | Create booking |
| GET | `/api/bookings/{id}` | Booking detail |
| PATCH | `/api/bookings/{id}/cancel` | Cancel booking |

### Example POST body
```json
{
  "hotel_id": 1,
  "guest_name": "Ibrahim",
  "guest_email": "ibrahim@example.com",
  "check_in": "2026-12-01",
  "check_out": "2026-12-04",
  "guests": 2
}
```
The backend resolves the hotel and rate from the external provider; the browser does not submit a price or total.

### Status codes
- `200`: successful GET/cancellation
- `201`: created
- `404`: record not found
- `409`: already cancelled
- `422`: validation/business-rule failure
- `503`: external provider unavailable

## Tests
```bash
php artisan test
php artisan test tests/Feature/HotelApiTest.php
php artisan test tests/Feature/BookingApiTest.php
```
Tests fake external HTTP calls and use SQLite in-memory. Ensure PHP's `pdo_sqlite` extension is enabled.

## Useful commands
```bash
php artisan route:list --path=api
php artisan migrate:status
php artisan test
./vendor/bin/pint
npm run build
```
`migrate:fresh` deletes local tables and data.

## Important files
- `app/Services/HotelApiService.php` — external API, mapping, cache, timeout, retry
- `app/Http/Controllers/Api/HotelController.php` — filters and pagination
- `app/Http/Controllers/Api/BookingController.php` — booking lifecycle
- `app/Http/Requests/StoreBookingRequest.php` — validation
- `app/Models/Booking.php` — Eloquent model
- `database/migrations/*bookings*` — schema
- `routes/api.php` — REST routes
- `resources/js/Pages` — Vue/Inertia pages
- `tests/Feature` — feature tests

Read `PRACTICE-GUIDE.md` for a structured training plan and live coding exercises.
