<?php

namespace App\Console\Commands;

use App\Models\MenuItem;
use Illuminate\Console\Command;

class StripRedundantFoodTags extends Command
{
    protected $signature = 'menu-items:strip-redundant-food-tags';

    protected $description = 'Remove associações de food tag que duplicam uma cozinha que o próprio restaurante já tem (ex.: item marcado "Pizza" numa pizzaria). Não mexe em mais nada -- seguro rodar em produção sem re-seedar.';

    public function handle(): int
    {
        $removed = 0;

        MenuItem::with(['foodTags', 'category.menu.restaurant.cuisines'])
            ->get()
            ->each(function (MenuItem $item) use (&$removed) {
                $restaurant = $item->category->menu->restaurant;
                $cuisineSlugs = $restaurant->cuisines->pluck('slug')->all();
                $redundant = $item->foodTags->filter(fn ($tag) => in_array($tag->slug, $cuisineSlugs, true));

                if ($redundant->isNotEmpty()) {
                    $item->foodTags()->detach($redundant->pluck('id'));
                    $this->line("\"{$item->name}\" ({$restaurant->name}): removida(s) ".$redundant->pluck('name')->implode(', '));
                    $removed += $redundant->count();
                }
            });

        $this->info("{$removed} associação(ões) redundante(s) removida(s).");

        return self::SUCCESS;
    }
}
