<?php

namespace Database\Seeders;

use App\Enums\DietaryTagKind;
use App\Models\DietaryTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DietaryTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Vegetariano', 'kind' => DietaryTagKind::Diet],
            ['name' => 'Vegano', 'kind' => DietaryTagKind::Diet],
            ['name' => 'Picante', 'kind' => DietaryTagKind::Diet],
            ['name' => 'Sem lactose', 'kind' => DietaryTagKind::Allergen],
            ['name' => 'Sem glúten', 'kind' => DietaryTagKind::Allergen],
            ['name' => 'Sem açúcar', 'kind' => DietaryTagKind::Allergen],
            ['name' => 'Sem amendoim/oleaginosas', 'kind' => DietaryTagKind::Allergen],
            ['name' => 'Sem frutos do mar', 'kind' => DietaryTagKind::Allergen],
            ['name' => 'Sem ovo', 'kind' => DietaryTagKind::Allergen],
        ];

        foreach ($tags as $position => $tag) {
            DietaryTag::updateOrCreate(
                ['slug' => Str::slug($tag['name'])],
                ['name' => $tag['name'], 'kind' => $tag['kind'], 'position' => $position]
            );
        }
    }
}
