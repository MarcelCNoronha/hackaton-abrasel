<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Restaurant;
use Database\Seeders\RestaurantSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixRestaurantCategories extends Command
{
    protected $signature = 'restaurants:fix-categories';

    protected $description = 'Resincroniza a categoria de cada restaurante com o valor atual do RestaurantSeeder -- corrige atribuições antigas que ficaram presas (ex.: Sushi Mirim/Japa Nobre marcados como "Restaurante" de antes da categoria "Japonês" existir, já que categorias eram só adicionadas, nunca substituídas).';

    public function handle(): int
    {
        $fixed = 0;

        foreach (RestaurantSeeder::categoriesBySlug() as $slug => $categoryName) {
            $restaurant = Restaurant::where('slug', $slug)->first();
            $category = Category::where('slug', Str::slug($categoryName))->first();

            if (! $restaurant || ! $category) {
                continue;
            }

            $currentIds = $restaurant->categories()->pluck('categories.id')->sort()->values()->all();

            if ($currentIds !== [$category->id]) {
                $restaurant->categories()->sync([$category->id]);
                $this->line("{$restaurant->name}: categoria -> {$categoryName}");
                $fixed++;
            }
        }

        $this->info("{$fixed} restaurante(s) corrigido(s).");

        return self::SUCCESS;
    }
}
