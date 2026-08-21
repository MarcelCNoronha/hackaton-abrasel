<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $menuCategory->items()->create($data + [
            'position' => $menuCategory->items()->count(),
        ]);

        return back()->with('status', 'Item adicionado ao cardápio.');
    }

    public function updateItem(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeItem($request, $menuItem);

        $menuItem->update($this->validateItem($request));

        return back()->with('status', 'Item atualizado.');
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
            'is_available' => ['required', 'boolean'],
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
