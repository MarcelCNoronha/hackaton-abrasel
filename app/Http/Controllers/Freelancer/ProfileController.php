<?php

namespace App\Http\Controllers\Freelancer;

use App\Enums\AvailabilityStatus;
use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Models\JobSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);

        $data = $request->validate([
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            // Opcional de proposito -- nem todo freelancer vai ter uma foto na hora de montar
            // o perfil, e isso nao pode bloquear o resto do cadastro.
            'photo' => ['nullable', 'image', 'max:4096'],
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

        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('freelancers', 'public');
        }
        unset($data['photo']);

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
