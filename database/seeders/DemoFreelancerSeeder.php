<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\HireRequestStatus;
use App\Enums\UserRole;
use App\Models\HireRequest;
use App\Models\JobSkill;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Dados de demonstracao pro modulo de empregabilidade -- perfis de freelancer com
 * habilidades/disponibilidade variadas, mais um punhado de contratacoes em estados
 * diferentes (pendente, aceita com avaliacao, recusada) pras telas nao aparecerem vazias.
 * Tudo idempotente (updateOrCreate/firstOrCreate), roda depois de RestaurantSeeder e
 * DemoActivitySeeder (reaproveita os "dono.{slug}@vicosafood.test" ja criados por ele).
 */
class DemoFreelancerSeeder extends Seeder
{
    public function run(): void
    {
        $freelancers = $this->createFreelancers();
        $this->createHireRequestsAndReviews($freelancers);
    }

    /**
     * @return array<string, User> nome => User (com freelancerProfile ja carregado)
     */
    private function createFreelancers(): array
    {
        $people = [
            'Rafael Andrade' => [
                'headline' => 'Sushiman com 6 anos de experiência em rodízio',
                'bio' => 'Já trabalhei em restaurantes japoneses em BH e Viçosa. Rápido no corte e boa apresentação de prato.',
                'skills' => ['Sushiman'],
                'status' => AvailabilityStatus::Immediate,
                'phone' => '+55 31 98888-0001',
            ],
            'Juliana Ferreira' => [
                'headline' => 'Cozinheira especializada em comida mineira',
                'bio' => 'Experiência em cozinha de restaurante e buffet self-service. Disponível pra turnos noturnos.',
                'skills' => ['Cozinheiro(a)'],
                'status' => AvailabilityStatus::Scheduled,
                'phone' => '+55 31 98888-0002',
                'slots' => ['tue-sat' => ['18:00', '23:00']],
            ],
            'Marcos Vinícius' => [
                'headline' => 'Churrasqueiro para eventos e rodízio',
                'bio' => 'Atuo com churrasco de rodízio há 4 anos, já trabalhei em churrascarias e eventos particulares.',
                'skills' => ['Churrasqueiro(a)'],
                'status' => AvailabilityStatus::Immediate,
                'phone' => '+55 31 98888-0003',
            ],
            'Camila Rodrigues' => [
                'headline' => 'Garçonete com experiência em salão',
                'bio' => 'Atendimento ágil e simpático, já trabalhei em restaurante à la carte e eventos.',
                'skills' => ['Garçom/Garçonete'],
                'status' => AvailabilityStatus::Unavailable,
                'phone' => '+55 31 98888-0004',
            ],
            'Bruno Cardoso' => [
                'headline' => 'Bartender pra eventos e finais de semana',
                'bio' => 'Drinks clássicos e autorais, disponível pra freelas de sexta a domingo.',
                'skills' => ['Bartender'],
                'status' => AvailabilityStatus::Scheduled,
                'phone' => '+55 31 98888-0005',
                'slots' => ['fri-sun' => ['20:00', '02:00']],
            ],
            'Patrícia Lima' => [
                'headline' => 'Confeiteira -- bolos, doces e sobremesas',
                'bio' => 'Confeitaria artesanal e produção em escala pra restaurante e cafeteria.',
                'skills' => ['Confeiteiro(a)'],
                'status' => AvailabilityStatus::Immediate,
                'phone' => '+55 31 98888-0006',
            ],
            'Diego Santos' => [
                'headline' => 'Auxiliar de cozinha, disponibilidade a combinar',
                'bio' => 'Experiência com pré-preparo, organização de estoque e apoio geral de cozinha.',
                'skills' => ['Auxiliar de Cozinha'],
                'status' => AvailabilityStatus::Unavailable,
                'phone' => '+55 31 98888-0007',
            ],
            'Larissa Souza' => [
                'headline' => 'Garçonete e cozinheira -- flexível',
                'bio' => 'Já atuei nas duas funções em restaurantes pequenos, me adapto ao que o estabelecimento precisar.',
                'skills' => ['Garçom/Garçonete', 'Cozinheiro(a)'],
                'status' => AvailabilityStatus::Scheduled,
                'phone' => '+55 31 98888-0008',
                'slots' => ['mon-fri' => ['11:00', '15:00']],
            ],
        ];

        $users = [];

        foreach ($people as $name => $data) {
            $slug = Str::slug($name);
            $user = User::updateOrCreate(
                ['email' => "freelancer.{$slug}@vicosafood.test"],
                ['name' => $name, 'password' => 'password', 'role' => UserRole::Consumer, 'phone' => $data['phone'], 'email_verified_at' => now()]
            );
            $user->forceFill(['freelancer_enabled_at' => now()])->save();

            $profile = $user->freelancerProfile()->updateOrCreate([], [
                'headline' => $data['headline'],
                'bio' => $data['bio'],
                'availability_status' => $data['status'],
            ]);

            $skillIds = JobSkill::whereIn('slug', array_map(Str::slug(...), $data['skills']))->pluck('id');
            $profile->jobSkills()->syncWithoutDetaching($skillIds);

            if ($data['status'] === AvailabilityStatus::Scheduled && isset($data['slots'])) {
                $this->createAvailabilitySlots($profile, $data['slots']);
            }

            $users[$name] = $user;
        }

        return $users;
    }

