<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'email'          => ['required', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'regex:/^[\+\d\s\-\(\)]{7,20}$/'],
            'message'        => ['required', 'string', 'max:3000'],
        ], [
            'contact_number.regex' => 'Please enter a valid contact number (7–20 digits).',
        ]);

        ContactSubmission::create([
            'name'           => $request->input('name'),
            'email'          => $request->input('email'),
            'contact_number' => $request->input('contact_number') ?: null,
            'message'        => $request->input('message'),
        ]);

        return back()->with('flash_success', "Thanks for getting in touch, {$request->input('name')}! We'll get back to you as soon as possible.");
    }
}
