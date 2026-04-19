<?php

namespace App\Http\Controllers;

use App\Models\CashBasketItem;
use App\Models\CashOrder;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $emailChanging    = $request->input('email')    !== $user->email;
        $usernameChanging = $request->input('username') !== $user->username;

        $rules = [
            'first_name'     => ['required', 'string', 'max:100'],
            'surname'        => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'contact_number' => ['required', 'string', 'regex:/^[\+\d\s\-\(\)]{7,20}$/'],
            'username'       => ['required', 'string', 'alpha_dash', 'min:12', 'max:30', 'unique:users,username,' . $user->id, 'regex:/[0-9]/'],
        ];

        if ($emailChanging || $usernameChanging) {
            $rules['current_password'] = ['required', 'string'];
        }

        $request->validate($rules, [
            'contact_number.regex'      => 'Please enter a valid contact number (7–20 digits).',
            'username.alpha_dash'       => 'Username may only contain letters, numbers, dashes, and underscores.',
            'username.min'              => 'Username must be at least 12 characters.',
            'username.regex'            => 'Username must contain at least one number.',
            'username.unique'           => 'That username is already taken.',
            'email.unique'              => 'An account with that email already exists.',
            'current_password.required' => 'Please enter your current password to change your email or username.',
        ]);

        if (($emailChanging || $usernameChanging) && ! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password is incorrect.'])->withInput();
        }

        $user->update([
            'first_name'     => $request->input('first_name'),
            'surname'        => $request->input('surname'),
            'name'           => $request->input('first_name') . ' ' . $request->input('surname'),
            'email'          => $request->input('email'),
            'contact_number' => $request->input('contact_number'),
            'username'       => $request->input('username'),
        ]);

        return back()->with('flash_success', 'Your profile has been updated successfully.');
    }

    public function export(Request $request): Response
    {
        $user = auth()->user();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'id'             => $user->id,
                'first_name'     => $user->first_name,
                'surname'        => $user->surname,
                'username'       => $user->username,
                'email'          => $user->email,
                'contact_number' => $user->contact_number,
                'role'           => $user->role,
                'registered_at'  => $user->created_at?->toIso8601String(),
                'last_active_at' => $user->last_active_at?->toIso8601String(),
            ],
            'wishlist' => Wishlist::where('user_id', $user->id)
                ->orderBy('created_at')
                ->get(['igdb_game_id', 'game_title', 'platform_id', 'cover_url', 'created_at'])
                ->map(fn ($w) => [
                    'igdb_game_id' => $w->igdb_game_id,
                    'game_title'   => $w->game_title,
                    'added_at'     => $w->created_at?->toIso8601String(),
                ])
                ->all(),
            'cash_basket' => CashBasketItem::where('user_id', $user->id)
                ->orderBy('created_at')
                ->get(['igdb_game_id', 'game_title', 'platform_id', 'condition', 'created_at'])
                ->map(fn ($b) => [
                    'igdb_game_id' => $b->igdb_game_id,
                    'game_title'   => $b->game_title,
                    'condition'    => $b->condition,
                    'added_at'     => $b->created_at?->toIso8601String(),
                ])
                ->all(),
            'cash_orders' => CashOrder::where('user_id', $user->id)
                ->orderBy('created_at')
                ->get()
                ->map(fn ($o) => [
                    'order_ref'   => $o->order_ref,
                    'status'      => $o->status,
                    'total_gbp'   => $o->total_gbp,
                    'items'       => $o->items,
                    'address'     => implode(', ', array_filter([
                        $o->house_name_number,
                        $o->address_line1,
                        $o->address_line2,
                        $o->address_line3,
                        $o->city,
                        $o->county,
                        $o->postcode,
                    ])),
                    'submitted_at' => $o->created_at?->toIso8601String(),
                ])
                ->all(),
        ];

        $filename = 'my-data-' . now()->format('Y-m-d') . '.json';
        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'confirm_password' => ['required', 'string'],
        ], [
            'confirm_password.required' => 'Please enter your password to confirm deletion.',
        ]);

        if (! Hash::check($request->input('confirm_password'), $user->password)) {
            return back()->withErrors(['confirm_password' => 'Password is incorrect.'])->withFragment('danger-zone');
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('flash_success', 'Your account has been permanently deleted.');
    }
}
