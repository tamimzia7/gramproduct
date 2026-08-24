<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::validate($request->safe()->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'প্রদত্ত তথ্য আমাদের রেকর্ডের সাথে মেলে না।',
            ])->onlyInput('email');
        }

        $user = Auth::getProvider()->retrieveByCredentials($request->safe()->only('email', 'password'));

        if ($user && ! $user->isActive()) {
            return back()->withErrors([
                'email' => 'এই অ্যাকাউন্টটি নিষ্ক্রিয়। অনুগ্রহ করে সাপোর্টের সাথে যোগাযোগ করুন।',
            ])->onlyInput('email');
        }

        Auth::attempt($request->safe()->only('email', 'password'), (bool) $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
