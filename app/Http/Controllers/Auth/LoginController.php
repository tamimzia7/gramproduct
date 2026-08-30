<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::validate($request->safe()->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'প্রদান করা তথ্য দিয়ে কোনো অ্যাকাউন্ট খুঁজে পাওয়া যায়নি।',
            ])->onlyInput('email');
        }

        $user = Auth::getProvider()->retrieveByCredentials($request->safe()->only('email', 'password'));

        if ($user && ! $user->isActive()) {
            return back()->withErrors([
                'email' => 'এই অ্যাকাউন্টটি নিষ্ক্রিয়। অনুগ্রহ করে সহায়তার জন্য যোগাযোগ করুন।',
            ])->onlyInput('email');
        }

        // Guest cart যে session-এ তৈরি হয়েছিল — Auth::attempt নিজেই session id
        // regenerate করে (SessionGuard::updateSession), তাই আগেই ধরে রাখতে হবে
        $guestSessionId = $request->session()->getId();

        Auth::attempt($request->safe()->only('email', 'password'), (bool) $request->boolean('remember'));

        $this->cartService->mergeGuestCart($guestSessionId, $user);

        return redirect()->intended(
            $user->hasAnyRole() ? route('admin.dashboard') : route('home')
        );
    }
}
