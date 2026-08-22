<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\JobSkill;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// A aba "Empregabilidade" vive dentro de Owner/Restaurants/Edit.vue (uma vaga e' sempre de
// UM restaurante especifico) -- Owner\RestaurantController::edit() carrega os dados, esse
// controller so' cuida das mutacoes (publicar/fechar).
class JobPostingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'job_skills' => ['nullable', 'array'],
            'job_skills.*' => ['string', 'max:50'],
        ]);

        $restaurant = Restaurant::findOrFail($data['restaurant_id']);
        RestaurantController::authorizeOwner($request, $restaurant);

        $posting = $restaurant->jobPostings()->create([
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        $posting->jobSkills()->sync($this->resolveJobSkillIds($data['job_skills'] ?? []));

        return back()->with('status', 'Vaga publicada.');
    }

    public function close(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        RestaurantController::authorizeOwner($request, $jobPosting->restaurant);

        $jobPosting->update(['status' => $jobPosting->isOpen() ? 'closed' : 'open']);

        return back()->with('status', $jobPosting->isOpen() ? 'Vaga reaberta.' : 'Vaga encerrada.');
    }

    /**
     * Mesma logica de Owner\MenuController::resolveFoodTagIds() / Freelancer\ProfileController.
     *
     * @param  array<int, string>  $names
     * @return array<int, int>
     */
    private function resolveJobSkillIds(array $names): array
    {
        $position = JobSkill::max('position') + 1;

        return array_map(function (string $name) use (&$position) {
            $name = trim($name);
            $skill = JobSkill::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'position' => $position]
            );

            if ($skill->wasRecentlyCreated) {
                $position++;
            }

            return $skill->id;
        }, array_filter($names, fn ($name) => trim((string) $name) !== ''));
    }
}
