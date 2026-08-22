<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\HireRequestStatus;
use App\Enums\JobApplicationStatus;
use App\Enums\JobPostingStatus;
use App\Enums\UserRole;
use App\Models\FreelancerRestaurantReview;
use App\Models\HireRequest;
use App\Models\JobApplication;
use App\Models\JobPosting;
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
        $this->createJobPostings($freelancers);
        $this->createRestaurantReviews($freelancers);
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

    /**
     * Vagas de demonstracao (aba Empregabilidade em Owner/Restaurants/Edit.vue e a lista de
     * vagas abertas do freelancer) com candidaturas em estados variados -- inclusive uma ja
     * aceita, pra mostrar como a candidatura vira HireRequest (mesmo efeito colateral de
     * Owner\JobApplicationController::accept(), replicado aqui a mao por ser seeder).
     *
     * @param  array<string, User>  $freelancers
     */
    private function createJobPostings(array $freelancers): void
    {
        $postings = [
            [
                'restaurant' => 'sushi-mirim',
                'title' => 'Sushiman para fins de semana',
                'description' => 'Reforço no balcão de sushi nas sextas, sábados e domingos. Experiência com corte de peixe é essencial.',
                'skills' => ['Sushiman'],
                'status' => JobPostingStatus::Open,
                'applicants' => [
                    ['freelancer' => 'Rafael Andrade', 'status' => JobApplicationStatus::Pending, 'message' => 'Tenho disponibilidade pros três dias, já trabalho com rodízio japonês.'],
                ],
            ],
            [
                'restaurant' => 'restaurante-villa-alfa',
                'title' => 'Garçom/Garçonete para o salão',
                'description' => 'Vaga para atendimento de mesas no horário de almoço, de segunda a sexta.',
                'skills' => ['Garçom/Garçonete'],
                'status' => JobPostingStatus::Open,
                'applicants' => [
                    ['freelancer' => 'Camila Rodrigues', 'status' => JobApplicationStatus::Pending, 'message' => null],
                    ['freelancer' => 'Larissa Souza', 'status' => JobApplicationStatus::Pending, 'message' => 'Tenho experiência nas duas funções, salão e cozinha.'],
                ],
            ],
            [
                'restaurant' => 'fabrica-hamburgueria-artesanal',
                'title' => 'Auxiliar de cozinha -- meio período',
                'description' => 'Apoio no pré-preparo e organização durante o turno da noite.',
                'skills' => ['Auxiliar de Cozinha'],
                'status' => JobPostingStatus::Open,
                'applicants' => [
                    ['freelancer' => 'Diego Santos', 'status' => JobApplicationStatus::Pending, 'message' => null],
                ],
            ],
            [
                'restaurant' => 'beco-das-flores',
                'title' => 'Confeiteiro(a) para produção de sobremesas',
                'description' => 'Produção de bolos e sobremesas para o cardápio fixo, 3x por semana.',
                'skills' => ['Confeiteiro(a)'],
                'status' => JobPostingStatus::Open,
                'applicants' => [
                    ['freelancer' => 'Patrícia Lima', 'status' => JobApplicationStatus::Accepted, 'message' => 'Já produzo em escala pra outros estabelecimentos, posso mandar fotos do portfólio.'],
                ],
            ],
            [
                'restaurant' => 'choperia-e-churrascaria-devan',
                'title' => 'Churrasqueiro(a) para eventos de fim de ano',
                'description' => 'Vaga encerrada -- equipe de eventos já completa.',
                'skills' => ['Churrasqueiro(a)'],
                'status' => JobPostingStatus::Closed,
                'applicants' => [
                    ['freelancer' => 'Marcos Vinícius', 'status' => JobApplicationStatus::Declined, 'message' => null],
                ],
            ],
        ];

        foreach ($postings as $data) {
            $restaurant = Restaurant::where('slug', $data['restaurant'])->first();
            $owner = User::where('email', "dono.{$data['restaurant']}@vicosafood.test")->first();

            if (! $restaurant || ! $owner) {
                $this->command?->warn("Pulando vaga '{$data['title']}' -- restaurante/dono nao encontrado.");

                continue;
            }

            $posting = JobPosting::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'title' => $data['title']],
                ['created_by' => $owner->id, 'description' => $data['description'], 'status' => $data['status']]
            );

            $skillIds = JobSkill::whereIn('slug', array_map(Str::slug(...), $data['skills']))->pluck('id');
            $posting->jobSkills()->sync($skillIds);

            foreach ($data['applicants'] as $applicant) {
                $freelancerUser = $freelancers[$applicant['freelancer']] ?? null;

                if (! $freelancerUser) {
                    continue;
                }

                $jobApplication = JobApplication::updateOrCreate(
                    ['job_posting_id' => $posting->id, 'freelancer_profile_id' => $freelancerUser->freelancerProfile->id],
                    ['message' => $applicant['message'], 'status' => $applicant['status']]
                );

                if ($applicant['status'] === JobApplicationStatus::Accepted && ! $jobApplication->hire_request_id) {
                    $hireRequest = HireRequest::updateOrCreate(
                        ['restaurant_id' => $restaurant->id, 'freelancer_profile_id' => $freelancerUser->freelancerProfile->id],
                        ['requested_by' => $owner->id, 'status' => HireRequestStatus::Accepted, 'responded_at' => now()]
                    );
                    $jobApplication->update(['hire_request_id' => $hireRequest->id]);
                }
            }
        }
    }

    /**
     * Avaliacoes de demonstracao na direcao contraria (freelancer avalia o restaurante) --
     * reaproveita as contratacoes aceitas ja criadas acima, so' pra nao inventar cenarios
     * novos. So' visivel pra outros freelancers, ver FreelancerRestaurantReview.
     *
     * @param  array<string, User>  $freelancers
     */
    private function createRestaurantReviews(array $freelancers): void
    {
        $reviews = [
            ['restaurant' => 'arte-sabor', 'freelancer' => 'Rafael Andrade', 'rating' => 5, 'comment' => 'Equipe organizada e pagamento em dia. Ambiente tranquilo pra trabalhar mesmo em dia de rodízio cheio.'],
            ['restaurant' => 'choperia-e-churrascaria-devan', 'freelancer' => 'Marcos Vinícius', 'rating' => 3, 'comment' => 'Movimento intenso e pouca pausa durante o turno, mas o combinado foi cumprido.'],
            ['restaurant' => 'beco-das-flores', 'freelancer' => 'Patrícia Lima', 'rating' => 5, 'comment' => 'Cozinha bem equipada e dona super acessível, recomendo pra outros confeiteiros.'],
        ];

        foreach ($reviews as $data) {
            $restaurant = Restaurant::where('slug', $data['restaurant'])->first();
            $freelancerUser = $freelancers[$data['freelancer']] ?? null;

            if (! $restaurant || ! $freelancerUser) {
                continue;
            }

            $hireRequest = HireRequest::where('restaurant_id', $restaurant->id)
                ->where('freelancer_profile_id', $freelancerUser->freelancerProfile->id)
                ->first();

            if (! $hireRequest) {
                continue;
            }

            FreelancerRestaurantReview::updateOrCreate(
                ['hire_request_id' => $hireRequest->id],
                [
                    'restaurant_id' => $restaurant->id,
                    'freelancer_profile_id' => $freelancerUser->freelancerProfile->id,
                    'rating' => $data['rating'],
                    'comment' => $data['comment'],
                ]
            );
        }
    }
}
