<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
