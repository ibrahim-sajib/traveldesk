<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class HotelApiService
{
    public function list(?string $search = null): array
    {
        $items = Cache::remember('practice-hotels', now()->addSeconds((int)config('services.hotel_api.cache_ttl', 180)), function () {
            try {
                $response = Http::baseUrl(rtrim(config('services.hotel_api.base_url'), '/'))->acceptJson()->timeout((int)config('services.hotel_api.timeout', 8))->retry(2, 250)->get('/posts');
                $response->throw();
                return collect($response->json())->take(30)->map(fn($row) => $this->map($row))->values()->all();
            } catch (\Throwable $e) {
                Log::warning('Hotel API failed', ['error' => $e->getMessage()]);
                throw new RuntimeException('Hotel provider is temporarily unavailable.', 0, $e);
            }
        });
        if (blank($search)) return $items;
        return array_values(array_filter($items, fn($h) => str_contains(mb_strtolower($h['name'] . ' ' . $h['description'] . ' ' . $h['city']), mb_strtolower(trim($search)))));
    }
    public function find(int $id): ?array
    {
        return collect($this->list())->firstWhere('id', $id);
    }
    private function map(array $row): array
    {
        $id = (int)($row['id'] ?? 0);
        return [
            'id' => $id,
            'name' => ucwords($row['title'] ?? 'Unknown hotel'),
            'description' => ucfirst($row['body'] ?? ''),
            'city' => ['Dhaka', 'Cox’s Bazar', 'Sylhet', 'Chattogram', 'Rajshahi'][$id % 5],
            'price_per_night' => 2500 + (($id * 375) % 6500),
            'rating' => number_format(3.5 + (($id % 15) / 10), 1),
            'image' => 'https://picsum.photos/seed/traveldesk-' . $id . '/900/600'
        ];
    }
}
