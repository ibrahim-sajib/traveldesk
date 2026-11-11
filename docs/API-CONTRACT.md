# API Contract

## GET /api/hotels
Optional query: `search`, `city`, `min_price`, `max_price`, `per_page` (1–30), `page` (>=1). Response contains `data` and `meta` pagination fields.

## POST /api/bookings
Required: `hotel_id`, `guest_name`, `guest_email`, `check_in`, `check_out`, `guests` (1–12). Optional `notes`. Backend resolves canonical hotel and rate, then calculates total. Response: 201 with booking.

## Cancellation
`PATCH /api/bookings/{booking}/cancel`
- 200 cancelled
- 404 booking missing
- 409 already cancelled
- 422 check-in has passed

## Important demo limitation
The provider data and prices are fictional. For production, resolve price and availability from a trusted provider/database, protect booking endpoints with authentication, and implement inventory concurrency controls and payment idempotency.
