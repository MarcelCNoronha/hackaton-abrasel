<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_active_restaurants_and_static_pages(): void
    {
        $active = Restaurant::create([
            'name' => 'Restaurante Ativo',
            'slug' => 'restaurante-ativo',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
            'is_active' => true,
        ]);
        $inactive = Restaurant::create([
            'name' => 'Restaurante Inativo',
            'slug' => 'restaurante-inativo',
            'latitude' => -20.7546,
            'longitude' => -42.8825,
            'is_active' => false,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(route('restaurants.show', $active), false);
        $response->assertDontSee(route('restaurants.show', $inactive), false);
        $response->assertSee(route('discover'), false);
    }
}
