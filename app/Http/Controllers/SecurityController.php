<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Rules\NotCommonPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Active sessions for this user
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($s) {
                $ua = $s->user_agent ?? '';
                return (object) [
                    'id'            => $s->id,
                    'ip_address'    => $s->ip_address ?? '—',
                    'browser'       => $this->parseBrowser($ua),
                    'platform'      => $this->parsePlatform($ua),
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($s->last_activity),
                    'is_current'    => $s->id === session()->getId(),
                ];
            });

        return view('security', compact('user', 'attempts', 'sessions'));
    }

    public function destroySession(Request $request, string $sessionId): RedirectResponse
    {
        $user = auth()->user();

        // Prevent users from revoking other users' sessions
        $exists = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists && $sessionId !== session()->getId()) {
            DB::table('sessions')->where('id', $sessionId)->delete();
        }

        return back()->with('flash_success', 'Session revoked.');
    }

    public function destroyAllSessions(Request $request): RedirectResponse
    {
        $user      = auth()->user();
        $currentId = session()->getId();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentId)
            ->delete();

        return back()->with('flash_success', 'All other sessions have been logged out.');
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function parseBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg'))    return 'Edge';
        if (str_contains($ua, 'OPR') || str_contains($ua, 'Opera')) return 'Opera';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Chrome'))  return 'Chrome';
        if (str_contains($ua, 'Safari'))  return 'Safari';
        return 'Unknown browser';
    }

    private function parsePlatform(string $ua): string
    {
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac'))     return 'macOS';
        if (str_contains($ua, 'Linux'))   return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Unknown OS';
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
