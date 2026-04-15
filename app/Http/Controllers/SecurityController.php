<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Rules\NotCommonPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        // Paginate over the most recent 100 attempts only, 10 per page
        $recentIds = LoginAttempt::where('user_id', $user->id)
                        ->latest('created_at')
                        ->limit(100)
                        ->pluck('id');

        $attempts = LoginAttempt::whereIn('id', $recentIds)
                        ->latest('created_at')
                        ->paginate(10);

        return view('security', compact('user', 'attempts'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(12)->numbers()->symbols(),
                new NotCommonPassword(),
            ],
        ], [
            'password.confirmed' => 'New passwords do not match.',
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('flash_success', 'Your password has been updated successfully.');
    }
}
