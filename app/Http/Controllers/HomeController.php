<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function dashboard(Request $request): View
    {
        return view('dashboard');
    }

    public function adminDashboard(Request $request): View
    {
        return view('admin.dashboard');
    }
}
