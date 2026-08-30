<?php

namespace App\Http\Controllers;

use App\Services\HomepageService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private HomepageService $homepageService,
    ) {}

    /**
     * হোমপেজ — সম্পূর্ণ ডায়নামিক সেকশনসহ
     */
    public function home(): View
    {
        return view('home', [
            'quickCategories' => $this->homepageService->quickCategories(),
            'featuredProducts' => $this->homepageService->featuredProducts(),
            'riceShowcase' => $this->homepageService->riceShowcase(),
            'fishShowcase' => $this->homepageService->fishShowcase(),
            'sections' => $this->homepageService->sections(),
        ]);
    }

    public function dashboard(): View
    {
        return view('dashboard');
    }

    public function adminDashboard(): View
    {
        return view('admin.dashboard');
    }
}
