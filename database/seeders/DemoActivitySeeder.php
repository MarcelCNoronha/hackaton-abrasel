<?php

namespace Database\Seeders;

use App\Enums\CouponStatus;
use App\Enums\RestaurantOwnerRole;
use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\CouponCampaign;
use App\Models\CouponRedemption;
use App\Models\QrToken;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Dados de demonstracao de atividade (donos, avaliacoes, cupons, favoritos)
 * para os restaurantes reais semeados por RestaurantSeeder -- Hackathon
 * ABRASEL, Rota Gastronomica Inteligente de Vicosa. Tudo idempotente
 * (updateOrCreate/chaves unicas) para poder rodar de novo sem duplicar.
 */
class DemoActivitySeeder extends Seeder
{
    private const CONSUMER_COUNT = 20;

    private array $positiveComments = [
        'Atendimento excelente e comida saborosa, super recomendo!',
        'Ambiente agradável, com certeza vou voltar.',
        'Um dos melhores lugares para comer em Viçosa.',
        'Preço justo pela qualidade servida, saí satisfeito.',
        'Equipe muito atenciosa, experiência ótima do início ao fim.',
    ];

    private array $neutralComments = [
        'Bom, mas o atendimento poderia ser mais rápido.',
        'Comida boa, porém achei um pouco caro para a porção.',
        'Experiência tranquila, nada excepcional mas cumpriu o esperado.',
        'Ambiente legal, só senti falta de mais opções no cardápio.',
    ];

    private array $negativeComments = [
        'Demorou bastante para ser atendido, mas a comida compensou.',
        'Esperava mais pelo preço cobrado, pode melhorar.',
    ];

    private array $ownerReplies = [
        'Obrigado pela avaliação! Ficamos felizes que tenha gostado, volte sempre.',
        'Agradecemos o retorno! Vamos trabalhar nos pontos que você apontou.',
        'Que bom ter você como cliente, esperamos por sua próxima visita!',
    ];

    public function run(): void
    {
        $consumers = $this->createConsumers();

        // Restrito aos slugs atualmente definidos em RestaurantSeeder -- a descricao padrao
        // sozinha nao serve de filtro porque e' igual pra qualquer restaurante que algum dia
        // passou por aquele seeder, mesmo um ja removido da lista (mas ainda no banco).
        Restaurant::whereIn('slug', RestaurantSeeder::slugs())->get()->each(function (Restaurant $restaurant) use ($consumers) {
            $owner = $this->createOwner($restaurant);
            $reviews = $this->createVisitsAndReviews($restaurant, $consumers, $owner);
            $this->createFavorites($restaurant, $consumers);
            $campaign = $this->createCouponCampaign($restaurant);
            $this->createCoupons($restaurant, $campaign, $reviews);
        });
    }

