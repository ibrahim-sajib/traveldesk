<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HotelApiService;
use Illuminate\Http\Request;
use RuntimeException;

class HotelController extends Controller
{
    public function index(Request $request, HotelApiService $service)
    {
        $v = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:80'], 'min_price' => ['nullable', 'numeric', 'min:0'], 'max_price' => ['nullable', 'numeric', 'gte:min_price'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:30'], 'page' => ['nullable', 'integer', 'min:1']]);
        try {
            $items = collect($service->list($v['search'] ?? null));
            if (! empty($v['city'])) {
                $items = $items->filter(fn($h) => strcasecmp($h['city'], $v['city']) === 0);
            }
            if (isset($v['min_price'])) {
                $items = $items->where('price_per_night', '>=', $v['min_price']);
            }
            if (isset($v['max_price'])) {
                $items = $items->where('price_per_night', '<=', $v['max_price']);
            }
            $per = (int) ($v['per_page'] ?? 9);
            $page = (int) ($v['page'] ?? 1);
            $total = $items->count();

            return response()->json(['data' => $items->forPage($page, $per)->values(), 'meta' => ['current_page' => $page, 'per_page' => $per, 'total' => $total, 'last_page' => max(1, (int) ceil($total / $per))]]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
    }

    public function show(int $hotel, HotelApiService $service)
    {
        try {
            $item = $service->find($hotel);

            return $item ? response()->json(['data' => $item]) : response()->json(['message' => 'Hotel not found.'], 404);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
    }
}
