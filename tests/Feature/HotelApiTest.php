<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HotelApiTest extends TestCase
{
    public function test_hotel_api_maps_external_records(): void
    {
        Cache::flush();
        Http::fake(['jsonplaceholder.typicode.com/posts' => Http::response([['id' => 1, 'title' => 'sample hotel', 'body' => 'A nice stay']], 200)]);
        $this->getJson('/api/hotels')->assertOk()->assertJsonPath('data.0.name', 'Sample Hotel')->assertJsonStructure(['data', 'meta']);
    }
    public function test_external_provider_failure_returns_503(): void
    {
        Cache::flush();
        Http::fake(['jsonplaceholder.typicode.com/posts' => Http::response([], 500)]);
        $this->getJson('/api/hotels')->assertStatus(503);
    }
}
