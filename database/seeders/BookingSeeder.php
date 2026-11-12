<?php

namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = [
            ['id' => 1, 'name' => 'Grand Dhaka Hotel', 'price' => 2875],
            ['id' => 2, 'name' => 'Sea Pearl Resort', 'price' => 3250],
            ['id' => 3, 'name' => 'Royal Palace Hotel', 'price' => 4100],
            ['id' => 4, 'name' => 'City View Hotel', 'price' => 2500],
            ['id' => 5, 'name' => 'Green Valley Resort', 'price' => 3500],
        ];

        $statuses = ['confirmed', 'cancelled'];

        $bookings = [];

        for ($i = 0; $i < 100; $i++) {
            $hotel = fake()->randomElement($hotels);

            // Generate a future check-in date
            $checkIn = Carbon::today()->addDays(
                fake()->numberBetween(1, 90)
            );

            $nights = fake()->numberBetween(1, 10);

            $checkOut = $checkIn->copy()->addDays($nights);

            $totalPrice = $nights * $hotel['price'];

            $bookings[] = [
                'hotel_external_id' => $hotel['id'],
                'hotel_name' => $hotel['name'],
                'guest_name' => fake()->name(),
                'guest_email' => fake()->safeEmail(),
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'guests' => fake()->numberBetween(1, 5),
                'total_price' => $totalPrice,
                'status' => fake()->randomElement($statuses),
                'notes' => fake()->optional(0.5)->sentence(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($bookings, 25) as $chunk) {
            Booking::insert($chunk);
        }
    }
}