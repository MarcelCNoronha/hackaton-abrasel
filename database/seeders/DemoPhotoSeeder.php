<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fotos de capa/banner ilustrativas pros 10 restaurantes reais de RestaurantSeeder --
 * puramente pra demo nao ficar so com gradiente+emoji. Todas de Wikimedia Commons,
 * licencas livres (CC0 ou CC BY/BY-SA, creditadas abaixo); nao sao fotos reais desses
 * estabelecimentos.
 *
 * Creditos (arquivo -- autor -- licenca):
 * - Pizza_with_food_waste.jpg -- (CC0)
 * - Food_plates,_Mania_de_Churrasco.jpg -- Tet -- CC0
 * - Sushi_platter,_Nikko,_Japan.jpg -- Joli Rumi -- CC BY-SA 4.0
 * - Japanese_Sushi_platter.jpg -- Luvpixy -- CC BY-SA 4.0
 * - Traditional_Churrasco_at_South_Brazil.jpg -- GSelau -- CC BY-SA 4.0
 * - Churrasco_carioca.jpg -- Leonardo "Leguas" Carvalho -- CC BY-SA 2.5
 * - Acai_bowl_(43779425762).jpg -- Ella Olsson -- CC BY 2.0
 * Todas via commons.wikimedia.org/wiki/Special:Search.
 */
class DemoPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $photoByRestaurant = [
            'arte-sabor' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Pizza_with_food_waste.jpg/960px-Pizza_with_food_waste.jpg',
            'sabor-e-cia' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Pizza_with_food_waste.jpg/960px-Pizza_with_food_waste.jpg',
            'sushi-mirim' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Sushi_platter%2C_Nikko%2C_Japan.jpg/960px-Sushi_platter%2C_Nikko%2C_Japan.jpg',
            'restaurante-villa-alfa' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Food_plates%2C_Mania_de_Churrasco.jpg/960px-Food_plates%2C_Mania_de_Churrasco.jpg',
            'japa-nobre' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Japanese_Sushi_platter.jpg/960px-Japanese_Sushi_platter.jpg',
            'o-barbante' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Traditional_Churrasco_at_South_Brazil.jpg/960px-Traditional_Churrasco_at_South_Brazil.jpg',
            'pizzaria-e-petiscaria-familia-mineira' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Pizza_with_food_waste.jpg/960px-Pizza_with_food_waste.jpg',
            'choperia-e-churrascaria-devan' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Churrasco_carioca.jpg/960px-Churrasco_carioca.jpg',
            'beco-das-flores' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/19/Acai_bowl_%2843779425762%29.jpg/960px-Acai_bowl_%2843779425762%29.jpg',
            'pizzaria-sabor-do-sul' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Pizza_with_food_waste.jpg/960px-Pizza_with_food_waste.jpg',
        ];

        // Cacheado por URL pra nao baixar a mesma foto 3x quando reaproveitada entre
        // restaurantes (pizza aparece em 4 deles).
        $storedPathByUrl = [];

        foreach ($photoByRestaurant as $slug => $url) {
            $restaurant = Restaurant::where('slug', $slug)->first();

            if (! $restaurant) {
                $this->command?->warn("Restaurante '{$slug}' nao encontrado, pulando foto.");

                continue;
            }

            if (! isset($storedPathByUrl[$url])) {
                // Wikimedia bloqueia com 403 requests sem um User-Agent que se identifique --
                // ver https://foundation.wikimedia.org/wiki/Policy:User-Agent_policy.
                $response = Http::withHeaders(['User-Agent' => 'VicosaFoodHackathonDemo/1.0 (+https://food.vicosa.tech)'])
                    ->timeout(15)
                    ->get($url);

                if (! $response->successful()) {
                    $this->command?->warn("Falha ao baixar foto pra '{$slug}': HTTP {$response->status()}.");

                    continue;
                }

                $extension = Str::lower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
                $path = 'restaurants/demo-'.Str::random(12).'.'.$extension;
                Storage::disk('public')->put($path, $response->body());
                $storedPathByUrl[$url] = $path;
            }

            $restaurant->update([
                'cover_photo_path' => $storedPathByUrl[$url],
                'banner_photo_path' => $storedPathByUrl[$url],
            ]);
        }
    }
}
