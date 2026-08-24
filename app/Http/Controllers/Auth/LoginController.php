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
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = Auth::getProvider()->retrieveByCredentials($request->safe()->only('email', 'password'));

        if ($user && ! $user->isActive()) {
            return back()->withErrors([
                'email' => 'This account is inactive. Please contact support.',
            ])->onlyInput('email');
        }

        Auth::attempt($request->safe()->only('email', 'password'), (bool) $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
