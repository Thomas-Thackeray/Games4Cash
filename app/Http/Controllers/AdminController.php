<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BlacklistedPassword;
use App\Models\FranchiseAdjustment;
use App\Models\CashBasketItem;
use App\Models\CashOrder;
use App\Models\ContactSubmission;
use App\Models\GamePrice;
use App\Models\LoginAttempt;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    // ----------------------------------------------------------------
    //  Dashboard
    // ----------------------------------------------------------------

    public function dashboard(): View
    {
        $stats = [
            'total_users'       => User::where('role', 'user')->count(),
            'new_this_month'    => User::where('role', 'user')
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count(),
            'active_7_days'     => User::where('role', 'user')
                                        ->where('last_active_at', '>=', now()->subDays(7))
                                        ->count(),
            'pending_resets'    => User::where('role', 'user')
                                        ->where('force_password_reset', true)
                                        ->count(),
            'unread_contacts'   => ContactSubmission::whereNull('read_at')->count(),
            'pending_orders'    => CashOrder::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // ----------------------------------------------------------------
    //  Users list
    // ----------------------------------------------------------------

    public function users(Request $request): View
    {
        $query = User::where('role', 'user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users', compact('users', 'search'));
    }

    // ----------------------------------------------------------------
    //  User detail (login attempts)
    // ----------------------------------------------------------------

    public function userDetail(int $id): View
    {
        $subject       = User::where('role', 'user')->findOrFail($id);
        $attempts      = LoginAttempt::where('user_id', $id)
                            ->latest('created_at')
                            ->paginate(10);
        $wishlistItems = Wishlist::where('user_id', $id)
                            ->latest('created_at')
                            ->get();

        // Current cash basket with computed prices
        $allPlatforms   = config('igdb.all_platforms');
        $rawBasketItems = CashBasketItem::where('user_id', $id)->latest('created_at')->get();
        $basketTotal    = 0.0;
        $basketItems    = $rawBasketItems->map(function ($item) use (&$basketTotal, $allPlatforms) {
            $pricing   = null;
            $gamePrice = GamePrice::where('igdb_game_id', $item->igdb_game_id)->first();
            if ($gamePrice) {
                try {
                    $pricing = $item->platform_id
                        ? $gamePrice->getComputedPriceForPlatform((int) $item->platform_id)
                        : $gamePrice->getComputedPrice();
                } catch (\Throwable) {}
            }
            $displayPrice = null;
            if ($pricing && ! $pricing['is_free']) {
                $displayPrice  = $pricing['display_price'];
                $basketTotal  += $pricing['price_numeric'] ?? 0.0;
            }
            return [
                'game_title'    => $item->game_title,
                'cover_url'     => $item->cover_url,
                'igdb_game_id'  => $item->igdb_game_id,
                'platform_name' => $item->platform_id ? ($allPlatforms[$item->platform_id] ?? null) : null,
                'display_price' => $displayPrice,
            ];
        });
        $basketTotal = '£' . number_format($basketTotal, 2);

        // Submitted quotes
        $cashOrders = CashOrder::where('user_id', $id)->latest()->get();

        return view('admin.user-detail', compact(
            'subject', 'attempts', 'wishlistItems', 'basketItems', 'basketTotal', 'cashOrders'
        ));
    }

    // ----------------------------------------------------------------
    //  Force password reset – individual
    // ----------------------------------------------------------------

    public function forceReset(int $id): RedirectResponse
    {
        User::where('role', 'user')->findOrFail($id)
            ->update(['force_password_reset' => true]);

        return back()->with('flash_success', 'Password reset flagged for that user.');
    }

    // ----------------------------------------------------------------
    //  Force password reset – all users
    // ----------------------------------------------------------------

    public function forceResetAll(): RedirectResponse
    {
        User::where('role', 'user')->update(['force_password_reset' => true]);

        return back()->with('flash_success', 'All standard users will be required to reset their password on next login.');
    }

    // ----------------------------------------------------------------
    //  Delete user
    // ----------------------------------------------------------------

    public function deleteUser(int $id): RedirectResponse
    {
        User::where('role', 'user')->findOrFail($id)->delete();

        return back()->with('flash_success', 'User account deleted.');
    }

    // ----------------------------------------------------------------
    //  Blacklisted passwords – list
    // ----------------------------------------------------------------

    public function showBlacklist(Request $request): View
    {
        $query = BlacklistedPassword::query();

        if ($search = $request->input('search')) {
            $query->where('password', 'like', "%{$search}%");
        }

        $passwords = $query->orderBy('password')->paginate(20)->withQueryString();

        return view('admin.blacklist', compact('passwords', 'search'));
    }

    // ----------------------------------------------------------------
    //  Blacklisted passwords – add
    // ----------------------------------------------------------------

    public function addToBlacklist(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ]);

        $pw = strtolower(trim($request->input('password')));

        if (BlacklistedPassword::where('password', $pw)->exists()) {
            return back()->with('flash_error', '"' . $pw . '" is already on the blacklist.');
        }

        BlacklistedPassword::create(['password' => $pw, 'created_at' => now()]);

        return back()->with('flash_success', '"' . $pw . '" added to the blacklist.');
    }

    // ----------------------------------------------------------------
    //  Blacklisted passwords – remove
    // ----------------------------------------------------------------

    public function removeFromBlacklist(int $id): RedirectResponse
    {
        BlacklistedPassword::findOrFail($id)->delete();

        return back()->with('flash_success', 'Password removed from the blacklist.');
    }

    // ----------------------------------------------------------------
    //  Contact submissions – list
    // ----------------------------------------------------------------

    public function contactSubmissions(Request $request): View
    {
        $filter = $request->input('filter', 'all');

        $query = ContactSubmission::latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $submissions = $query->paginate(15)->withQueryString();

        return view('admin.contact-submissions', compact('submissions', 'filter'));
    }

    // ----------------------------------------------------------------
    //  Contact submissions – view single (marks as read)
    // ----------------------------------------------------------------

    public function viewSubmission(int $id): View
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->markRead();

        return view('admin.contact-submission-detail', compact('submission'));
    }

    // ----------------------------------------------------------------
    //  Contact submissions – delete
    // ----------------------------------------------------------------

    public function deleteSubmission(int $id): RedirectResponse
    {
        ContactSubmission::findOrFail($id)->delete();

        return redirect()->route('admin.contact-submissions')
                         ->with('flash_success', 'Submission deleted.');
    }

    // ----------------------------------------------------------------
    //  Activity logs – list
    // ----------------------------------------------------------------

    public function activityLogs(Request $request): View
    {
        $type   = $request->input('type', 'all');
        $search = $request->input('search', '');

        $query = ActivityLog::with('user')->latest('created_at');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.activity-logs', compact('logs', 'type', 'search'));
    }

    // ----------------------------------------------------------------
    //  Activity logs – delete single
    // ----------------------------------------------------------------

    public function deleteActivityLog(int $id): RedirectResponse
    {
        ActivityLog::findOrFail($id)->delete();

        return back()->with('flash_success', 'Log entry deleted.');
    }

    // ----------------------------------------------------------------
    //  Activity logs – clear all or by type
    // ----------------------------------------------------------------

    public function clearActivityLogs(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'all');

        if ($type !== 'all') {
            ActivityLog::where('type', $type)->delete();
            return back()->with('flash_success', ucfirst($type) . ' logs cleared.');
        }

        ActivityLog::truncate();

        return back()->with('flash_success', 'All activity logs cleared.');
    }

    // ----------------------------------------------------------------
    //  Email templates
    // ----------------------------------------------------------------

    private const EMAIL_TEMPLATE_DEFAULTS = [
        'email_order_intro'          => "Your cash quote has been received and we're reviewing it now.\nA member of our team will be in touch shortly with further information about your collection and payment.",
        'email_order_packaging_note' => "Please ensure your games are ready and packaged securely before the collection date. All prices are estimates and may be adjusted upon physical inspection.",
        'email_welcome_intro'        => "Thank you for creating an account on {site_name}. Your account is all set — you can now explore thousands of games, browse by platform and genre, and discover your next favourite title.",
        'email_welcome_footer_note'  => "If you did not create this account, you can safely ignore this email — no action is required.",
        'email_reset_intro'          => "Hi {first_name}, we received a request to reset the password for your {site_name} account. Click the button below to choose a new password. This link will expire in 60 minutes.",
        'email_reset_footer_note'    => "If you did not request a password reset, no action is required — your password will remain unchanged.",
    ];

    public function showEmailTemplates(): View
    {
        $templates = [];
        foreach (self::EMAIL_TEMPLATE_DEFAULTS as $key => $default) {
            $templates[$key] = Setting::get($key, $default);
        }
        return view('admin.email-templates', compact('templates'));
    }

    public function updateEmailTemplates(Request $request): RedirectResponse
    {
        $request->validate([
            'email_order_intro'          => ['required', 'string', 'max:2000'],
            'email_order_packaging_note' => ['required', 'string', 'max:2000'],
            'email_welcome_intro'        => ['required', 'string', 'max:2000'],
            'email_welcome_footer_note'  => ['required', 'string', 'max:2000'],
            'email_reset_intro'          => ['required', 'string', 'max:2000'],
            'email_reset_footer_note'    => ['required', 'string', 'max:2000'],
        ]);

        foreach (array_keys(self::EMAIL_TEMPLATE_DEFAULTS) as $key) {
            Setting::set($key, $request->input($key));
        }

        return back()->with('flash_success', 'Email templates saved.');
    }

    // ----------------------------------------------------------------
    //  Site settings
    // ----------------------------------------------------------------

    // Platforms shown in admin with their IGDB IDs
    private const PLATFORMS = [
        ['id' => 167, 'name' => 'PlayStation 5'],
        ['id' => 48,  'name' => 'PlayStation 4'],
        ['id' => 9,   'name' => 'PlayStation 3'],
        ['id' => 8,   'name' => 'PlayStation 2'],
        ['id' => 169, 'name' => 'Xbox Series X|S'],
        ['id' => 49,  'name' => 'Xbox One'],
        ['id' => 12,  'name' => 'Xbox 360'],
        ['id' => 11,  'name' => 'Xbox'],
        ['id' => 130, 'name' => 'Nintendo Switch'],
        ['id' => 41,  'name' => 'Wii U'],
        ['id' => 5,   'name' => 'Wii'],
        ['id' => 6,   'name' => 'PC'],
    ];

    public function showSettings(): View
    {
        $settings = [
            'pricing_discount_percent' => Setting::get('pricing_discount_percent', 85),
            'usd_to_gbp_rate'          => Setting::get('usd_to_gbp_rate', 1.36),
            'age_reduction_per_year'   => Setting::get('age_reduction_per_year', 1),
            'base_price_gbp'           => Setting::get('base_price_gbp', 0),
            'min_order_gbp'            => Setting::get('min_order_gbp', 20),
            'condition_new_pct'          => Setting::get('condition_new_pct', 20),
            'condition_complete_pct'     => Setting::get('condition_complete_pct', 0),
            'condition_disk_pct'         => Setting::get('condition_disk_pct', -50),
            'low_price_boost_gbp'        => Setting::get('low_price_boost_gbp', 0.20),
            'high_price_reduction_pct'   => Setting::get('high_price_reduction_pct', 0),
            'bundle_price_increase_gbp'  => Setting::get('bundle_price_increase_gbp', 0),
        ];

        $platforms = array_map(function ($p) {
            return array_merge($p, [
                'modifier'      => (float) Setting::get("platform_modifier_{$p['id']}", 0),
                'modifier_type' => Setting::get("platform_modifier_type_{$p['id']}", 'percent'),
            ]);
        }, self::PLATFORMS);

        $franchiseAdjustments = FranchiseAdjustment::orderBy('franchise_name')->get();

        return view('admin.settings', compact('settings', 'platforms', 'franchiseAdjustments'));
    }

    // ----------------------------------------------------------------
    //  Cash orders
    // ----------------------------------------------------------------

    public function cashOrders(Request $request): View
    {
        $status = $request->input('status', 'all');

        $query = CashOrder::with('user')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders', compact('orders', 'status'));
    }

    public function cashOrderDetail(int $id): View
    {
        $order = CashOrder::with('user')->findOrFail($id);

        return view('admin.order-detail', compact('order'));
    }

    public function updateOrderStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status'      => ['required', 'in:pending,contacted,completed,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = CashOrder::findOrFail($id);
        $order->update([
            'status'      => $request->input('status'),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return back()->with('flash_success', 'Order ' . $order->order_ref . ' updated.');
    }

    // ----------------------------------------------------------------

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'pricing_discount_percent'  => ['required', 'numeric', 'min:0', 'max:99'],
            'usd_to_gbp_rate'           => ['required', 'numeric', 'min:0.01', 'max:99.99'],
            'age_reduction_per_year'    => ['required', 'numeric', 'min:0', 'max:9.99'],
            'base_price_gbp'            => ['required', 'numeric', 'min:0', 'max:999.99'],
            'min_order_gbp'             => ['required', 'numeric', 'min:0', 'max:999.99'],
            'condition_new_pct'         => ['required', 'numeric', 'min:-100', 'max:100'],
            'condition_complete_pct'    => ['required', 'numeric', 'min:-100', 'max:100'],
            'condition_disk_pct'        => ['required', 'numeric', 'min:-100', 'max:100'],
            'low_price_boost_gbp'       => ['required', 'numeric', 'min:0', 'max:99.99'],
            'high_price_reduction_pct'  => ['required', 'numeric', 'min:0', 'max:99'],
            'bundle_price_increase_gbp' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'platform_modifier.*'       => ['nullable', 'numeric', 'min:-999.99', 'max:999.99'],
            'platform_modifier_type.*'  => ['nullable', 'in:percent,gbp'],
        ], [
            'pricing_discount_percent.min' => 'Discount must be between 0% and 99%.',
            'pricing_discount_percent.max' => 'Discount must be between 0% and 99%.',
            'usd_to_gbp_rate.min'          => 'Exchange rate must be a positive number.',
            'age_reduction_per_year.max'   => 'Age reduction cannot exceed £9.99 per year.',
            'base_price_gbp.max'           => 'Base price cannot exceed £999.99.',
        ]);

        Setting::set('pricing_discount_percent', $request->input('pricing_discount_percent'));
        Setting::set('usd_to_gbp_rate', $request->input('usd_to_gbp_rate'));
        Setting::set('age_reduction_per_year', $request->input('age_reduction_per_year'));
        Setting::set('base_price_gbp', $request->input('base_price_gbp'));
        Setting::set('min_order_gbp', $request->input('min_order_gbp'));
        Setting::set('condition_new_pct', $request->input('condition_new_pct'));
        Setting::set('condition_complete_pct', $request->input('condition_complete_pct'));
        Setting::set('condition_disk_pct', $request->input('condition_disk_pct'));
        Setting::set('low_price_boost_gbp', $request->input('low_price_boost_gbp'));
        Setting::set('high_price_reduction_pct', $request->input('high_price_reduction_pct'));
        Setting::set('bundle_price_increase_gbp', $request->input('bundle_price_increase_gbp'));

        $modifierTypes = $request->input('platform_modifier_type', []);
        foreach ($request->input('platform_modifier', []) as $platformId => $modifier) {
            Setting::set("platform_modifier_{$platformId}", (float) ($modifier ?? 0));
            $type = $modifierTypes[$platformId] ?? 'percent';
            Setting::set("platform_modifier_type_{$platformId}", in_array($type, ['percent', 'gbp']) ? $type : 'percent');
        }

        return back()->with('flash_success', 'Settings saved.');
    }

    // ----------------------------------------------------------------
    //  Franchise price adjustments
    // ----------------------------------------------------------------

    public function storeFranchiseAdjustment(Request $request): RedirectResponse
    {
        $request->validate([
            'franchise_name' => ['required', 'string', 'max:100', 'unique:franchise_adjustments,franchise_name'],
            'adjustment_gbp' => ['required', 'numeric', 'min:-999.99', 'max:999.99'],
        ]);

        FranchiseAdjustment::create([
            'franchise_name' => trim($request->input('franchise_name')),
            'adjustment_gbp' => $request->input('adjustment_gbp'),
        ]);

        return back()->with('flash_success', 'Franchise adjustment added.');
    }

    public function updateFranchiseAdjustment(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'adjustment_gbp' => ['required', 'numeric', 'min:-999.99', 'max:999.99'],
        ]);

        FranchiseAdjustment::findOrFail($id)->update([
            'adjustment_gbp' => $request->input('adjustment_gbp'),
        ]);

        return back()->with('flash_success', 'Franchise adjustment updated.');
    }

    public function destroyFranchiseAdjustment(int $id): RedirectResponse
    {
        FranchiseAdjustment::findOrFail($id)->delete();

        return back()->with('flash_success', 'Franchise adjustment removed.');
    }
}
