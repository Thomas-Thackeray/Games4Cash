<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    // ── Setup (GET) ──────────────────────────────────────────────────────────

    public function setup(): View
    {
        $user   = auth()->user();
        $g2fa   = new Google2FA();

        // Generate a fresh secret each visit (stored in session until confirmed)
        if (! session()->has('2fa_setup_secret')) {
            session(['2fa_setup_secret' => $g2fa->generateSecretKey()]);
        }

        $secret = session('2fa_setup_secret');

        $otpauthUrl = $g2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $qrSvg = $this->generateQrSvg($otpauthUrl);

        return view('profile.two-factor-setup', compact('secret', 'qrSvg'));
    }

    // ── Enable (POST — confirm setup with a valid code) ─────────────────────

    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.digits' => 'The code must be exactly 6 digits.',
        ]);

        $secret = session('2fa_setup_secret');
        if (! $secret) {
            return back()->withErrors(['code' => 'Setup session expired. Please start again.']);
        }

        $g2fa = new Google2FA();
        if (! $g2fa->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'The code was incorrect. Please try again.']);
        }

        $user = auth()->user();
        $user->update([
            'two_factor_secret'       => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        session()->forget('2fa_setup_secret');
        // Mark session as already verified (no challenge on current session)
        session(['2fa_verified' => true]);

        return redirect()->route('profile')
            ->with('flash_success', 'Two-factor authentication has been enabled.');
    }

    // ── Disable (POST) ───────────────────────────────────────────────────────

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['disable_password' => 'Password is incorrect.'])->withFragment('2fa');
        }

        $user->update([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ]);

        session()->forget('2fa_verified');

        return redirect()->route('profile')
            ->with('flash_success', 'Two-factor authentication has been disabled.');
    }

    // ── Challenge (GET) — shown after login when 2FA is required ────────────

    public function challenge(): View|RedirectResponse
    {
        if (! session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    // ── Verify challenge (POST) ──────────────────────────────────────────────

    public function verify(Request $request): RedirectResponse
    {
        $userId = session('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.digits' => 'The code must be exactly 6 digits.',
        ]);

        $user = \App\Models\User::find($userId);
        if (! $user || ! $user->two_factor_secret) {
            return redirect()->route('login');
        }

        $g2fa = new Google2FA();
        if (! $g2fa->verifyKey($user->two_factor_secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        // Authenticate the user
        $remember = session()->pull('2fa_remember', false);
        session()->forget('2fa_user_id');

        \Illuminate\Support\Facades\Auth::login($user, $remember);
        $request->session()->regenerate();
        session(['2fa_verified' => true]);

        if ($user->force_password_reset) {
            return redirect()->route('password.force-reset');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function generateQrSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        return $writer->writeString($url);
    }
}
