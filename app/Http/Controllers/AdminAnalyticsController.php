<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\PageView;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function index(): View
    {
        $hasTable = \Illuminate\Support\Facades\Schema::hasTable('page_views');

        if (! $hasTable) {
            return view('admin.analytics', ['hasTable' => false]);
        }

        $now       = now();
        $todayStart     = $now->copy()->startOfDay();
        $weekStart      = $now->copy()->startOfWeek();
        $monthStart     = $now->copy()->startOfMonth();

        // Summary counts
        $summary = [
            'today' => [
                'views'    => PageView::where('created_at', '>=', $todayStart)->count(),
                'visitors' => PageView::where('created_at', '>=', $todayStart)->distinct('session_id')->count('session_id'),
            ],
            'week' => [
                'views'    => PageView::where('created_at', '>=', $weekStart)->count(),
                'visitors' => PageView::where('created_at', '>=', $weekStart)->distinct('session_id')->count('session_id'),
            ],
            'month' => [
                'views'    => PageView::where('created_at', '>=', $monthStart)->count(),
                'visitors' => PageView::where('created_at', '>=', $monthStart)->distinct('session_id')->count('session_id'),
            ],
            'all_time' => [
                'views'    => PageView::count(),
                'visitors' => PageView::distinct('session_id')->count('session_id'),
            ],
        ];

        // Daily views + unique visitors for last 30 days
        $dailyRaw = PageView::select(
                DB::raw("strftime('%Y-%m-%d', created_at) as date"),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT session_id) as visitors')
            )
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill in missing days with zeros
        $daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $daily[] = [
                'date'     => $date,
                'label'    => $now->copy()->subDays($i)->format('d M'),
                'views'    => $dailyRaw[$date]->views ?? 0,
                'visitors' => $dailyRaw[$date]->visitors ?? 0,
            ];
        }

        // Top pages (last 30 days) — exclude image proxy paths
        $topPages = PageView::select('path', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT session_id) as visitors'))
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->where('path', 'not like', '/img/%')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(15)
            ->get();

        // Top referrers (last 30 days, external only)
        $topReferrers = PageView::select('referrer', DB::raw('COUNT(*) as visits'), DB::raw('COUNT(DISTINCT session_id) as visitors'))
            ->whereNotNull('referrer')
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->groupBy('referrer')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        // Most-searched game titles (last 30 days, from activity_logs)
        $topSearches = DB::table('activity_logs')
            ->select('description', DB::raw('COUNT(*) as count'))
            ->where('type', 'search')
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
            ->groupBy('description')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'term'  => preg_replace('/^Searched for "(.*)"$/', '$1', $r->description),
                'count' => $r->count,
            ]);

        // Most-wishlisted games (all time)
        $topWishlisted = DB::table('wishlists')
            ->select('game_title', DB::raw('COUNT(*) as count'))
            ->whereNotNull('game_title')
            ->groupBy('game_title')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Basket abandonment stats
        $activeBasketUsers = DB::table('cash_basket_items')
            ->distinct('user_id')
            ->count('user_id');

        $abandonedUsers = DB::table('cash_basket_items')
            ->whereNotIn('user_id', function ($q) {
                $q->select('user_id')->from('cash_orders');
            })
            ->distinct('user_id')
            ->count('user_id');

        $basketStats = [
            'active_basket_users' => $activeBasketUsers,
            'abandoned_users'     => $abandonedUsers,
            'total_items'         => DB::table('cash_basket_items')->count(),
        ];

        // Newsletter subscriber count
        $newsletterCount = \Illuminate\Support\Facades\Schema::hasTable('newsletter_subscribers')
            ? NewsletterSubscriber::activeCount()
            : 0;

        return view('admin.analytics', compact(
            'hasTable', 'summary', 'daily', 'topPages', 'topReferrers',
            'topSearches', 'topWishlisted', 'basketStats', 'newsletterCount'
        ));
    }
}
