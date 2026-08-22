<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Command;

class PruneLegacyDemoRestaurants extends Command
{
    protected $signature = 'restaurants:prune-legacy-demo';

    protected $description = 'Remove os restaurantes ficticios do primeiro seed (antes da troca pra restaurantes reais de Viçosa) que ficaram esquecidos em produção.';

    // Lista exata e fechada de proposito -- em vez de "qualquer restaurante fora de
    // RestaurantSeeder::slugs()", que apagaria sem querer um estabelecimento real
    // cadastrado manualmente por um admin depois do seed inicial.
    private const LEGACY_NAMES = [
        'Cantina Nonna Rosa', 'Sushi Kaze', 'Padoca da Vila',
        'Churrascaria Fogo Alto', 'Veggie Green Bowl', 'Burger Local',
    ];

    public function handle(): int
    {
        $restaurants = Restaurant::whereIn('name', self::LEGACY_NAMES)->get();

        if ($restaurants->isEmpty()) {
            $this->info('Nenhum restaurante legado encontrado -- nada a remover.');

            return self::SUCCESS;
        }

        foreach ($restaurants as $restaurant) {
            $this->line(sprintf(
                'Removendo "%s" (gestores=%d, avaliações=%d, cupons=%d)...',
                $restaurant->name,
                $restaurant->owners()->count(),
                $restaurant->reviews()->count(),
                $restaurant->coupons()->count(),
            ));
            $restaurant->delete();
        }

        $this->info(sprintf('%d restaurante(s) legado(s) removido(s).', $restaurants->count()));

        return self::SUCCESS;
    }
}
