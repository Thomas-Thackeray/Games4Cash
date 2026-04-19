@extends('layouts.app')
@section('title', 'Security')

@section('content')
<div class="account-page">
    <div class="account-container">

        {{-- Sidebar --}}
        <aside class="account-sidebar">
            <div class="account-avatar">{{ strtoupper(substr($user->username, 0, 1)) }}</div>
            <div class="account-sidebar__name">{{ $user->first_name }} {{ $user->surname }}</div>
            <div class="account-sidebar__username">&#64;{{ $user->username }}</div>
            <nav class="account-nav">
                <a href="{{ route('profile') }}" class="account-nav__link">👤 Profile</a>
                <a href="{{ route('security') }}" class="account-nav__link active">🔒 Security</a>
            </nav>
        </aside>

        {{-- Main content --}}
        <div class="account-main">

            {{-- Change Password --}}
            <section class="account-card">
                <div class="account-card__header">
                    <h2 class="account-card__title">Change Password</h2>
                    <p class="account-card__subtitle">Choose a strong password you don't use elsewhere.</p>
                </div>

                <form method="POST" action="{{ route('security.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <div class="password-wrap">
                            <input type="password" id="current_password" name="current_password"
                                class="form-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                placeholder="Your current password"
                                autocomplete="current-password">
                            <button type="button" class="password-toggle" data-target="current_password" aria-label="Toggle">
                                <span class="eye-icon">👁</span>
                            </button>
                        </div>
                        @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password">New Password <span class="required">*</span></label>
                        <div class="password-wrap">
                            <input type="password" id="password" name="password"
                                class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                placeholder="Min. 12 characters"
                                autocomplete="new-password">
                            <button type="button" class="password-toggle" data-target="password" aria-label="Toggle">
                                <span class="eye-icon">👁</span>
                            </button>
                        </div>
                        <p class="password-hint">At least 12 characters including a number and a special character (e.g. !@#$%).</p>
                        @error('password')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password <span class="required">*</span></label>
                        <div class="password-wrap">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-input"
                                placeholder="Re-enter new password"
                                autocomplete="new-password">
                            <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Toggle">
                                <span class="eye-icon">👁</span>
                            </button>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn--primary">Update Password</button>
                    </div>
                </form>
            </section>

            {{-- Active Sessions --}}
            <section class="account-card" id="sessions">
                <div class="account-card__header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.75rem;">
                    <div>
                        <h2 class="account-card__title">Active Sessions</h2>
                        <p class="account-card__subtitle">Devices currently logged in to your account.</p>
                    </div>
                    @if($sessions->count() > 1)
                    <form method="POST" action="{{ route('security.sessions.destroy-all') }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn--outline btn--sm"
                            data-confirm="Log out of all other sessions?"
                            onclick="if(confirm('Log out of all other sessions?')) this.closest('form').submit();">
                            Log Out Everywhere Else
                        </button>
                    </form>
                    @endif
                </div>

                @if($sessions->isEmpty())
                <p style="color:var(--text-muted); font-size:0.88rem;">No active sessions found.</p>
                @else
                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-top:0.5rem;">
                    @foreach($sessions as $session)
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0.85rem 1rem; background:var(--card-bg); border:1px solid var(--border); border-radius:8px; {{ $session->is_current ? 'border-color:var(--accent);' : '' }}">
                        <div style="display:flex; align-items:center; gap:0.75rem; min-width:0;">
                            <div style="font-size:1.4rem; flex-shrink:0;">
                                @if(str_contains($session->platform, 'Android') || str_contains($session->platform, 'iOS'))💬
                                @else🖥️
                                @endif
                            </div>
                            <div style="min-width:0;">
                                <div style="font-weight:600; font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $session->browser }} on {{ $session->platform }}
                                    @if($session->is_current)
                                    <span style="font-size:0.75rem; color:var(--accent); font-weight:400; margin-left:0.4rem;">(this session)</span>
                                    @endif
                                </div>
                                <div style="font-size:0.8rem; color:var(--text-muted); margin-top:0.15rem;">
                                    {{ $session->ip_address }} &mdash; last active {{ $session->last_activity->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        @if(! $session->is_current)
                        <form method="POST" action="{{ route('security.sessions.destroy', $session->id) }}" style="flex-shrink:0;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn--outline btn--sm"
                                onclick="if(confirm('Revoke this session?')) this.closest('form').submit();"
                                style="color:var(--accent);">
                                Revoke
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </section>

            {{-- Login History --}}
            <section class="account-card" id="login-history">
                <div class="account-card__header">
                    <h2 class="account-card__title">Recent Login Activity</h2>
                    <p class="account-card__subtitle">
                        Your last {{ $attempts->total() }} login {{ $attempts->total() === 1 ? 'attempt' : 'attempts' }},
                        showing {{ $attempts->firstItem() }}–{{ $attempts->lastItem() }}.
                    </p>
                </div>

                @if($attempts->isEmpty())
                <div class="login-history-empty">No login history recorded yet.</div>
                @else
                <div class="login-history-table-wrap">
                    <table class="login-history-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Date &amp; Time</th>
                                <th>IP Address</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attempts as $attempt)
                            <tr>
                                <td>
                                    <span class="attempt-badge attempt-badge--{{ $attempt->status }}">
                                        {{ $attempt->status === 'success' ? '✓ Success' : '✕ Failed' }}
                                    </span>
                                </td>
                                <td class="attempt-time">{{ $attempt->created_at->format('d M Y, H:i') }}</td>
                                <td class="attempt-ip">{{ $attempt->ip_address }}</td>
                                <td class="attempt-location">{{ $attempt->location }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="history-pagination">
                    <span class="history-pagination__info">
                        Page {{ $attempts->currentPage() }} of {{ $attempts->lastPage() }}
                    </span>
                    <div class="history-pagination__btns">
                        @if($attempts->onFirstPage())
                            <span class="btn btn--outline btn--sm history-pagination__btn--disabled">← Previous</span>
                        @else
                            <a href="{{ $attempts->previousPageUrl() }}#login-history" class="btn btn--outline btn--sm">← Previous</a>
                        @endif
                        @if($attempts->hasMorePages())
                            <a href="{{ $attempts->nextPageUrl() }}#login-history" class="btn btn--outline btn--sm">Next →</a>
                        @else
                            <span class="btn btn--outline btn--sm history-pagination__btn--disabled">Next →</span>
                        @endif
                    </div>
                </div>
                @endif
            </section>

        </div>
    </div>
</div>

<script>
document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endsection
