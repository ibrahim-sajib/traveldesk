<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\HotelApiService;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{


    public function index()
    {
        $bookings = Booking::query()
            ->orderByDesc('id')
            ->cursorPaginate(10);

        return response()->json([
            'data' => $bookings,
        ]);
    }

    public function store(StoreBookingRequest $request, HotelApiService $hotels)
    {
        $data = $request->validated();
        $hotel = $hotels->find((int)$data['hotel_id']);
        if (!$hotel) return response()->json(['message' => 'Selected hotel could not be verified.'], 422);
        $nights = \Carbon\Carbon::parse($data['check_in'])->diffInDays(\Carbon\Carbon::parse($data['check_out']));
        $booking = DB::transaction(fn() => Booking::create([
            'hotel_external_id' => $hotel['id'],
            'hotel_name' => $hotel['name'],
            'guest_name' => $data['guest_name'],
            'guest_email' => $data['guest_email'],
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'guests' => $data['guests'],
            'total_price' => $nights * $hotel['price_per_night'],
            'status' => 'confirmed',
            'notes' => $data['notes'] ?? null
        ]));
        return response()->json(['message' => 'Booking created.', 'data' => $booking], 201);
    }
    public function show(Booking $booking)
    {
        return response()->json(['data' => $booking]);
    }
    public function cancel(Booking $booking)
    {
        if ($booking->status === 'cancelled') return response()->json(['message' => 'Booking already cancelled.'], 409);
        if ($booking->check_in->isPast()) return response()->json(['message' => 'Cannot cancel after check-in date.'], 422);
        $booking->update(['status' => 'cancelled']);
        return response()->json(['data' => $booking->fresh()]);
    }
}
