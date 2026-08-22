<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fotos de capa/banner e de prato-em-destaque ilustrativas pros restaurantes reais de
 * RestaurantSeeder -- puramente pra demo nao ficar so com gradiente+emoji. Todas de
 * Wikimedia Commons, licencas livres (CC0 ou CC BY/BY-SA, creditadas abaixo); nao sao
 * fotos reais desses estabelecimentos nem dos pratos deles.
 *
 * Creditos (arquivo -- autor -- licenca):
 * - Pizza_with_food_waste.jpg -- (CC0)
 * - Food_plates,_Mania_de_Churrasco.jpg -- Tet -- CC0
 * - Sushi_platter,_Nikko,_Japan.jpg -- Joli Rumi -- CC BY-SA 4.0
 * - Japanese_Sushi_platter.jpg -- Luvpixy -- CC BY-SA 4.0
 * - Traditional_Churrasco_at_South_Brazil.jpg -- GSelau -- CC BY-SA 4.0
 * - Churrasco_carioca.jpg -- Leonardo "Leguas" Carvalho -- CC BY-SA 2.5
 * - Acai_bowl_(43779425762).jpg -- Ella Olsson -- CC BY 2.0
 * - Bife_a_parmegiana_com_arroz_e_batata_frita.jpg -- Márcia Cristina Machado -- CC BY-SA 4.0
 * - Feijoada_à_brasileira_1.jpg -- Melsj -- CC BY-SA 4.0
 * - A_homemade_hamburger.jpg -- TheGirlsNY -- CC BY-SA 2.0
 * - Coffee_cup_surrounded_by_coffee_beans.jpg -- Petr Kratochvil -- CC0
 * - Ice_Cream_Scoop.jpg -- Veganbaking.net -- CC BY-SA 2.0
 * Todas via commons.wikimedia.org/wiki/Special:Search.
 */
class DemoPhotoSeeder extends Seeder
{
    private const PIZZA = 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Pizza_with_food_waste.jpg/960px-Pizza_with_food_waste.jpg';

    private const CHURRASCO_MANIA = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Food_plates%2C_Mania_de_Churrasco.jpg/960px-Food_plates%2C_Mania_de_Churrasco.jpg';

    private const SUSHI_NIKKO = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Sushi_platter%2C_Nikko%2C_Japan.jpg/960px-Sushi_platter%2C_Nikko%2C_Japan.jpg';

    private const SUSHI_JAPANESE = 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Japanese_Sushi_platter.jpg/960px-Japanese_Sushi_platter.jpg';

    private const CHURRASCO_TRADICIONAL = 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Traditional_Churrasco_at_South_Brazil.jpg/960px-Traditional_Churrasco_at_South_Brazil.jpg';

    private const CHURRASCO_CARIOCA = 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Churrasco_carioca.jpg/960px-Churrasco_carioca.jpg';

    private const ACAI_BOWL = 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/19/Acai_bowl_%2843779425762%29.jpg/960px-Acai_bowl_%2843779425762%29.jpg';

    private const BIFE_ARROZ = 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Bife_a_parmegiana_com_arroz_e_batata_frita.jpg/960px-Bife_a_parmegiana_com_arroz_e_batata_frita.jpg';

    private const FEIJOADA = 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Feijoada_%C3%A0_brasileira_1.jpg/960px-Feijoada_%C3%A0_brasileira_1.jpg';

    private const HAMBURGER = 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d7/A_homemade_hamburger.jpg/960px-A_homemade_hamburger.jpg';

    private const COFFEE = 'https://upload.wikimedia.org/wikipedia/commons/2/20/Coffee_cup_surrounded_by_coffee_beans.jpg';

    private const ICE_CREAM = 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/96/Ice_Cream_Scoop.jpg/960px-Ice_Cream_Scoop.jpg';

    // Cacheado por URL em toda a execucao (restaurante e prato compartilham o mesmo cache)
    // pra nao baixar a mesma foto de novo quando reaproveitada entre varios estabelecimentos.
    private array $storedPathByUrl = [];

    public function run(): void
    {
        $this->seedRestaurantPhotos();
        $this->seedFeaturedItems();
    }

