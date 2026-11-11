<?php

namespace App\Http\Controllers;

use App\Services\HotelApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;

class HotelPageController extends Controller
{
    public function index(Request $request, HotelApiService $service)
    {
        try {
            $items = $service->list($request->string('search')->toString() ?: null);
            $error = null;
        } catch (RuntimeException $e) {
            $items = [];
            $error = $e->getMessage();
        }
        return Inertia::render('Hotels/Index', ['initialHotels' => $items, 'initialSearch' => $request->string('search')->toString(), 'providerError' => $error]);
    }
}
