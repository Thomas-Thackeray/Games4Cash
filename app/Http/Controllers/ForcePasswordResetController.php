<?php

namespace App\Http\Controllers;

use App\Rules\NotCommonPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForcePasswordResetController extends Controller
{
    public function show(): View
    {
        return view('auth.force-reset');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->numbers()->symbols(),
                new NotCommonPassword(),
            ],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        auth()->user()->update([
            'password'             => Hash::make($request->input('password')),
            'force_password_reset' => false,
        ]);

        return redirect()->route('home')
            ->with('flash_success', 'Your password has been updated. Welcome back!');
    }
}
