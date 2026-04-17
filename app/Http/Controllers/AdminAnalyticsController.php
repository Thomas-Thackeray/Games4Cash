<?php

namespace App\Http\Controllers;

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

        // Top pages (last 30 days)
        $topPages = PageView::select('path', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT session_id) as visitors'))
            ->where('created_at', '>=', $now->copy()->subDays(29)->startOfDay())
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

        return view('admin.analytics', compact('hasTable', 'summary', 'daily', 'topPages', 'topReferrers'));
    }
}