    private function createAvailabilitySlots($profile, array $ranges): void
    {
        $weekdaySets = [
            'mon-fri' => [1, 2, 3, 4, 5],
            'tue-sat' => [2, 3, 4, 5, 6],
            'fri-sun' => [5, 6, 0],
        ];

        $profile->availabilitySlots()->delete();

        foreach ($ranges as $key => [$opensAt, $closesAt]) {
            $activeWeekdays = $weekdaySets[$key] ?? [];

            foreach (range(0, 6) as $weekday) {
                $isActive = in_array($weekday, $activeWeekdays, true);
                $profile->availabilitySlots()->create([
                    'weekday' => $weekday,
                    'is_off' => ! $isActive,
                    'opens_at' => $isActive ? $opensAt : null,
                    'closes_at' => $isActive ? $closesAt : null,
                ]);
            }
        }
    }

    /**
     * @param  array<string, User>  $freelancers
     */
    private function createHireRequestsAndReviews(array $freelancers): void
    {
        $scenarios = [
            [
                'restaurant' => 'arte-sabor',
                'freelancer' => 'Rafael Andrade',
                'status' => HireRequestStatus::Accepted,
                'review' => ['rating' => 5, 'to_freelancer' => 'Excelente trabalho, super pontual e caprichoso no corte. Já quero chamar de novo!', 'to_owners' => 'Profissional confiável, recomendo sem ressalvas.'],
            ],
            [
                'restaurant' => 'choperia-e-churrascaria-devan',
                'freelancer' => 'Marcos Vinícius',
                'status' => HireRequestStatus::Accepted,
                'review' => ['rating' => 4, 'to_freelancer' => 'Bom trabalho, só peço mais atenção ao horário de chegada.', 'to_owners' => 'Trabalho bom, chegou um pouco atrasado no primeiro turno.'],
            ],
            [
                'restaurant' => 'o-barbante',
                'freelancer' => 'Bruno Cardoso',
                'status' => HireRequestStatus::Pending,
                'message' => 'Precisamos de um bartender pro fim de semana que vem, sexta e sábado à noite.',
            ],
            [
                'restaurant' => 'sabor-e-cia',
                'freelancer' => 'Camila Rodrigues',
                'status' => HireRequestStatus::Declined,
                'message' => 'Temos uma vaga de garçonete pros finais de semana, teria interesse?',
            ],
        ];

        foreach ($scenarios as $scenario) {
            $restaurant = Restaurant::where('slug', $scenario['restaurant'])->first();
            $freelancerUser = $freelancers[$scenario['freelancer']] ?? null;
            $owner = User::where('email', "dono.{$scenario['restaurant']}@vicosafood.test")->first();

            if (! $restaurant || ! $freelancerUser || ! $owner) {
                $this->command?->warn("Pulando cenario de contratacao pra '{$scenario['freelancer']}' -- restaurante/dono nao encontrado.");

                continue;
            }

            $hireRequest = HireRequest::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'freelancer_profile_id' => $freelancerUser->freelancerProfile->id],
                [
                    'requested_by' => $owner->id,
                    'status' => $scenario['status'],
                    'message' => $scenario['message'] ?? null,
                    'responded_at' => $scenario['status'] === HireRequestStatus::Pending ? null : now(),
                ]
            );

            if (isset($scenario['review'])) {
                $review = $hireRequest->review()->updateOrCreate([], [
                    'restaurant_id' => $restaurant->id,
                    'freelancer_profile_id' => $freelancerUser->freelancerProfile->id,
                    'rating' => $scenario['review']['rating'],
                    'feedback_to_freelancer' => $scenario['review']['to_freelancer'],
                    'feedback_to_owners' => $scenario['review']['to_owners'],
                ]);

                // Simula o freelancer ja tendo confirmado o vinculo -- sem isso a avaliacao de
                // demonstracao ficaria presa em "pending_approval" pra sempre e nao apareceria
                // pra outros donos nem contaria pra nota (ver ReviewApprovalStatus).
                $review->forceFill(['status' => 'approved'])->save();
            }
        }
    }
}
