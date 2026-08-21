<?php

namespace Database\Seeders;

use App\Enums\PriceRange;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Dados de demonstracao para a tela de descoberta (mapa/busca/filtros) --
 * nao representam estabelecimentos reais existentes em Vicosa. Coordenadas
 * espalhadas em torno do centro de Vicosa/MG (-20.7546, -42.8825), proximo
 * a UFV, como referencia geografica real para o Hackathon ABRASEL (Desafio 1
 * -- Rota Gastronomica Inteligente de Vicosa).
 */
class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = [
            [
                'name' => 'Burger Local',
                'neighborhood' => 'Centro',
                'category' => 'Hamburgueria',
                'cuisines' => ['Hambúrguer'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7538, 'lng' => -42.8811,
                'rating' => 4.7, 'reviews' => 324,
                'menu' => [
                    'Hambúrgueres' => [
                        ['name' => 'Smash Bacon', 'desc' => 'Pão brioche, carne 160g, cheddar, bacon e molho da casa.', 'price' => 32.90, 'tags' => []],
                        ['name' => 'Smash Veggie', 'desc' => 'Blend de grão-de-bico e cogumelos, queijo vegano e picles.', 'price' => 29.90, 'tags' => ['Vegano', 'Vegetariano']],
                    ],
                    'Bebidas' => [
                        ['name' => 'Limonada suíça', 'desc' => null, 'price' => 9.90, 'tags' => ['Vegano']],
                    ],
                ],
                'hours' => ['tue-sun' => ['11:30', '23:00']],
            ],
            [
                'name' => 'Cantina Nonna Rosa',
                'neighborhood' => 'Bela Vista',
                'category' => 'Restaurante',
                'cuisines' => ['Italiano', 'Massas'],
                'price_range' => PriceRange::High,
                'lat' => -20.7600, 'lng' => -42.8790,
                'rating' => 4.5, 'reviews' => 198,
                'menu' => [
                    'Massas' => [
                        ['name' => 'Fettuccine ao Sugo', 'desc' => 'Massa fresca da casa com molho de tomate San Marzano.', 'price' => 48.00, 'tags' => ['Vegetariano']],
                        ['name' => 'Risoto de Cogumelos', 'desc' => 'Arbóreo, mix de cogumelos e parmesão.', 'price' => 54.00, 'tags' => ['Vegetariano', 'Sem glúten']],
                    ],
                ],
                'hours' => ['tue-sun' => ['12:00', '23:30']],
            ],
            [
                'name' => 'Sushi Kaze',
                'neighborhood' => 'Nova Viçosa',
                'category' => 'Restaurante',
                'cuisines' => ['Japonês'],
                'price_range' => PriceRange::VeryHigh,
                'lat' => -20.7480, 'lng' => -42.8900,
                'rating' => 4.8, 'reviews' => 412,
                'menu' => [
                    'Sushi' => [
                        ['name' => 'Combinado Sakana (20 peças)', 'desc' => null, 'price' => 89.90, 'tags' => []],
                        ['name' => 'Hossomaki Sem Glúten', 'desc' => 'Molho shoyu sem trigo.', 'price' => 24.90, 'tags' => ['Sem glúten']],
                    ],
                ],
                'hours' => ['tue-sun' => ['18:00', '23:30']],
            ],
            [
                'name' => 'Padoca da Vila',
                'neighborhood' => 'Clélia Bernardes',
                'category' => 'Padaria',
                'cuisines' => ['Café', 'Doces'],
                'price_range' => PriceRange::Low,
                'lat' => -20.7490, 'lng' => -42.8760,
                'rating' => 4.6, 'reviews' => 156,
                'menu' => [
                    'Café da manhã' => [
                        ['name' => 'Pão na chapa', 'desc' => null, 'price' => 8.50, 'tags' => ['Vegetariano']],
                        ['name' => 'Bolo de fubá sem glúten', 'desc' => null, 'price' => 12.00, 'tags' => ['Sem glúten', 'Vegetariano']],
                    ],
                ],
                'hours' => ['mon-sun' => ['06:30', '20:00']],
            ],
            [
                'name' => 'Churrascaria Fogo Alto',
                'neighborhood' => 'Santo Antônio',
                'category' => 'Churrascaria',
                'cuisines' => ['Churrasco', 'Brasileiro'],
                'price_range' => PriceRange::High,
                'lat' => -20.7650, 'lng' => -42.8900,
                'rating' => 4.4, 'reviews' => 267,
                'menu' => [
                    'Rodízio' => [
                        ['name' => 'Rodízio completo', 'desc' => 'Cortes nobres + buffet de saladas.', 'price' => 129.90, 'tags' => []],
                        ['name' => 'Buffet vegetariano avulso', 'desc' => null, 'price' => 59.90, 'tags' => ['Vegetariano', 'Sem glúten']],
                    ],
                ],
                'hours' => ['tue-sun' => ['11:30', '22:30']],
            ],
            [
                'name' => 'Veggie Green Bowl',
                'neighborhood' => 'Centro',
                'category' => 'Restaurante',
                'cuisines' => ['Brasileiro'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7565, 'lng' => -42.8850,
                'rating' => 4.9, 'reviews' => 88,
                'menu' => [
                    'Bowls' => [
                        ['name' => 'Bowl Buda vegano', 'desc' => 'Grãos, legumes assados, húmus e tahine.', 'price' => 36.00, 'tags' => ['Vegano', 'Vegetariano', 'Sem glúten', 'Sem lactose']],
                        ['name' => 'Bowl proteico sem lactose', 'desc' => 'Frango grelhado, quinoa e vegetais.', 'price' => 39.00, 'tags' => ['Sem lactose', 'Sem glúten']],
                    ],
                ],
                'hours' => ['mon-sat' => ['11:00', '21:00']],
            ],
        ];

        foreach ($restaurants as $data) {
            $this->createRestaurant($data);
        }
    }

    private function createRestaurant(array $data): void
    {
        $restaurant = Restaurant::updateOrCreate(
            ['slug' => Str::slug($data['name'])],
            [
                'name' => $data['name'],
                'description' => "{$data['name']} -- dado de demonstração para a tela de descoberta (Hackathon ABRASEL / Rota Gastronômica Inteligente de Viçosa).",
                'address_neighborhood' => $data['neighborhood'],
                'address_city' => 'Viçosa',
                'address_state' => 'MG',
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'phone' => '+55 31 90000-0000',
                'whatsapp' => '+55 31 90000-0000',
                'price_range' => $data['price_range'],
                'average_rating' => $data['rating'],
                'reviews_count' => $data['reviews'],
                'verified_reviews_count' => $data['reviews'],
                'is_active' => true,
            ]
        );

        $category = Category::where('slug', Str::slug($data['category']))->first();
        if ($category) {
            $restaurant->categories()->syncWithoutDetaching([$category->id]);
        }

        $cuisineIds = Cuisine::whereIn('slug', array_map(Str::slug(...), $data['cuisines']))->pluck('id');
        $restaurant->cuisines()->syncWithoutDetaching($cuisineIds);

        $this->createBusinessHours($restaurant, $data['hours']);
        $this->createMenu($restaurant, $data['menu']);
    }

    private function createBusinessHours(Restaurant $restaurant, array $hours): void
    {
        $ranges = [
            'mon-sun' => range(0, 6),
            'tue-sun' => [0, 2, 3, 4, 5, 6],
            'mon-sat' => [1, 2, 3, 4, 5, 6],
        ];

        $restaurant->businessHours()->delete();

        foreach ($hours as $key => [$opensAt, $closesAt]) {
            $weekdays = $ranges[$key] ?? [];
            $closedWeekdays = array_diff(range(0, 6), $weekdays);

            foreach ($weekdays as $weekday) {
                $restaurant->businessHours()->create([
                    'weekday' => $weekday,
                    'opens_at' => $opensAt,
                    'closes_at' => $closesAt,
                    'is_closed' => false,
                ]);
            }

            foreach ($closedWeekdays as $weekday) {
                $restaurant->businessHours()->create([
                    'weekday' => $weekday,
                    'is_closed' => true,
                ]);
            }
        }
    }

    private function createMenu(Restaurant $restaurant, array $categories): void
    {
        $menu = $restaurant->menus()->updateOrCreate(
            ['name' => 'Cardápio principal'],
            ['is_active' => true]
        );

        $menu->categories()->delete();

        $position = 0;
        foreach ($categories as $categoryName => $items) {
            $menuCategory = $menu->categories()->create([
                'name' => $categoryName,
                'position' => $position++,
            ]);

            $itemPosition = 0;
            foreach ($items as $item) {
                $menuItem = $menuCategory->items()->create([
                    'name' => $item['name'],
                    'description' => $item['desc'],
                    'price' => $item['price'],
                    'is_available' => true,
                    'position' => $itemPosition++,
                ]);

                if (! empty($item['tags'])) {
                    $tagIds = DietaryTag::whereIn('slug', array_map(Str::slug(...), $item['tags']))->pluck('id');
                    $menuItem->dietaryTags()->sync($tagIds);
                }
            }
        }
    }
}
