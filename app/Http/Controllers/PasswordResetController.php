<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use App\Rules\NotCommonPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    // -----------------------------------------------------------------------
    //  Step 1: Show "forgot password" form
    // -----------------------------------------------------------------------

    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    // -----------------------------------------------------------------------
    //  Step 2: Send reset link email
    // -----------------------------------------------------------------------

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->input('email'))
                    ->where('role', 'user')
                    ->first();

        // Always show the same success message whether the email exists or not
        // to prevent user enumeration
        if ($user) {
            $token = Password::broker()->createToken($user);
            Mail::to($user->email)->send(new PasswordResetMail($user, $token));
        }

        return back()->with('status', 'If an account with that email address exists, a password reset link has been sent.');
    }

    // -----------------------------------------------------------------------
    //  Step 3: Show reset form (linked from email)
    // -----------------------------------------------------------------------

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    // -----------------------------------------------------------------------
    //  Step 4: Apply new password
    // -----------------------------------------------------------------------

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(12)->numbers()->symbols(),
                new NotCommonPassword(),
            ],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'             => Hash::make($password),
                    'force_password_reset' => false,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('flash_success', 'Your password has been reset. Please sign in.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
