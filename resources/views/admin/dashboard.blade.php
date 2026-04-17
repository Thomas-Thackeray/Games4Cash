@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Admin Dashboard</h1>
            <p class="admin-subtitle">Welcome back, {{ auth()->user()->first_name }}</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn--primary btn--sm">Manage Users</a>
    </div>

    {{-- Stats --}}
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-card__value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-card__label">Registered Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">{{ number_format($stats['new_this_month']) }}</div>
            <div class="stat-card__label">New This Month</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">{{ number_format($stats['active_7_days']) }}</div>
            <div class="stat-card__label">Active Last 7 Days</div>
        </div>
        <div class="stat-card {{ $stats['pending_resets'] > 0 ? 'stat-card--warning' : '' }}">
            <div class="stat-card__value">{{ number_format($stats['pending_resets']) }}</div>
            <div class="stat-card__label">Pending Password Resets</div>
        </div>
        <div class="stat-card {{ $stats['unread_contacts'] > 0 ? 'stat-card--warning' : '' }}">
            <div class="stat-card__value">{{ number_format($stats['unread_contacts']) }}</div>
            <div class="stat-card__label">Unread Contact Submissions</div>
        </div>
        <div class="stat-card {{ $stats['pending_orders'] > 0 ? 'stat-card--warning' : '' }}">
            <div class="stat-card__value">{{ number_format($stats['pending_orders']) }}</div>
            <div class="stat-card__label">Pending Cash Orders</div>
        </div>
        <div class="stat-card {{ $stats['no_price_count'] > 0 ? 'stat-card--warning' : '' }}">
            <div class="stat-card__value">{{ number_format($stats['no_price_count']) }}</div>
            <div class="stat-card__label">Games Awaiting Price</div>
        </div>
        @if($stats['views_today'] !== null)
        <div class="stat-card">
            <div class="stat-card__value" style="color:var(--accent);">{{ number_format($stats['visitors_today']) }}</div>
            <div class="stat-card__label">Unique Visitors Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">{{ number_format($stats['views_today']) }}</div>
            <div class="stat-card__label">Page Views Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value" style="color:var(--accent);">{{ number_format($stats['visitors_month']) }}</div>
            <div class="stat-card__label">Unique Visitors This Month</div>
        </div>
        @endif
    </div>

    {{-- Quick actions --}}
    <div class="admin-section">
        <h2 class="admin-section__title">Quick Actions</h2>
        <div class="admin-actions">
            <div class="admin-action-card">
                <div class="admin-action-card__icon">👥</div>
                <div class="admin-action-card__body">
                    <h3>View All Users</h3>
                    <p>Browse, search, and manage registered accounts.</p>
                </div>
                <a href="{{ route('admin.users') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">✉️</div>
                <div class="admin-action-card__body">
                    <h3>Contact Submissions</h3>
                    <p>
                        View messages sent via the Contact Us form.
                        @if($stats['unread_contacts'] > 0)
                        <span class="admin-badge admin-badge--warning" style="margin-left:0.4rem;">{{ $stats['unread_contacts'] }} unread</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.contact-submissions') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">🔒</div>
                <div class="admin-action-card__body">
                    <h3>Blacklisted Passwords</h3>
                    <p>Manage the list of passwords users are not allowed to choose.</p>
                </div>
                <a href="{{ route('admin.blacklist') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">📋</div>
                <div class="admin-action-card__body">
                    <h3>Activity Logs</h3>
                    <p>View all search, login, and filter activity across all users.</p>
                </div>
                <a href="{{ route('admin.activity-logs') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card {{ $stats['no_price_count'] > 0 ? 'admin-action-card--warning' : '' }}">
                <div class="admin-action-card__icon">🏷️</div>
                <div class="admin-action-card__body">
                    <h3>No Price Review</h3>
                    <p>
                        Games hidden from listings because no price was found.
                        @if($stats['no_price_count'] > 0)
                        <span class="admin-badge admin-badge--warning" style="margin-left:0.4rem;">{{ $stats['no_price_count'] }} awaiting</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.no-price-review') }}" class="btn btn--outline btn--sm">Review</a>
            </div>


            <div class="admin-action-card">
                <div class="admin-action-card__icon">⚙️</div>
                <div class="admin-action-card__body">
                    <h3>Site Settings</h3>
                    <p>Configure pricing discount percentage and other site-wide settings.</p>
                </div>
                <a href="{{ route('admin.settings') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">📧</div>
                <div class="admin-action-card__body">
                    <h3>Email Templates</h3>
                    <p>Edit the text content of order confirmation, welcome, and password reset emails.</p>
                </div>
                <a href="{{ route('admin.email-templates') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">❓</div>
                <div class="admin-action-card__body">
                    <h3>FAQs</h3>
                    <p>Add, edit, and remove entries on the public FAQ page.</p>
                </div>
                <a href="{{ route('admin.faqs.index') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">📝</div>
                <div class="admin-action-card__body">
                    <h3>Blog</h3>
                    <p>Write and publish blog posts — gaming news, reviews, and selling guides.</p>
                </div>
                <a href="{{ route('admin.blog.index') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">📊</div>
                <div class="admin-action-card__body">
                    <h3>Analytics</h3>
                    <p>Track unique visitors, page views, top pages, and referral sources.</p>
                </div>
                <a href="{{ route('admin.analytics') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card">
                <div class="admin-action-card__icon">💰</div>
                <div class="admin-action-card__body">
                    <h3>Cash Orders</h3>
                    <p>
                        Review and manage Get Cash quote submissions from users.
                        @if($stats['pending_orders'] > 0)
                        <span class="admin-badge admin-badge--warning" style="margin-left:0.4rem;">{{ $stats['pending_orders'] }} pending</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.orders') }}" class="btn btn--outline btn--sm">Open</a>
            </div>

            <div class="admin-action-card admin-action-card--danger">
                <div class="admin-action-card__icon">🔑</div>
                <div class="admin-action-card__body">
                    <h3>Force All Password Resets</h3>
                    <p>Require every standard user to set a new password on next login.</p>
                </div>
                <form method="POST" action="{{ route('admin.users.force-reset-all') }}">
                    @csrf
                    <button type="submit"
                        class="btn btn--danger btn--sm"
                        data-confirm="Force ALL users to reset their password on next login?">
                        Force All
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
