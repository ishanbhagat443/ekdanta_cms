<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OnboardingService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(protected OnboardingService $onboarding)
    {
    }

    public function index(): View
    {
        $steps = $this->onboarding->steps();

        return view('admin.dashboard.index', [
            'steps' => $steps,
            'remaining' => $this->onboarding->remaining(),
            'onboardingComplete' => $this->onboarding->isComplete(),
            'counts' => $this->onboarding->counts(),
        ]);
    }
}
