<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewUserMail;
use App\Mail\WelcomeEmail;
use App\Models\LoginAttempt;
use App\Models\Setting;
use App\Models\User;
use App\Rules\NotCommonPassword;
use App\Services\ActivityLogger;
use App\Services\GeoLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    // ----------------------------------------------------------------
    //  Registration
    // ----------------------------------------------------------------

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name'            => ['required', 'string', 'max:100'],
            'surname'               => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number'        => ['required', 'string', 'regex:/^[\+\d\s\-\(\)]{7,20}$/'],
            'username'              => ['required', 'string', 'alpha_dash', 'min:12', 'max:30', 'unique:users,username', 'regex:/[0-9]/'],
            'password'              => [
                'required',
                'confirmed',
                Password::min(12)->numbers()->symbols(),
                new NotCommonPassword(),
            ],
        ], [
            'contact_number.regex'   => 'Please enter a valid contact number (7–20 digits).',
            'username.alpha_dash'    => 'Username may only contain letters, numbers, dashes, and underscores.',
            'username.min'           => 'Username must be at least 12 characters.',
            'username.regex'         => 'Username must contain at least one number.',
            'username.unique'        => 'That username is already taken.',
            'email.unique'           => 'An account with that email already exists.',
            'password.confirmed'     => 'Passwords do not match.',
        ]);

        $user = User::create([
            'first_name'     => $request->first_name,
            'surname'        => $request->surname,
            'name'           => $request->first_name . ' ' . $request->surname,
            'username'       => $request->username,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'password'       => Hash::make($request->password),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        Mail::to($user->email)->send(new WelcomeEmail($user));

        $adminEmail = Setting::get('admin_notification_email', 'thomasthackeray0@gmail.com');
        try {
            Mail::to($adminEmail)->send(new AdminNewUserMail($user));
        } catch (\Throwable) {
            // Non-critical — don't fail registration if admin email fails
        }

        return redirect()->route('home')
            ->with('flash_success', 'Welcome to ' . config('app.name') . ', ' . $user->first_name . '! Your account has been created and a confirmation email is on its way.');
    }

    // ----------------------------------------------------------------
    //  Login
    // ----------------------------------------------------------------

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user     = User::where('username', $request->username)
                        ->where('email', $request->email)
                        ->first();
        $location = GeoLocationService::lookup($request->ip());

        if (! $user || ! Hash::check($request->password, $user->password)) {
            if ($user) {
                LoginAttempt::create([
                    'user_id'    => $user->id,
                    'ip_address' => $request->ip(),
                    'location'   => $location,
                    'status'     => 'failed',
                ]);
                ActivityLogger::login('Failed login attempt for user "' . $user->username . '"', $request);
            } else {
                ActivityLogger::login('Failed login attempt (unrecognised credentials)', $request);
            }

            return back()
                ->withErrors(['login' => 'The provided credentials do not match our records.'])
                ->onlyInput('username', 'email');
        }

        LoginAttempt::create([
            'user_id'    => $user->id,
            'ip_address' => $request->ip(),
            'location'   => $location,
            'status'     => 'success',
        ]);
        ActivityLogger::login('Successful login for user "' . $user->username . '"', $request);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->force_password_reset) {
            return redirect()->route('password.force-reset');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }

    // ----------------------------------------------------------------
    //  Logout
    // ----------------------------------------------------------------

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
