<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\PriceRange;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\RestaurantClaim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $usersQuery = User::orderBy('name');
        if ($usersSearch = trim((string) $request->input('users_search'))) {
            $usersQuery->where('name', 'ilike', "%{$usersSearch}%");
        }

        $restaurantsQuery = Restaurant::orderBy('name')->with('owners:id,name,email');
        if ($restaurantsSearch = trim((string) $request->input('restaurants_search'))) {
            $restaurantsQuery->where('name', 'ilike', "%{$restaurantsSearch}%");
        }
        if ($restaurantsCategory = $request->input('restaurants_category')) {
            $restaurantsQuery->whereHas('categories', fn ($c) => $c->where('slug', $restaurantsCategory));
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'restaurants' => Restaurant::count(),
                'pendingClaims' => RestaurantClaim::where('status', ClaimStatus::Pending)->count(),
            ],
            'pendingClaims' => RestaurantClaim::where('status', ClaimStatus::Pending)
                ->with(['restaurant:id,name', 'user:id,name,email'])
                ->latest()
                ->get(),
            // Full profile columns (not just the ones the table renders) -- the "Editar"
            // dialog pre-fills its form straight from this same list.
            'users' => $this->paginate($usersQuery, ['id', 'name', 'email', 'role'], 'users_page'),
            'restaurants' => $this->paginate($restaurantsQuery, ['*'], 'restaurants_page'),
            'filters' => [
                'users_search' => $usersSearch ?: null,
                'restaurants_search' => $restaurantsSearch ?: null,
                'restaurants_category' => $restaurantsCategory,
            ],
            'categories' => Category::orderBy('position')->get(['id', 'name', 'slug']),
            'priceRanges' => array_column(PriceRange::cases(), 'value'),
        ]);
    }

    /**
     * Formato {data, meta} explicito (nao o array plano que LengthAwarePaginator serializa por
     * padrao, com current_page/last_page soltos na raiz) -- e' o que o Dashboard.vue espera.
     */
    private function paginate($query, array $columns, string $pageName): array
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate(15, $columns, $pageName);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
