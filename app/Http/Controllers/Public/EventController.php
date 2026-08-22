<?php

namespace App\Http\Controllers\Public;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint minimo pra registrar cliques em telefone/whatsapp/rota -- ao contrario de
 * "restaurant_view" (logado direto no controller, mesma requisicao que renderiza a pagina),
 * esses saem por um <a href> externo (tel:/wa.me/maps) que o backend nunca ve, entao precisam
 * de uma chamada dedicada disparada no clique. So' aceita os 3 tipos de clique -- os demais
 * EventType (view, avaliacao, cupom) ja sao logados no fluxo real que os gera.
 */
class EventController extends Controller
{
    private const TRACKABLE_TYPES = ['phone_click', 'whatsapp_click', 'directions_click'];

    public function track(Request $request): JsonResponse
    {
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'type' => ['required', 'string', 'in:'.implode(',', self::TRACKABLE_TYPES)],
        ]);

        Event::create([
            'type' => EventType::from($data['type']),
            'restaurant_id' => $data['restaurant_id'],
            'user_id' => $request->user()?->id,
        ]);

        return response()->json(status: 204);
    }
}
