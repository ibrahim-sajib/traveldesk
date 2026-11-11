# Practice Guide — How to train with TravelDesk

## Goal
Prepare to build a similar Laravel feature under live-coding conditions. Do not just read the solution. Trace it, change it, break it, test it, then rebuild a feature from scratch.

## First: get it running
Follow README.md exactly. Do not edit code until:
- [ ] Composer dependencies install
- [ ] `.env` configured and app key generated
- [ ] MySQL database created
- [ ] migrations complete
- [ ] npm dependencies install
- [ ] `php artisan serve` and `npm run dev` both run
- [ ] home page loads, a booking can be created, booking can be cancelled

## Understand the architecture
Hotel listing: Browser → `HotelPageController` → `HotelApiService` → Laravel HTTP Client → JSONPlaceholder → normalized data → Inertia/Vue.
Filter request: Vue → `GET /api/hotels` → API controller → service → JSON.
Booking: Vue → `POST /api/bookings` → Form Request → controller verifies hotel → server calculates total → DB transaction → JSON response.
Cancellation: Vue → PATCH endpoint → route model binding → business-rule checks → update status.

Be ready to explain why the HTTP request is in a service class, why the backend validates input, why the backend calculates price, and what a transaction protects.

## Practice sessions

### Session 1 — Project navigation (45 min)
- [ ] Run `php artisan route:list --path=api`.
- [ ] Read both route files.
- [ ] Trace hotel list from controller to service.
- [ ] Trace booking POST from Vue form to database.
- [ ] Explain Inertia page rendering versus Axios API calls.

### Session 2 — Laravel HTTP Client (60 min)
Edit `HotelApiService.php`:
- [ ] Try `/posts/1` and inspect the JSON shape.
- [ ] Add a query parameter to an external GET request.
- [ ] Change timeout and observe behaviour.
- [ ] Set a bad base URL and inspect the error response/log.
- [ ] Temporarily disable cache and compare repeated calls.
- [ ] Explain `acceptJson()`, `timeout()`, `retry()`, `throw()`, and `Http::fake()`.

Example:
```php
$response = Http::baseUrl($baseUrl)
    ->acceptJson()
    ->timeout(8)
    ->retry(2, 250)
    ->get('/posts');
$response->throw();
```
Retries are generally safer for idempotent GETs. Do not blindly retry booking/payment POSTs; that can create duplicates unless idempotency is supported.

### Session 3 — REST API (60–90 min)
- [ ] Add sort parameters to hotels; whitelist allowed sort keys.
- [ ] Add `status` filter to bookings.
- [ ] Add email filter to bookings.
- [ ] Return consistent success/error JSON.
- [ ] Test missing and invalid query parameters with Postman/curl.

### Session 4 — Booking logic (60–90 min)
- [ ] Add a booking reference such as `TD-000001`.
- [ ] Add a cancellation reason.
- [ ] Reject invalid date ranges.
- [ ] Add a maximum guest rule.
- [ ] Add tests for past check-in and repeated cancellation.
- [ ] Consider overlapping reservations.

For date overlap, an existing reservation conflicts when:
```text
existing.check_in < requested.check_out
AND existing.check_out > requested.check_in
```
A real booking engine also needs inventory and concurrency protection (locking/constraints). This demo does not manage room availability.

### Session 5 — Vue/Inertia (60 min)
- [ ] Add a status filter to the bookings page.
- [ ] Show validation errors beside fields.
- [ ] Add loading/empty/error states.
- [ ] Add pagination controls.
- [ ] Explain `ref`, `computed`, props, Axios, and Inertia navigation.

### Session 6 — Testing/debugging (60 min)
- [ ] Read both feature test files.
- [ ] Add a missing-hotel test.
- [ ] Add invalid-date test.
- [ ] Add repeated-cancel test.
- [ ] Add external 500 response test.
- [ ] Add a cache behaviour test.
- [ ] Reproduce a bug before fixing it; add a test that fails first.

## Live coding variations

### Task A — Third-party API integration (25 min)
Create `GET /api/destinations`. Fetch an external endpoint, normalize its response, support search, set timeout, handle failure, and write one `Http::fake()` test.

### Task B — CRUD API (35 min)
Create a `TravelPackage` resource with `title`, `destination`, `description`, `price`, `duration_days`, `status`. Build migration/model/Form Request/controller/routes for index/show/store/update/delete, pagination, and two tests.

### Task C — API + local database (40 min)
Fetch flights from a provider and allow a user to save a flight. Add table/model, validation, duplicate prevention, and tests. Use a unique composite index as the final duplicate guard.

### Task D — Booking conflict logic (45 min)
Reject a booking when the same room has overlapping confirmed dates. Explain why a simple pre-check can race under concurrent requests and what locking/database constraints add.

### Task E — Debug existing code (20 min)
Reproduce → inspect request/route → read `storage/logs/laravel.log` → inspect controller/service/query → add regression test → fix → rerun tests.

## Recommended workflow for any assignment
1. Read prompt fully. Identify input/output and edge cases.
2. Inspect repository conventions and installed packages.
3. Define route, request, response shape, status codes.
4. Build backend first: migration/model, validation, service, controller, route.
5. Test happy path and failure path with Postman/curl.
6. Add frontend only after the API contract works.
7. Add loading, empty, validation, and provider error states.
8. Run tests and formatter.
9. Explain what is done, assumptions, and remaining limitations.

## Questions to revise
- Dependency injection, route model binding, Form Request, mass assignment
- `findOrFail`, `firstOrFail`, `DB::transaction`
- PUT vs PATCH; REST methods; HTTP status codes
- N+1 queries and indexes
- API headers, query params, path params, body, auth, rate limits
- Why external calls need timeouts and safe retry rules
- Unit vs feature tests; `Http::fake()`
- Vue `ref` vs `computed`; Inertia visit vs Axios call

## 30-minute mock test
Build a favourites API:
- POST `/api/favourites`
- GET `/api/favourites?email=...`
- DELETE `/api/favourites/{id}`
- Store email, external hotel ID, hotel name.
- Same email cannot favourite the same hotel twice.
- Return 201 on create and 409 on duplicate.
- Add migration, model, validation, controller, routes, and two feature tests.

Review: backend validation, unique composite index `(email, hotel_external_id)`, correct status codes, and no real provider dependency in tests.

## Live test checklist
Before:
- [ ] IDE, terminal, Postman ready
- [ ] PHP/Composer/Node/MySQL working
- [ ] Know migration, model, Form Request, controller, route commands
- [ ] Know Laravel HTTP Client and `Http::fake()`

During:
- [ ] Clarify only genuinely ambiguous requirements
- [ ] State a short plan before coding
- [ ] Make a working backend path first
- [ ] Test one success and one failure
- [ ] Never expose API secrets to Vue
- [ ] Never trust client-calculated totals
- [ ] Communicate progress and trade-offs

After:
- [ ] Summarize files changed
- [ ] State tests actually run and their results
- [ ] Mention known limitations honestly

**Best use of your time:** rebuild Task A and Task B once without looking at this project, then repeat one under a 30-minute timer.
