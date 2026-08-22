<?php

namespace App\Http\Controllers\Freelancer;

use App\Enums\AvailabilityStatus;
use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Models\JobPosting;
use App\Models\JobSkill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Perfil, vagas disponiveis e pedidos de contratacao do freelancer moram numa unica pagina
 * com abas (Freelancer/Workspace.vue) -- os 3 nomes de rota antigos (profile.edit/jobs.index/
 * hires.index) continuam existindo e todos caem aqui, so mudando qual aba abre por padrao,
 * pra nao ter que atualizar todo link/redirect que ja aponta pra eles.
 */
class WorkspaceController extends Controller
{
    public function profile(Request $request): Response
    {
        return $this->render($request, 'profile');
    }

    public function jobs(Request $request): Response
    {
        return $this->render($request, 'jobs');
    }

    public function hires(Request $request): Response
    {
        return $this->render($request, 'hires');
    }

    private function render(Request $request, string $tab): Response
    {
        $profile = $this->profileFor($request);
        $profile->load(['jobSkills', 'availabilitySlots']);

        $jobPostingsQuery = JobPosting::query()
            ->where('status', 'open')
            ->with(['restaurant:id,name,slug,address_neighborhood', 'jobSkills']);

        if ($skills = array_filter((array) $request->input('job_skills', []))) {
            $jobPostingsQuery->whereHas('jobSkills', fn ($s) => $s->whereIn('slug', $skills));
        }

        $jobPostings = $jobPostingsQuery->latest()->limit(60)->get();

        $myApplicationByPosting = $profile->jobApplications()
            ->whereIn('job_posting_id', $jobPostings->pluck('id'))
            ->get()
            ->keyBy('job_posting_id');

        $jobPostings->each(function (JobPosting $posting) use ($myApplicationByPosting) {
            $application = $myApplicationByPosting->get($posting->id);
            $posting->my_application_status = $application?->status;
            $posting->my_application_id = $application?->id;
        });

        $hireRequests = $profile->hireRequests()
            ->with(['restaurant:id,name,slug', 'review'])
            ->latest()
            ->get();

        // feedback_to_owners e' pra outros donos lerem como referencia, nao pro proprio
        // freelancer -- ver Owner\FreelancerController::show(), que faz o oposto (esconde
        // feedback_to_freelancer). Os dois nunca aparecem juntos pro mesmo publico.
        $hireRequests->pluck('review')->filter()->each->makeHidden('feedback_to_owners');

        return Inertia::render('Freelancer/Workspace', [
            'initialTab' => $tab,
            'profile' => $profile,
            'availabilityStatuses' => array_column(AvailabilityStatus::cases(), 'value'),
            'jobSkillSuggestions' => JobSkill::orderBy('position')->pluck('name'),
            'jobPostings' => $jobPostings,
            'jobSkills' => JobSkill::orderBy('position')->get(['id', 'name', 'slug']),
            'filters' => ['job_skills' => array_values($skills ?? [])],
            'hireRequests' => $hireRequests,
        ]);
    }

    private function profileFor(Request $request): FreelancerProfile
    {
        return $request->user()->freelancerProfile()->firstOrCreate([]);
    }
}
