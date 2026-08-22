<?php

namespace App\Http\Controllers\Freelancer;

use App\Enums\AvailabilityStatus;
use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Models\JobSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $profile = $this->profileFor($request);
        $profile->load(['jobSkills', 'availabilitySlots']);

        return Inertia::render('Freelancer/Profile/Edit', [
            'profile' => $profile,
            'availabilityStatuses' => array_column(AvailabilityStatus::cases(), 'value'),
            'jobSkillSuggestions' => JobSkill::orderBy('position')->pluck('name'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $data = $request->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'availability_status' => ['required', new Enum(AvailabilityStatus::class)],
            'job_skills' => ['nullable', 'array'],
            'job_skills.*' => ['string', 'max:50'],
            'slots' => ['required', 'array', 'size:7'],
            'slots.*.weekday' => ['required', 'integer', 'min:0', 'max:6'],
            'slots.*.is_off' => ['required', 'boolean'],
            'slots.*.opens_at' => ['nullable', 'date_format:H:i'],
            'slots.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        $jobSkillNames = $data['job_skills'] ?? [];
        $slots = $data['slots'];
        unset($data['job_skills'], $data['slots']);

        $profile->update($data);
        $profile->jobSkills()->sync($this->resolveJobSkillIds($jobSkillNames));

        foreach ($slots as $slot) {
            $profile->availabilitySlots()->updateOrCreate(
                ['weekday' => $slot['weekday']],
                [
                    'is_off' => $slot['is_off'],
                    'opens_at' => $slot['is_off'] ? null : $slot['opens_at'],
                    'closes_at' => $slot['is_off'] ? null : $slot['closes_at'],
                ],
            );
        }

        return back()->with('status', 'Perfil de freelancer atualizado.');
    }

    private function profileFor(Request $request): FreelancerProfile
    {
        return $request->user()->freelancerProfile()->firstOrCreate([]);
    }

    /**
     * Mesma logica de Owner\MenuController::resolveFoodTagIds() -- tags curadas + criacao
     * livre por slug.
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
