<?php

use App\Http\Controllers\HotelPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HotelPageController::class, 'index'])->name('hotels.index');
Route::get('/bookings', fn() => Inertia::render('Bookings/Index'))->name('bookings.index');