    private function seedRestaurantPhotos(): void
    {
        $photoByRestaurant = [
            'arte-sabor' => self::PIZZA,
            'sabor-e-cia' => self::PIZZA,
            'sushi-mirim' => self::SUSHI_NIKKO,
            'restaurante-villa-alfa' => self::CHURRASCO_MANIA,
            'japa-nobre' => self::SUSHI_JAPANESE,
            'o-barbante' => self::CHURRASCO_TRADICIONAL,
            'pizzaria-e-petiscaria-familia-mineira' => self::PIZZA,
            'choperia-e-churrascaria-devan' => self::CHURRASCO_CARIOCA,
            'beco-das-flores' => self::ACAI_BOWL,
            'pizzaria-sabor-do-sul' => self::PIZZA,
            'fabrica-hamburgueria-artesanal' => self::HAMBURGER,
            'cafeteria-viena-cafe' => self::COFFEE,
            'padaria-vesuvio' => self::COFFEE,
            'lanchonete-do-denis' => self::HAMBURGER,
            'sorveteria-zero-grau' => self::ICE_CREAM,
            'acai-mix-vicosa' => self::ACAI_BOWL,
        ];

        foreach ($photoByRestaurant as $slug => $url) {
            $restaurant = Restaurant::where('slug', $slug)->first();

            if (! $restaurant) {
                $this->command?->warn("Restaurante '{$slug}' nao encontrado, pulando foto.");

                continue;
            }

            $path = $this->download($url, 'restaurants');

            if ($path === null) {
                continue;
            }

            $restaurant->update([
                'cover_photo_path' => $path,
                'banner_photo_path' => $path,
            ]);
        }
    }

    /**
     * 1 prato por restaurante com foto propria + marcado como featured_menu_item_id -- o que
     * aparece no card de busca (ver RestaurantCard.vue) no lugar do icone generico, pensado
     * pra ser o prato mais "vendedor" de cada casa (com desconto, item mais caro/nobre, ou o
     * prato-assinatura mais obvio do cardapio).
     */
    private function seedFeaturedItems(): void
    {
        $itemByRestaurant = [
            'arte-sabor' => ['Rodízio premium', self::PIZZA],
            'sabor-e-cia' => ['Pizza calabresa', self::PIZZA],
            'sushi-mirim' => ['Combinado 20 peças', self::SUSHI_NIKKO],
            'restaurante-villa-alfa' => ['Filé ao molho madeira', self::BIFE_ARROZ],
            'japa-nobre' => ['Combinado Nobre (30 peças)', self::SUSHI_JAPANESE],
            'o-barbante' => ['Picanha na tábua', self::CHURRASCO_TRADICIONAL],
            'pizzaria-e-petiscaria-familia-mineira' => ['Feijão tropeiro', self::FEIJOADA],
            'choperia-e-churrascaria-devan' => ['Rodízio de carnes', self::CHURRASCO_CARIOCA],
            'beco-das-flores' => ['Tutu à mineira com couve', self::FEIJOADA],
            'pizzaria-sabor-do-sul' => ['Pizza portuguesa', self::PIZZA],
            'fabrica-hamburgueria-artesanal' => ['X-Fábrica', self::HAMBURGER],
            'cafeteria-viena-cafe' => ['Croissant com queijo e presunto', self::COFFEE],
            'padaria-vesuvio' => ['Pão na chapa com café com leite', self::COFFEE],
            'lanchonete-do-denis' => ['X-salada', self::HAMBURGER],
            'sorveteria-zero-grau' => ['Milk-shake de morango', self::ICE_CREAM],
            'acai-mix-vicosa' => ['Bowl de açaí com granola e morango', self::ACAI_BOWL],
        ];

        foreach ($itemByRestaurant as $slug => [$itemName, $url]) {
            $restaurant = Restaurant::where('slug', $slug)->first();

            if (! $restaurant) {
                continue;
            }

            $item = MenuItem::whereHas(
                'category.menu',
                fn ($menu) => $menu->where('restaurant_id', $restaurant->id)
            )->where('name', $itemName)->first();

            if (! $item) {
                $this->command?->warn("Item '{$itemName}' nao encontrado em '{$slug}', pulando destaque.");

                continue;
            }

            $path = $this->download($url, 'menu-items');

            if ($path !== null) {
                $item->update(['main_photo_path' => $path]);
            }

            $restaurant->forceFill(['featured_menu_item_id' => $item->id])->save();
        }
    }

    private function download(string $url, string $directory): ?string
    {
        if (isset($this->storedPathByUrl[$url])) {
            // Mesma foto, novo arquivo -- cada restaurante/prato precisa do proprio registro
            // em storage mesmo reaproveitando os bytes originais ja baixados.
            $sourcePath = $this->storedPathByUrl[$url];
            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $newPath = $directory.'/demo-'.Str::random(12).'.'.$extension;
            Storage::disk('public')->put($newPath, Storage::disk('public')->get($sourcePath));

            return $newPath;
        }

        // Wikimedia bloqueia com 403 requests sem um User-Agent que se identifique --
        // ver https://foundation.wikimedia.org/wiki/Policy:User-Agent_policy.
        $response = Http::withHeaders(['User-Agent' => 'VicosaFoodHackathonDemo/1.0 (+https://food.vicosa.tech)'])
            ->timeout(15)
            ->get($url);

        if (! $response->successful()) {
            $this->command?->warn("Falha ao baixar foto '{$url}': HTTP {$response->status()}.");

            return null;
        }

        $extension = Str::lower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
        $path = $directory.'/demo-'.Str::random(12).'.'.$extension;
        Storage::disk('public')->put($path, $response->body());
        $this->storedPathByUrl[$url] = $path;

        return $path;
    }
}
