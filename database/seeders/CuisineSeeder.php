<?php

namespace Database\Seeders;

use App\Models\Cuisine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CuisineSeeder extends Seeder
{
    public function run(): void
    {
        $cuisines = [
            'Hambúrguer', 'Pizza', 'Japonês', 'Brasileiro', 'Mineiro',
            'Italiano', 'Mexicano', 'Árabe', 'Churrasco', 'Massas', 'Doces', 'Café',
        ];

        foreach ($cuisines as $position => $name) {
            Cuisine::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $position]
            );
        }
    }
}
