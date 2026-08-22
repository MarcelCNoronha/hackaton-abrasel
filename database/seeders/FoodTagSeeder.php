<?php

namespace Database\Seeders;

use App\Models\FoodTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Tags de prato/comida especifica (diferente de Cuisine, que e' o estilo do
 * restaurante como um todo) -- permitem achar "feijão tropeiro" ou "hambúrguer"
 * mesmo em um restaurante cuja categoria/cozinha principal e' outra.
 */
class FoodTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Feijão Tropeiro', 'Feijoada', 'Arroz e Feijão', 'Hambúrguer', 'Pizza',
            'Rodízio', 'Petiscos', 'Sushi', 'Temaki', 'Tacos', 'Açaí', 'Sorvete',
            'Café da Manhã', 'Prato Executivo', 'Churrasco', 'Massas', 'Frutos do Mar',
            'Doces', 'Salgados', 'Frango', 'Picanha', 'Sopas', 'Esfiha', 'Bowls',
        ];

        foreach ($tags as $position => $name) {
            FoodTag::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $position]
            );
        }
    }
}
