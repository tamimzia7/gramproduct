<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(): View
    {
        return view('auth.verify-email');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('home')->with('status', 'আপনার ইমেইল সফলভাবে যাচাই হয়েছে।');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'আপনার ইমেইলে একটি যাচাইকরণ লিংক পাঠানো হয়েছে।');
    }
}
