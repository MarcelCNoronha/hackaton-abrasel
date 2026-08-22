<?php

namespace Database\Seeders;

use App\Enums\PriceRange;
use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\FoodTag;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Dados de demonstracao para a tela de descoberta (mapa/busca/filtros) do
 * Hackathon ABRASEL (Desafio 1 -- Rota Gastronomica Inteligente de Vicosa).
 * Nomes, bairros, enderecos e coordenadas sao de estabelecimentos reais de
 * Vicosa/MG (levantados via busca publica), mas nota, numero de avaliacoes,
 * cardapio e horarios sao aproximados/ilustrativos para fins de demonstracao
 * -- nao refletem necessariamente os dados reais e atuais de cada casa.
 */
class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = self::restaurantsData();

        foreach ($restaurants as $data) {
            $this->createRestaurant($data);
        }
    }

    /**
     * Slugs dos restaurantes atualmente definidos aqui -- usado por DemoActivitySeeder para
     * nunca gerar donos/avaliacoes/cupons para restaurantes obsoletos que ainda estejam no
     * banco (a descricao padrao sozinha nao basta pra distinguir, ela e' igual pra qualquer
     * restaurante que algum dia passou por este seeder, mesmo removido da lista abaixo).
     *
     * @return array<int, string>
     */
    public static function slugs(): array
    {
        return array_map(fn (array $r) => Str::slug($r['name']), self::restaurantsData());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function restaurantsData(): array
    {
        return [
            [
                'name' => 'Arte & Sabor',
                'neighborhood' => 'Centro',
                'category' => 'Pizzaria',
                'cuisines' => ['Pizza', 'Brasileiro'],
                'price_range' => PriceRange::High,
                'lat' => -20.7557897, 'lng' => -42.8754650,
                'rating' => 4.6, 'reviews' => 340,
                'menu' => [
                    'Rodízio' => [
                        ['name' => 'Rodízio tradicional', 'desc' => 'Pizzas, petiscos, refrigerante e massa liberados.', 'price' => 59.00, 'tags' => [], 'food_tags' => ['Rodízio']],
                        ['name' => 'Rodízio premium', 'desc' => 'Inclui camarões empanados, presunto parma com alho-poró e costela ao molho barbecue.', 'price' => 59.00, 'compare_at_price' => 69.00, 'tags' => [], 'food_tags' => ['Rodízio', 'Frutos do Mar']],
                    ],
                ],
                'hours' => ['mon-sun' => ['11:00', '23:30']],
            ],
            [
                'name' => 'Sabor e Cia',
                'neighborhood' => 'Centro',
                'category' => 'Pizzaria',
                'cuisines' => ['Pizza', 'Brasileiro'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7550814, 'lng' => -42.8799854,
                'rating' => 4.0, 'reviews' => 210,
                'menu' => [
                    'Pizzas' => [
                        ['name' => 'Pizza calabresa', 'desc' => 'Calabresa, cebola e mussarela.', 'price' => 32.90, 'compare_at_price' => 39.90, 'tags' => [], 'food_tags' => []],
                        ['name' => 'Pizza vegetariana', 'desc' => 'Legumes grelhados e mussarela.', 'price' => 42.90, 'tags' => ['Vegetariano'], 'food_tags' => []],
                    ],
                    'Bebidas' => [
                        ['name' => 'Chope artesanal', 'desc' => null, 'price' => 12.00, 'tags' => []],
                    ],
                ],
                'hours' => ['tue-sun' => ['18:00', '00:00']],
            ],
            [
                'name' => 'Sushi Mirim',
                'neighborhood' => 'Centro',
                'category' => 'Japonês',
                'cuisines' => ['Japonês'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7548500, 'lng' => -42.8797000,
                'rating' => 4.5, 'reviews' => 150,
                'menu' => [
                    'Combinados' => [
                        ['name' => 'Combinado 20 peças', 'desc' => 'Sushi e sashimi variados.', 'price' => 54.90, 'compare_at_price' => 64.90, 'tags' => [], 'food_tags' => ['Sushi', 'Frutos do Mar']],
                        ['name' => 'Temaki salmão', 'desc' => null, 'price' => 22.90, 'tags' => [], 'food_tags' => ['Temaki', 'Sushi']],
                    ],
                ],
                'hours' => ['tue-sun' => ['18:00', '23:30']],
            ],
            [
                'name' => 'Restaurante Villa Alfa',
                'neighborhood' => 'Centro',
                'category' => 'Restaurante',
                'cuisines' => ['Brasileiro'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7564514, 'lng' => -42.8775763,
                'rating' => 4.2, 'reviews' => 95,
                'menu' => [
                    'Pratos executivos' => [
                        ['name' => 'Filé ao molho madeira', 'desc' => 'Servido com arroz, farofa e vinagrete.', 'price' => 38.90, 'tags' => [], 'food_tags' => ['Prato Executivo', 'Arroz e Feijão']],
                        ['name' => 'Prato vegetariano do dia', 'desc' => null, 'price' => 29.90, 'tags' => ['Vegetariano'], 'food_tags' => ['Prato Executivo']],
                    ],
                ],
                'hours' => ['mon-sun' => ['06:30', '22:00']],
            ],
            [
                'name' => 'Japa Nobre',
                'neighborhood' => 'Centro',
                'category' => 'Japonês',
                'cuisines' => ['Japonês'],
                'price_range' => PriceRange::High,
                'lat' => -20.7572361, 'lng' => -42.8752678,
                'rating' => 4.3, 'reviews' => 180,
                'menu' => [
                    'Combinados' => [
                        ['name' => 'Combinado Nobre (30 peças)', 'desc' => null, 'price' => 94.90, 'tags' => [], 'food_tags' => ['Sushi', 'Frutos do Mar']],
                        ['name' => 'Hot roll sem glúten', 'desc' => 'Molho shoyu sem trigo.', 'price' => 26.90, 'tags' => ['Sem glúten'], 'food_tags' => ['Sushi']],
                    ],
                ],
                'hours' => ['tue-sun' => ['18:00', '23:00']],
            ],
            [
                'name' => 'O Barbante',
                'neighborhood' => 'Centro',
                'category' => 'Bar',
                'cuisines' => ['Brasileiro', 'Churrasco'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7535348, 'lng' => -42.8815291,
                'rating' => 4.1, 'reviews' => 220,
                'menu' => [
                    'Petiscos' => [
                        ['name' => 'Picanha na tábua', 'desc' => 'Acompanha farofa e vinagrete.', 'price' => 64.90, 'tags' => [], 'food_tags' => ['Picanha', 'Petiscos']],
                        ['name' => 'Torresmo de barriga', 'desc' => null, 'price' => 32.90, 'tags' => [], 'food_tags' => ['Petiscos']],
                    ],
                ],
                'hours' => ['tue-sun' => ['17:00', '00:00']],
            ],
            [
                'name' => 'Pizzaria e Petiscaria Família Mineira',
                'neighborhood' => 'São José do Triunfo',
                'category' => 'Pizzaria',
                'cuisines' => ['Pizza', 'Mineiro'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7513498, 'lng' => -42.8269674,
                'rating' => 4.4, 'reviews' => 130,
                'menu' => [
                    'Pizzas' => [
                        ['name' => 'Pizza mineira', 'desc' => 'Linguiça, pimenta biquinho e queijo canastra.', 'price' => 44.90, 'tags' => [], 'food_tags' => []],
                    ],
                    'Petiscos' => [
                        ['name' => 'Frango com quiabo sem lactose', 'desc' => null, 'price' => 28.90, 'tags' => ['Sem lactose'], 'food_tags' => ['Frango', 'Arroz e Feijão']],
                        ['name' => 'Feijão tropeiro', 'desc' => 'Feijão, couve, torresmo, bacon e farinha de mandioca.', 'price' => 26.90, 'tags' => [], 'food_tags' => ['Feijão Tropeiro', 'Arroz e Feijão']],
                    ],
                ],
                'hours' => ['tue-sun' => ['18:00', '23:30']],
            ],
            [
                'name' => 'Choperia e Churrascaria Devan',
                'neighborhood' => 'Santo Antônio',
                'category' => 'Churrascaria',
                'cuisines' => ['Churrasco', 'Brasileiro'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7429192, 'lng' => -42.8646143,
                'rating' => 4.2, 'reviews' => 175,
                'menu' => [
                    'Rodízio' => [
                        ['name' => 'Rodízio de carnes', 'desc' => 'Cortes nobres + buffet de saladas.', 'price' => 89.90, 'tags' => [], 'food_tags' => ['Rodízio', 'Picanha']],
                        ['name' => 'Buffet vegetariano avulso', 'desc' => null, 'price' => 49.90, 'tags' => ['Vegetariano', 'Sem glúten'], 'food_tags' => ['Arroz e Feijão']],
                    ],
                ],
                'hours' => ['tue-sun' => ['11:00', '23:00']],
            ],
            [
                'name' => 'Beco das Flores',
                'neighborhood' => 'Lourdes',
                'category' => 'Restaurante',
                'cuisines' => ['Brasileiro'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7572438, 'lng' => -42.8872071,
                'rating' => 4.5, 'reviews' => 160,
                'menu' => [
                    'Pratos executivos' => [
                        ['name' => 'Tutu à mineira com couve', 'desc' => 'Feijão, torresmo e couve refogada.', 'price' => 34.90, 'tags' => [], 'food_tags' => ['Prato Executivo', 'Arroz e Feijão']],
                        ['name' => 'Bowl vegano do dia', 'desc' => 'Grãos, legumes assados e húmus.', 'price' => 32.90, 'tags' => ['Vegano', 'Vegetariano'], 'food_tags' => ['Bowls']],
                    ],
                ],
                'hours' => ['mon-sat' => ['11:00', '15:00']],
            ],
            [
                'name' => 'Pizzaria Sabor do Sul',
                'neighborhood' => 'Centro',
                'category' => 'Pizzaria',
                'cuisines' => ['Pizza'],
                'price_range' => PriceRange::Low,
                'lat' => -20.7545000, 'lng' => -42.8790000,
                'rating' => 4.3, 'reviews' => 140,
                'menu' => [
                    'Pizzas' => [
                        ['name' => 'Pizza margherita', 'desc' => null, 'price' => 36.90, 'tags' => ['Vegetariano'], 'food_tags' => []],
                        ['name' => 'Pizza portuguesa', 'desc' => null, 'price' => 41.90, 'tags' => [], 'food_tags' => []],
                    ],
                ],
                'hours' => ['tue-sun' => ['18:00', '23:30']],
            ],
            // A partir daqui: 1 estabelecimento por tipo que ainda nao tinha nenhum
            // representante na lista acima (Hamburgueria, Cafeteria, Padaria, Lanchonete,
            // Sorveteria, Acai) -- sem isso a quickbar de categorias do Discover tinha icone
            // pra tipos que a busca nunca retornava nada.
            [
                'name' => 'Fábrica Hamburgueria Artesanal',
                'neighborhood' => 'Santo Antônio',
                'category' => 'Hamburgueria',
                'cuisines' => ['Hambúrguer'],
                'price_range' => PriceRange::Medium,
                'lat' => -20.7506241, 'lng' => -42.8638934,
                'rating' => 4.4, 'reviews' => 95,
                'menu' => [
                    'Burgers' => [
                        ['name' => 'X-Fábrica', 'desc' => 'Blend 180g, cheddar, bacon e cebola caramelizada no pão brioche.', 'price' => 28.90, 'tags' => [], 'food_tags' => []],
                        ['name' => 'Batata frita rústica', 'desc' => 'Com casca, alecrim e páprica.', 'price' => 14.90, 'tags' => ['Vegetariano'], 'food_tags' => ['Petiscos']],
                    ],
                ],
                'hours' => ['tue-sun' => ['18:00', '23:30']],
            ],
            [
                'name' => 'Cafeteria Viena Café',
                'neighborhood' => 'Centro',
                'category' => 'Cafeteria',
                'cuisines' => ['Café'],
                'price_range' => PriceRange::Low,
                'lat' => -20.7544069, 'lng' => -42.8804566,
                'rating' => 4.7, 'reviews' => 210,
                'menu' => [
                    'Cardápio' => [
                        ['name' => 'Croissant com queijo e presunto', 'desc' => null, 'price' => 14.90, 'tags' => [], 'food_tags' => ['Café da Manhã']],
                        ['name' => 'Bolo de fubá com café coado', 'desc' => null, 'price' => 12.90, 'tags' => ['Vegetariano'], 'food_tags' => ['Café da Manhã']],
                    ],
                ],
                'hours' => ['mon-sat' => ['07:00', '19:00']],
            ],
            [
                'name' => 'Padaria Vesúvio',
                'neighborhood' => 'Bela Vista',
                'category' => 'Padaria',
                'cuisines' => ['Café'],
                'price_range' => PriceRange::Low,
                'lat' => -20.7583470, 'lng' => -42.8789830,
                'rating' => 4.2, 'reviews' => 88,
                'menu' => [
                    'Cardápio' => [
                        ['name' => 'Pão na chapa com café com leite', 'desc' => null, 'price' => 8.90, 'tags' => [], 'food_tags' => ['Café da Manhã']],
                        ['name' => 'Combo pão de queijo (6 un.)', 'desc' => null, 'price' => 12.90, 'tags' => ['Vegetariano', 'Sem glúten'], 'food_tags' => []],
                    ],
                ],
                'hours' => ['mon-sun' => ['06:00', '20:00']],
            ],
            [
                'name' => 'Lanchonete do Denis',
                'neighborhood' => 'Centro',
                'category' => 'Lanchonete',
                'cuisines' => ['Hambúrguer'],
                'price_range' => PriceRange::Low,
                'lat' => -20.7580500, 'lng' => -42.8792500,
                'rating' => 4.0, 'reviews' => 60,
                'menu' => [
                    'Lanches' => [
                        ['name' => 'X-salada', 'desc' => 'Hambúrguer, alface, tomate e maionese da casa.', 'price' => 16.90, 'tags' => [], 'food_tags' => []],
                        ['name' => 'Misto quente', 'desc' => null, 'price' => 9.90, 'tags' => ['Vegetariano'], 'food_tags' => []],
                    ],
                ],
                'hours' => ['mon-sat' => ['07:00', '21:00']],
            ],
            [
                'name' => 'Sorveteria Zero Grau',
                'neighborhood' => 'Centro',
                'category' => 'Sorveteria',
                'cuisines' => ['Doces'],
                'price_range' => PriceRange::Low,
                'lat' => -20.7555262, 'lng' => -42.8781960,
                'rating' => 4.6, 'reviews' => 175,
                'menu' => [
                    'Sorvetes' => [
                        ['name' => 'Casquinha de creme com calda de chocolate', 'desc' => null, 'price' => 9.90, 'tags' => ['Vegetariano'], 'food_tags' => []],
                        ['name' => 'Milk-shake de morango', 'desc' => null, 'price' => 15.90, 'tags' => ['Vegetariano'], 'food_tags' => []],
                    ],
                ],
                'hours' => ['mon-sun' => ['12:00', '22:00']],
            ],
            [
                'name' => 'Açaí Mix Viçosa',
                'neighborhood' => 'Centro',
                'category' => 'Açaí',
                'cuisines' => ['Doces'],
                'price_range' => PriceRange::Low,
                'lat' => -20.7562976, 'lng' => -42.8798046,
                'rating' => 4.5, 'reviews' => 130,
                'menu' => [
                    'Açaí' => [
                        ['name' => 'Açaí 500ml completo', 'desc' => 'Granola, banana, leite em pó e leite condensado.', 'price' => 18.90, 'tags' => ['Vegetariano'], 'food_tags' => ['Açaí']],
                        ['name' => 'Bowl de açaí com granola e morango', 'desc' => null, 'price' => 22.90, 'tags' => ['Vegano', 'Vegetariano'], 'food_tags' => ['Açaí', 'Bowls']],
                    ],
                ],
                'hours' => ['mon-sun' => ['11:00', '22:00']],
            ],
        ];
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
                    'compare_at_price' => $item['compare_at_price'] ?? null,
                    'is_available' => true,
                    'position' => $itemPosition++,
                ]);

                if (! empty($item['tags'])) {
                    $tagIds = DietaryTag::whereIn('slug', array_map(Str::slug(...), $item['tags']))->pluck('id');
                    $menuItem->dietaryTags()->sync($tagIds);
                }

                if (! empty($item['food_tags'])) {
                    $foodTagIds = FoodTag::whereIn('slug', array_map(Str::slug(...), $item['food_tags']))->pluck('id');
                    $menuItem->foodTags()->sync($foodTagIds);
                }
            }
        }
    }
}
