<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * হোম পেজ — ফিচার্ড ক্যাটাগরি ও নতুন পণ্য (ডায়নামিক)
     */
    public function home(): View
    {
        $featuredCategories = Category::active()
            ->featured()
            ->ordered()
            ->take(6)
            ->get();

        $latestProducts = Product::active()
            ->with(['category', 'primaryImage', 'images', 'activeVariants.inventory'])
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('featuredCategories', 'latestProducts'));
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
