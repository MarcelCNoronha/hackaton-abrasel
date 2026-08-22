<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Restaurante', 'Bar', 'Hamburgueria', 'Pizzaria', 'Cafeteria',
            'Padaria', 'Lanchonete', 'Sorveteria', 'Açaí', 'Churrascaria', 'Japonês',
        ];

        foreach ($categories as $position => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $position]
            );
        }
    }
}
