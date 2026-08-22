<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Restaurant;
use App\Models\Visit;

/**
 * Estimativa de movimento por hora do dia, a partir do que ja existe: a tabela 'events'
 * (visualizacao de pagina, clique em telefone/whatsapp/rota, avaliacao enviada, cupom emitido/
 * resgatado) somada aos check-ins reais (Visit). Nao existe nenhum sensor de fila de verdade --
 * "movimento"/"tempo de espera" aqui sao SEMPRE estimativas relativas ao proprio historico do
 * restaurante (a hora com mais interacoes vira "alto", nunca um numero absoluto de pessoas),
 * por isso o front precisa deixar claro que e' estimado. Postgres-only (EXTRACT), como o resto
 * do app (ver DiscoveryController::haversineExpression / uso de ilike).
 */
class RestaurantTrafficReport
{
    private const SEGMENT_LABELS = [
        'restaurant_view' => 'Visualizações da página',
        'phone_click' => 'Cliques em telefone',
        'whatsapp_click' => 'Cliques em WhatsApp',
        'directions_click' => 'Cliques em rota',
        'review_created' => 'Avaliações enviadas',
        'coupon_issued' => 'Cupons emitidos',
        'coupon_used' => 'Cupons resgatados',
        'visit' => 'Check-ins confirmados',
    ];

    // Abaixo disso, uma unica interacao decidiria "o horario de pico" -- ruido, nao sinal.
    private const MIN_TOTAL_FOR_INSIGHTS = 5;

    /**
     * @return array{total: int, by_hour: array, peak_hour: ?array, quiet_hour: ?array, by_segment: array}
     */
    public static function for(Restaurant $restaurant): array
    {
        $countsByHour = array_fill(0, 24, 0);
        $bySegment = [];

        $eventRows = Event::where('restaurant_id', $restaurant->id)
            ->selectRaw('type, extract(hour from created_at)::int as hour, count(*) as total')
            ->groupBy('type', 'hour')
            ->get();

        foreach ($eventRows as $row) {
            $countsByHour[$row->hour] += (int) $row->total;
            $type = $row->type instanceof \BackedEnum ? $row->type->value : $row->type;
            $bySegment[$type] = ($bySegment[$type] ?? 0) + (int) $row->total;
        }

        $visitRows = Visit::where('restaurant_id', $restaurant->id)
            ->selectRaw('extract(hour from created_at)::int as hour, count(*) as total')
            ->groupBy('hour')
            ->get();

        foreach ($visitRows as $row) {
            $countsByHour[$row->hour] += (int) $row->total;
            $bySegment['visit'] = ($bySegment['visit'] ?? 0) + (int) $row->total;
        }

        $total = array_sum($countsByHour);
        $max = max($countsByHour);

        $byHour = [];
        foreach ($countsByHour as $hour => $count) {
            $level = self::level($count, $max);
            $byHour[] = [
                'hour' => $hour,
                'count' => $count,
                'level' => $level,
                'estimated_wait' => self::estimatedWait($level),
            ];
        }

        $withData = array_values(array_filter($byHour, fn ($h) => $h['count'] > 0));
        usort($withData, fn ($a, $b) => $b['count'] <=> $a['count']);
        $peak = $withData[0] ?? null;
        $quiet = $withData ? $withData[count($withData) - 1] : null;

        $segments = [];
        foreach ($bySegment as $type => $count) {
            $segments[] = ['type' => $type, 'label' => self::SEGMENT_LABELS[$type] ?? $type, 'count' => $count];
        }
        usort($segments, fn ($a, $b) => $b['count'] <=> $a['count']);

        return [
            'total' => $total,
            'by_hour' => $byHour,
            'peak_hour' => $total >= self::MIN_TOTAL_FOR_INSIGHTS ? $peak : null,
            'quiet_hour' => $total >= self::MIN_TOTAL_FOR_INSIGHTS ? $quiet : null,
            'by_segment' => $segments,
        ];
    }

    public static function hasEnoughDataForPublic(array $report): bool
    {
        return $report['total'] >= self::MIN_TOTAL_FOR_INSIGHTS;
    }

    /**
     * Versao simplificada pra pagina publica -- so' o nivel por hora e os destaques, sem
     * numeros absolutos nem quebra por segmento (isso e' ferramenta de gestao, nao pro cliente).
     */
    public static function publicSummary(array $report): ?array
    {
        if (! self::hasEnoughDataForPublic($report)) {
            return null;
        }

        return [
            'by_hour' => array_map(fn ($h) => ['hour' => $h['hour'], 'level' => $h['level']], $report['by_hour']),
            'peak_hour' => $report['peak_hour'] ? ['hour' => $report['peak_hour']['hour']] : null,
            'quiet_hour' => $report['quiet_hour'] ? ['hour' => $report['quiet_hour']['hour']] : null,
        ];
    }

    private static function level(int $count, int $max): string
    {
        if ($max === 0 || $count === 0) {
            return 'sem_dados';
        }

        $ratio = $count / $max;

        return match (true) {
            $ratio >= 0.66 => 'alto',
            $ratio >= 0.33 => 'medio',
            default => 'baixo',
        };
    }

    private static function estimatedWait(string $level): ?string
    {
        return match ($level) {
            'alto' => '20–30 min',
            'medio' => '10–15 min',
            'baixo' => '0–5 min',
            default => null,
        };
    }
}
