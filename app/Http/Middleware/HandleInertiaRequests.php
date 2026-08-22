<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            // Pras paginas montarem URL absoluta (canonical, og:url, og:image) sem depender
            // de window.location -- indisponivel no processo Node do SSR.
            'appUrl' => rtrim(config('app.url'), '/'),
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }
}