    private function createConsumers(): array
    {
        $names = [
            'Ana Clara Souza', 'Bruno Almeida', 'Carla Mendes', 'Diego Ferreira', 'Elisa Cardoso',
            'Fábio Nogueira', 'Gabriela Rocha', 'Henrique Lima', 'Isabela Martins', 'João Pedro Costa',
            'Karina Duarte', 'Lucas Barbosa', 'Mariana Teixeira', 'Nicolas Araújo', 'Olívia Ramos',
            'Pedro Henrique Dias', 'Queila Fonseca', 'Rafael Moreira', 'Sabrina Pires', 'Thiago Correia',
        ];

        $consumers = [];
        foreach (array_slice($names, 0, self::CONSUMER_COUNT) as $index => $name) {
            $consumers[] = User::updateOrCreate(
                ['email' => sprintf('consumidor%02d@vicosafood.test', $index + 1)],
                [
                    'name' => $name,
                    'password' => 'password',
                    'role' => UserRole::Consumer,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $consumers;
    }

    private function createOwner(Restaurant $restaurant): User
    {
        $slug = $restaurant->slug;

        $owner = User::updateOrCreate(
            ['email' => "dono.{$slug}@vicosafood.test"],
            [
                'name' => "Gestor {$restaurant->name}",
                'password' => 'password',
                'role' => UserRole::Owner,
                'email_verified_at' => now(),
            ]
        );

        $restaurant->owners()->syncWithoutDetaching([
            $owner->id => ['role' => RestaurantOwnerRole::Owner],
        ]);

        return $owner;
    }

    /**
     * @return Collection<int, Review>
     */
    private function createVisitsAndReviews(Restaurant $restaurant, array $consumers, User $owner): Collection
    {
        $slug = $restaurant->slug;
        $reviewerCount = min(count($consumers), 4);
        $reviewers = array_slice($consumers, 0, $reviewerCount);

        $reviews = collect();

        foreach ($reviewers as $index => $consumer) {
            $visitedAt = now()->subDays(3 + ($index * 5))->setTime(random_int(11, 21), random_int(0, 59));

            $qrToken = QrToken::updateOrCreate(
                ['code' => "QR-{$slug}-{$index}"],
                [
                    'restaurant_id' => $restaurant->id,
                    'expires_at' => $visitedAt->copy()->addDay(),
                    'used_at' => $visitedAt,
                ]
            );

            $visit = Visit::updateOrCreate(
                ['qr_token_id' => $qrToken->id],
                [
                    'user_id' => $consumer->id,
                    'restaurant_id' => $restaurant->id,
                    'visited_at' => $visitedAt,
                ]
            );

            $rating = [5, 4, 5, 3][$index % 4];
            $comment = match (true) {
                $rating >= 5 => $this->positiveComments[$index % count($this->positiveComments)],
                $rating === 4 => $this->positiveComments[($index + 1) % count($this->positiveComments)],
                $rating === 3 => $this->neutralComments[$index % count($this->neutralComments)],
                default => $this->negativeComments[$index % count($this->negativeComments)],
            };

            $review = Review::updateOrCreate(
                ['visit_id' => $visit->id],
                [
                    'user_id' => $consumer->id,
                    'restaurant_id' => $restaurant->id,
                    'rating' => $rating,
                    'food_rating' => max(1, min(5, $rating)),
                    'service_rating' => max(1, min(5, $rating - ($index % 2))),
                    'ambience_rating' => max(1, min(5, $rating)),
                    'value_rating' => max(1, min(5, $rating - (($index + 1) % 2))),
                    'comment' => $comment,
                    'status' => 'published',
                    'created_at' => $visitedAt->copy()->addHours(2),
                    'updated_at' => $visitedAt->copy()->addHours(2),
                ]
            );

            if ($index % 2 === 0) {
                ReviewReply::updateOrCreate(
                    ['review_id' => $review->id],
                    [
                        'user_id' => $owner->id,
                        'message' => $this->ownerReplies[$index % count($this->ownerReplies)],
                    ]
                );
            }

            $reviews->push($review);
        }

        return $reviews;
    }

    private function createFavorites(Restaurant $restaurant, array $consumers): void
    {
        $favoriters = array_slice($consumers, 4, 3);
        $restaurant->favoritedByUsers()->syncWithoutDetaching(array_map(fn (User $u) => $u->id, $favoriters));
    }

    private function createCouponCampaign(Restaurant $restaurant): CouponCampaign
    {
        return CouponCampaign::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Desconto para quem avalia'],
            [
                'description' => 'Cupom de agradecimento para clientes que avaliam a visita.',
                'benefit_description' => '10% de desconto na próxima visita',
                'starts_at' => now()->subDays(30)->toDateString(),
                'ends_at' => now()->addDays(60)->toDateString(),
                'coupon_validity_days' => 30,
                'quantity_available' => 100,
                'per_user_limit' => 1,
                'is_active' => true,
            ]
        );
    }

    private function createCoupons(Restaurant $restaurant, CouponCampaign $campaign, Collection $reviews): void
    {
        foreach ($reviews as $index => $review) {
            if ($index % 2 !== 0) {
                continue;
            }

            $issuedAt = $review->created_at;
            $expiresAt = $issuedAt->copy()->addDays($campaign->coupon_validity_days);
            $status = $expiresAt->isPast() ? CouponStatus::Expired : ($index === 0 ? CouponStatus::Used : CouponStatus::Available);

            $coupon = Coupon::updateOrCreate(
                ['code' => sprintf('VF-%s-%03d', Str::upper(Str::substr($restaurant->slug, 0, 6)), $review->id)],
                [
                    'coupon_campaign_id' => $campaign->id,
                    'user_id' => $review->user_id,
                    'restaurant_id' => $restaurant->id,
                    'review_id' => $review->id,
                    'issued_at' => $issuedAt,
                    'expires_at' => $expiresAt,
                    'status' => $status,
                ]
            );

            if ($status === CouponStatus::Used) {
                CouponRedemption::updateOrCreate(
                    ['coupon_id' => $coupon->id],
                    [
                        'redeemed_by' => null,
                        'redeemed_at' => $issuedAt->copy()->addDays(2),
                        'notes' => 'Validado no balcão pela equipe do restaurante.',
                    ]
                );
            }
        }
    }
}
