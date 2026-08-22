<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('landing'), 'changefreq' => 'monthly', 'priority' => '1.0'],
            ['loc' => route('discover'), 'changefreq' => 'daily', 'priority' => '0.9'],
        ];

        Restaurant::where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->get(['slug', 'updated_at'])
            ->each(function (Restaurant $restaurant) use (&$urls) {
                $urls[] = [
                    'loc' => route('restaurants.show', $restaurant->slug),
                    'lastmod' => $restaurant->updated_at->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
