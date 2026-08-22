<?php

namespace Database\Seeders;

use App\Models\JobSkill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Tags de habilidade profissional pro modulo de empregabilidade -- lista curada inicial,
 * mas o freelancer pode digitar uma nova na hora de montar o proprio perfil (mesmo padrao
 * de FoodTagSeeder + MenuController::resolveFoodTagIds()).
 */
class JobSkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'Cozinheiro(a)', 'Auxiliar de Cozinha', 'Garçom/Garçonete', 'Sushiman',
            'Churrasqueiro(a)', 'Confeiteiro(a)', 'Padeiro(a)', 'Bartender', 'Barback',
            'Copeiro(a)', 'Caixa', 'Gerente de Salão', 'Estoquista', 'Entregador(a)',
        ];

        foreach ($skills as $position => $name) {
            JobSkill::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $position]
            );
        }
    }
}
