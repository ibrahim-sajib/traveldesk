<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
  use RefreshDatabase;
  protected function setUp(): void
  {
    parent::setUp();
    Cache::flush();
    Http::fake(['jsonplaceholder.typicode.com/*' => Http::response([]), 'jsonplaceholder.typicode.com/posts' => Http::response([['id' => 1, 'title' => 'sample hotel', 'body' => 'stay']], 200)]);
  }
  public function test_booking_can_be_created_and_total_is_calculated_by_server(): void
  {
    $this->postJson('/api/bookings', ['hotel_id' => 1, 'guest_name' => 'Ibrahim', 'guest_email' => 'ibrahim@example.com', 'check_in' => now()->addDays(5)->toDateString(), 'check_out' => now()->addDays(8)->toDateString(), 'guests' => 2])
      ->assertCreated()->assertJsonPath('data.total_price', '8625.00');
    $this->assertDatabaseHas('bookings', ['guest_email' => 'ibrahim@example.com', 'status' => 'confirmed']);
  }
  public function test_invalid_booking_input_is_rejected(): void
  {
    $this->postJson('/api/bookings', ['hotel_id' => 1, 'guest_name' => '', 'guest_email' => 'bad', 'check_in' => now()->addDays(5)->toDateString(), 'check_out' => now()->addDays(2)->toDateString(), 'guests' => 0])
      ->assertUnprocessable()->assertJsonValidationErrors(['guest_name', 'guest_email', 'check_out', 'guests']);
  }
  public function test_booking_can_be_cancelled(): void
  {
    $b = Booking::create(['hotel_external_id' => 1, 'hotel_name' => 'Hotel', 'guest_name' => 'Test', 'guest_email' => 't@example.com', 'check_in' => now()->addDays(5)->toDateString(), 'check_out' => now()->addDays(7)->toDateString(), 'guests' => 2, 'total_price' => 5000, 'status' => 'confirmed']);
    $this->patchJson("/api/bookings/{$b->id}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');
  }
}
