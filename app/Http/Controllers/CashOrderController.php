<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewQuoteMail;
use App\Mail\OrderConfirmationMail;
use App\Models\CashOrder;
use App\Models\GamePrice;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CashOrderController extends Controller
{

    // -----------------------------------------------------------------------

    /**
     * Checkout page — shows basket summary and collects pickup address.
     */
    public function create(): View|RedirectResponse
    {
        $user  = auth()->user();
        $items = $user->cashBasketItems()->latest('created_at')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cash-basket.index')
                ->with('flash_error', 'Your cash basket is empty.');
        }

        // Require all items to have a condition set
        if ($items->contains(fn ($i) => $i->condition === null)) {
            return redirect()->route('cash-basket.index')
                ->with('flash_error', 'Please select a condition for every game before proceeding.');
        }

        [$orderItems, $total] = $this->buildOrderItems($items);

        $minOrder = (float) Setting::get('min_order_gbp', 20);

        if ($minOrder > 0 && $total < $minOrder) {
            return redirect()->route('cash-basket.index')
                ->with('flash_error', sprintf(
                    'Your basket total must be at least £%.2f before you can submit. Current total: £%.2f.',
                    $minOrder,
                    $total
                ));
        }

        $totalFormatted = '£' . number_format($total, 2);

        return view('cash-order-checkout', compact('orderItems', 'total', 'totalFormatted'));
    }

    // -----------------------------------------------------------------------

    /**
     * Submit the basket as a new order, saving the pickup address.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'house_name_number'  => ['required', 'string', 'max:100'],
            'address_line1'      => ['required', 'string', 'max:150'],
            'address_line2'      => ['nullable', 'string', 'max:150'],
            'address_line3'      => ['nullable', 'string', 'max:150'],
            'city'               => ['required', 'string', 'max:100'],
            'county'             => ['nullable', 'string', 'max:100'],
            'postcode'           => ['required', 'string', 'max:20'],
            'agreed_terms'       => ['required', 'accepted'],
            'confirmed_contents' => ['required', 'accepted'],
        ], [
            'agreed_terms.required'       => 'You must agree to the Terms & Conditions.',
            'agreed_terms.accepted'       => 'You must agree to the Terms & Conditions.',
            'confirmed_contents.required' => 'You must confirm that all items are present and correct.',
            'confirmed_contents.accepted' => 'You must confirm that all items are present and correct.',
        ]);

        $user  = auth()->user();
        $items = $user->cashBasketItems()->latest('created_at')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cash-basket.index')
                ->with('flash_error', 'Your cash basket is empty.');
        }

        // All items must have a condition selected
        if ($items->contains(fn ($i) => $i->condition === null)) {
            return redirect()->route('cash-basket.index')
                ->with('flash_error', 'Please select a condition for every game before proceeding.');
        }

        [$orderItems, $total] = $this->buildOrderItems($items);

        $minOrder = (float) Setting::get('min_order_gbp', 20);

        if ($minOrder > 0 && $total < $minOrder) {
            return redirect()->route('cash-basket.index')
                ->with('flash_error', sprintf(
                    'Your basket total must be at least £%.2f before you can submit.',
                    $minOrder
                ));
        }

        $order = CashOrder::create([
            'order_ref'         => CashOrder::generateRef(),
            'user_id'           => $user->id,
            'status'            => 'pending',
            'items'             => $orderItems,
            'total_gbp'         => round($total, 2),
            'house_name_number' => $request->input('house_name_number'),
            'address_line1'     => $request->input('address_line1'),
            'address_line2'     => $request->input('address_line2') ?: null,
            'address_line3'     => $request->input('address_line3') ?: null,
            'city'              => $request->input('city'),
            'county'            => $request->input('county') ?: null,
            'postcode'           => strtoupper(trim($request->input('postcode'))),
            'agreed_terms'       => true,
            'confirmed_contents' => true,
        ]);

        $user->cashBasketItems()->delete();

        Mail::to($user->email)->send(new OrderConfirmationMail($user, $order));

        $adminEmail = Setting::get('admin_notification_email', 'thomasthackeray0@gmail.com');
        try {
            Mail::to($adminEmail)->send(new AdminNewQuoteMail($user, $order));
        } catch (\Throwable) {
            // Non-critical — don't fail the order submission if admin email fails
        }

        ActivityLogger::quote(
            'Cash quote submitted: ' . $order->order_ref .
            ' (' . count($orderItems) . ' ' . (count($orderItems) === 1 ? 'game' : 'games') . ',' .
            ' £' . number_format(round($total, 2), 2) . ')',
            $request
        );

        return redirect()->route('cash-orders.confirmation', $order->order_ref);
    }

    // -----------------------------------------------------------------------

    /**
     * Confirmation page shown immediately after submission.
     */
    public function confirmation(string $ref): View
    {
        $order = CashOrder::where('order_ref', $ref)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('cash-order-confirmation', compact('order'));
    }

    // -----------------------------------------------------------------------

    /**
     * List all of the authenticated user's past submissions.
     */
    public function index(): View
    {
        $orders = auth()->user()->cashOrders()->latest()->paginate(15);

        return view('cash-orders', compact('orders'));
    }

    // -----------------------------------------------------------------------

    /**
     * Show a single submission for the authenticated user.
     */
    public function show(string $ref): View
    {
        $order = CashOrder::where('order_ref', $ref)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('cash-order-detail', compact('order'));
    }

    // -----------------------------------------------------------------------

    /**
     * Cancel an order — only allowed within 2 hours of placing it.
     */
    public function cancel(string $ref): RedirectResponse
    {
        $order = CashOrder::where('order_ref', $ref)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (! $order->canCancel()) {
            return back()->with('flash_error', 'This order can no longer be cancelled. The 2-hour cancellation window has passed, or the order is no longer pending.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('cash-orders.show', $ref)
            ->with('flash_success', 'Your order ' . $ref . ' has been cancelled.');
    }

    // -----------------------------------------------------------------------

    /**
     * Re-add all items from a cancelled order back into the user's cash basket.
     */
    public function resubmit(string $ref): RedirectResponse
    {
        $order = CashOrder::where('order_ref', $ref)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== 'cancelled') {
            return back()->with('flash_error', 'Only cancelled orders can be re-added to your basket.');
        }

        $user  = auth()->user();
        $added = 0;

        foreach ($order->items as $item) {
            if (empty($item['igdb_game_id'])) {
                continue;
            }

            // Skip if already in basket for same game + platform
            $platformId = $item['platform_id'] ?? null;
            $exists = $user->cashBasketItems()
                ->where('igdb_game_id', $item['igdb_game_id'])
                ->where(function ($q) use ($platformId) {
                    $platformId !== null
                        ? $q->where('platform_id', $platformId)
                        : $q->whereNull('platform_id');
                })
                ->exists();

            if ($exists) {
                continue;
            }

            $user->cashBasketItems()->create([
                'igdb_game_id' => $item['igdb_game_id'],
                'platform_id'  => $platformId,
                'game_title'   => $item['game_title'],
                'cover_url'    => $item['cover_url'] ?? null,
            ]);

            $added++;
        }

        if ($added === 0) {
            return redirect()->route('cash-basket.index')
                ->with('flash_error', 'All games from this order are already in your cash basket.');
        }

        return redirect()->route('cash-basket.index')
            ->with('flash_success', $added . ' ' . ($added === 1 ? 'game' : 'games') . ' from order ' . $ref . ' added back to your cash basket.');
    }

    // -----------------------------------------------------------------------

    /**
     * Compute order items and total from the user's basket items.
     * Returns [$orderItems, $total].
     */
    private function buildOrderItems($items): array
    {
        $allPlatforms      = config('igdb.all_platforms');
        $conditionMods     = [
            'new'      => (float) Setting::get('condition_new_pct', 20),
            'complete' => (float) Setting::get('condition_complete_pct', 0),
            'disk'     => (float) Setting::get('condition_disk_pct', -50),
        ];
        $conditionLabels = [
            'new'      => 'Brand New',
            'complete' => 'Complete Game',
            'disk'     => 'Just Disk',
        ];
        $total      = 0.0;
        $orderItems = [];

        foreach ($items as $item) {
            $pricing   = null;
            $gamePrice = GamePrice::where('igdb_game_id', $item->igdb_game_id)->first();

            if ($gamePrice) {
                try {
                    $pricing = $item->platform_id
                        ? $gamePrice->getComputedPriceForPlatform((int) $item->platform_id, [], $item->game_title)
                        : $gamePrice->getComputedPrice([], $item->game_title);
                } catch (\Throwable) {
                    // best-effort
                }
            }

            $priceNumeric = null;
            $displayPrice = null;

            if ($pricing && ! $pricing['is_free']) {
                $priceNumeric = (float) $pricing['price_numeric'];

                // Apply condition modifier
                if ($item->condition && isset($conditionMods[$item->condition])) {
                    $pct          = $conditionMods[$item->condition];
                    $priceNumeric = max(0.01, round($priceNumeric * (1 + $pct / 100), 2));
                }

                $displayPrice = '£' . number_format($priceNumeric, 2);
                $total       += $priceNumeric;
            }

            $orderItems[] = [
                'igdb_game_id'    => $item->igdb_game_id,
                'game_title'      => $item->game_title,
                'cover_url'       => $item->cover_url,
                'platform_id'     => $item->platform_id,
                'platform_name'   => $item->platform_id ? ($allPlatforms[$item->platform_id] ?? null) : null,
                'condition'       => $item->condition,
                'condition_label' => $item->condition ? ($conditionLabels[$item->condition] ?? null) : null,
                'display_price'   => $displayPrice,
                'price_numeric'   => $priceNumeric,
            ];
        }

        return [$orderItems, $total];
    }
}
