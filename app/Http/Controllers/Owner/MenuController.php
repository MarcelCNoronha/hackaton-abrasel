<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\FoodTag;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        $dietaryTagIds = $data['dietary_tags'] ?? [];
        unset($data['food_tags'], $data['dietary_tags']);
        $this->assertFoodTagsDontDuplicateCuisine($foodTagNames, $menuCategory->menu->restaurant);

        if ($request->hasFile('photo')) {
            $data['main_photo_path'] = $request->file('photo')->store('menu-items', 'public');
        }
        unset($data['photo']);

        $item = $menuCategory->items()->create($data + [
            'position' => $menuCategory->items()->count(),
        ]);

        $item->foodTags()->sync($this->resolveFoodTagIds($foodTagNames));
        $item->dietaryTags()->sync($dietaryTagIds);

        return back()->with('status', 'Item adicionado ao cardápio.');
    }

    public function updateItem(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($request, $menuItem);

        $data = $this->validateItem($request);
        $foodTagNames = $data['food_tags'] ?? [];
        $dietaryTagIds = $data['dietary_tags'] ?? [];
        unset($data['food_tags'], $data['dietary_tags']);
        $this->assertFoodTagsDontDuplicateCuisine($foodTagNames, $menuItem->category->menu->restaurant);

        if ($request->hasFile('photo')) {
            if ($menuItem->main_photo_path) {
                Storage::disk('public')->delete($menuItem->main_photo_path);
            }
            $data['main_photo_path'] = $request->file('photo')->store('menu-items', 'public');
        }
        unset($data['photo']);

        $menuItem->update($data);
        $menuItem->foodTags()->sync($this->resolveFoodTagIds($foodTagNames));
        $menuItem->dietaryTags()->sync($dietaryTagIds);

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

    /**
     * Uma food tag igual a uma cozinha que o proprio restaurante ja tem (ex.: marcar "Pizza"
     * num prato de uma pizzaria) nao ajuda a filtrar nada -- o restaurante ja aparece nesse
     * filtro via Cuisine, no mesmo nivel de granularidade. Isso ja aconteceu nos dados de
     * demonstracao (Pizza/Churrasco duplicados) e confundia quem via "Tipo de comida" e
     * "Prato ou ingrediente" oferecendo a mesma palavra. A tag continua valida pra QUALQUER
     * outro restaurante sem essa cozinha -- so' e' redundante pra este.
     *
     * @param  array<int, string>  $names
     */
    private function assertFoodTagsDontDuplicateCuisine(array $names, Restaurant $restaurant): void
    {
        $cuisineSlugs = $restaurant->cuisines()->pluck('slug')->all();

        foreach ($names as $name) {
            $slug = Str::slug(trim((string) $name));

            if (in_array($slug, $cuisineSlugs, true)) {
                throw ValidationException::withMessages([
                    'food_tags' => "\"{$name}\" já é o tipo de cozinha deste restaurante -- use uma tag mais específica de prato ou ingrediente.",
                ]);
            }
        }
    }

    public function destroyItem(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($request, $menuItem);

        $menuItem->delete();

        return back()->with('status', 'Item removido.');
    }

    // So' 1 item em destaque por restaurante -- selecionar um novo troca o anterior; clicar
    // no que ja esta em destaque desmarca. E' isso que aparece no lugar do icone generico no
    // card de busca (ver Discover\RestaurantCard.vue), pra diferenciar cada estabelecimento.
    public function toggleFeatured(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($request, $menuItem);

        $restaurant = $menuItem->category->menu->restaurant;
        $isCurrentlyFeatured = $restaurant->featured_menu_item_id === $menuItem->id;

        $restaurant->forceFill([
            'featured_menu_item_id' => $isCurrentlyFeatured ? null : $menuItem->id,
        ])->save();

        return back()->with(
            'status',
            $isCurrentlyFeatured ? 'Destaque removido.' : "\"{$menuItem->name}\" agora é o destaque do estabelecimento.",
        );
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
            'dietary_tags' => ['nullable', 'array'],
            'dietary_tags.*' => ['integer', 'exists:dietary_tags,id'],
            'photo' => ['nullable', 'image', 'max:4096'],
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
