<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\FoodTag;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function storeCategory(Request $request, Menu $menu): RedirectResponse
    {
        $this->authorizeMenu($request, $menu);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $menu->categories()->create([
            'name' => $data['name'],
            'position' => $menu->categories()->count(),
        ]);

        return back()->with('status', 'Categoria adicionada.');
    }

    public function updateCategory(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $this->authorizeCategory($request, $menuCategory);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $menuCategory->update($data);

        return back()->with('status', 'Categoria atualizada.');
    }

    public function destroyCategory(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $this->authorizeCategory($request, $menuCategory);

        $menuCategory->delete();

        return back()->with('status', 'Categoria removida.');
    }

    public function storeItem(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $this->authorizeCategory($request, $menuCategory);

        $data = $this->validateItem($request);
        $foodTagNames = $data['food_tags'] ?? [];
        unset($data['food_tags']);

        $item = $menuCategory->items()->create($data + [
            'position' => $menuCategory->items()->count(),
        ]);

        $item->foodTags()->sync($this->resolveFoodTagIds($foodTagNames));

        return back()->with('status', 'Item adicionado ao cardápio.');
    }

    public function updateItem(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($request, $menuItem);

        $data = $this->validateItem($request);
        $foodTagNames = $data['food_tags'] ?? [];
        unset($data['food_tags']);

        $menuItem->update($data);
        $menuItem->foodTags()->sync($this->resolveFoodTagIds($foodTagNames));

        return back()->with('status', 'Item atualizado.');
    }

    /**
     * Tags de comida sao livres -- o gestor digita ingredientes/tipo de prato (ex.: "Frango",
     * "Feijão") e cada nome novo vira uma FoodTag reaproveitavel por slug, sem tela de
     * cadastro separada. Isso e' o que alimenta o filtro de busca por prato/ingrediente.
     *
     * @param  array<int, string>  $names
     * @return array<int, int>
     */
    private function resolveFoodTagIds(array $names): array
    {
        $position = FoodTag::max('position') + 1;

        return array_map(function (string $name) use (&$position) {
            $name = trim($name);
            $tag = FoodTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $position]
            );

            if ($tag->wasRecentlyCreated) {
                $position++;
            }

            return $tag->id;
        }, array_filter($names, fn ($name) => trim((string) $name) !== ''));
    }

    public function destroyItem(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($request, $menuItem);

        $menuItem->delete();

        return back()->with('status', 'Item removido.');
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price'],
            'is_available' => ['required', 'boolean'],
            'food_tags' => ['nullable', 'array'],
            'food_tags.*' => ['string', 'max:50'],
        ]);
    }

    private function authorizeMenu(Request $request, Menu $menu): void
    {
        RestaurantController::authorizeOwner($request, $menu->restaurant);
    }

    private function authorizeCategory(Request $request, MenuCategory $menuCategory): void
    {
        RestaurantController::authorizeOwner($request, $menuCategory->menu->restaurant);
    }

    private function authorizeItem(Request $request, MenuItem $menuItem): void
    {
        RestaurantController::authorizeOwner($request, $menuItem->category->menu->restaurant);
    }
}
