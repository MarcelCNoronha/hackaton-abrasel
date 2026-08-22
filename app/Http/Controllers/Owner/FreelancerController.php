<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Models\HireRequest;
use App\Models\JobSkill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FreelancerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = FreelancerProfile::query()
            ->with(['user:id,name,phone', 'jobSkills'])
            ->whereNotNull('id');

        if ($skills = array_filter((array) $request->input('job_skills', []))) {
            $query->whereHas('jobSkills', fn ($s) => $s->whereIn('slug', $skills));
        }

        if ($availability = $request->input('availability_status')) {
            $query->where('availability_status', $availability);
        }

        $profiles = $query->orderByDesc('average_rating')->orderByDesc('reviews_count')->limit(60)->get();

        return Inertia::render('Owner/Freelancers/Index', [
            'freelancers' => $profiles,
            'jobSkills' => JobSkill::orderBy('position')->get(['id', 'name', 'slug']),
            'filters' => [
                'job_skills' => array_values($skills ?? []),
                'availability_status' => $availability,
            ],
        ]);
    }

    public function show(Request $request, FreelancerProfile $freelancerProfile): Response
    {
        $freelancerProfile->load([
            'user:id,name,phone',
            'jobSkills',
            'availabilitySlots',
            'reviews' => fn ($q) => $q->latest()->with('restaurant:id,name'),
        ]);

        // feedback_to_freelancer e' privado -- so' o proprio freelancer ve (Freelancer\HireController).
        // Donos so' podem ver feedback_to_owners, entao esconde explicitamente aqui antes de
        // serializar pro Inertia, mesmo o modelo nao tendo esse campo oculto por padrao (o
        // freelancer PRECISA ve-lo na propria tela).
        $freelancerProfile->reviews->each->makeHidden('feedback_to_freelancer');

        $myRestaurantIds = $request->user()->ownedRestaurants()->pluck('restaurants.id');

        $myHireRequests = HireRequest::whereIn('restaurant_id', $myRestaurantIds)
            ->where('freelancer_profile_id', $freelancerProfile->id)
            ->with(['restaurant:id,name', 'review'])
            ->latest()
            ->get();

        return Inertia::render('Owner/Freelancers/Show', [
            'freelancer' => $freelancerProfile,
            'myRestaurants' => $request->user()->ownedRestaurants()->get(['restaurants.id', 'restaurants.name']),
            'myHireRequests' => $myHireRequests,
        ]);
    }
}
