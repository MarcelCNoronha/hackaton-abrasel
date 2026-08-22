<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

/**
 * Backfill de interacoes (visualizacao de pagina, cliques em telefone/whatsapp/rota) pros
 * ultimos 14 dias, com um perfil de horario plausivel (pico de almoco 12h-14h e jantar
 * 19h-21h) -- so' pro dashboard de movimento (ver App\Support\RestaurantTrafficReport) nao
 * aparecer vazio pra nenhum restaurante de demonstracao. A instrumentacao real (ver
 * ReviewController, Public\RestaurantController, Public\EventController) continua gerando
 * eventos novos organicamente a partir de agora, por cima desse historico.
 */
class DemoTrafficSeeder extends Seeder
{
    // peso relativo de interacao por hora do dia (0-23) -- maior = mais movimento.
    private const HOURLY_WEIGHTS = [
        0 => 1, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 1, 7 => 2,
        8 => 3, 9 => 3, 10 => 4, 11 => 6, 12 => 10, 13 => 10, 14 => 6, 15 => 3,
        16 => 3, 17 => 4, 18 => 6, 19 => 10, 20 => 10, 21 => 7, 22 => 3, 23 => 2,
    ];

    private const TYPE_WEIGHTS = [
        'restaurant_view' => 10,
        'phone_click' => 2,
        'whatsapp_click' => 2,
        'directions_click' => 3,
    ];

    private const DAYS = 14;

    public function run(): void
    {
        // Idempotente-o-bastante: se ja existe algum evento (rodou antes, ou ja tem trafego
        // real), nao duplica duas semanas de dados ficticios por cima.
        if (Event::count() > 0) {
            return;
        }

        $restaurantIds = Restaurant::whereIn('slug', RestaurantSeeder::slugs())->pluck('id');

        $rows = [];
        foreach ($restaurantIds as $restaurantId) {
            for ($daysAgo = 0; $daysAgo < self::DAYS; $daysAgo++) {
                $day = now()->subDays($daysAgo)->startOfDay();

                foreach (self::HOURLY_WEIGHTS as $hour => $weight) {
                    if ($weight === 0) {
                        continue;
                    }

                    foreach (self::TYPE_WEIGHTS as $type => $typeWeight) {
                        $count = (int) round($weight * $typeWeight / 10);

                        for ($i = 0; $i < $count; $i++) {
                            $rows[] = [
                                'type' => $type,
                                'restaurant_id' => $restaurantId,
                                'user_id' => null,
                                'menu_item_id' => null,
                                'metadata' => null,
                                'created_at' => $day->copy()->addHours($hour)->addMinutes(random_int(0, 59))->format('Y-m-d H:i:s'),
                            ];
                        }
                    }
                }
            }
        }

        // Bulk insert (nao Event::create() em loop) -- sao milhares de linhas so' de backfill
        // historico, sem observer nem side-effect nenhum pra preservar aqui.
        foreach (array_chunk($rows, 1000) as $chunk) {
            Event::insert($chunk);
        }
    }
}
