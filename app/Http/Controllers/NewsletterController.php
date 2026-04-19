<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    /**
     * Subscribe an email address to the newsletter.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name'  => ['nullable', 'string', 'max:150'],
        ]);

        $source = $request->input('source', 'footer');

        NewsletterSubscriber::subscribe(
            $request->input('email'),
            $request->input('name', ''),
            in_array($source, ['footer', 'order', 'wishlist']) ? $source : 'footer'
        );

        return back()->with('flash_success', 'You\'ve been subscribed to our newsletter. Welcome aboard!');
    }

    /**
     * Show the unsubscribe confirmation page.
     */
    public function unsubscribe(string $token): View
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        return view('newsletter.unsubscribe', compact('subscriber', 'token'));
    }

    /**
     * Process the unsubscribe.
     */
    public function confirmUnsubscribe(string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        if ($subscriber->isActive()) {
            $subscriber->update(['unsubscribed_at' => now()]);
        }

        return redirect()->route('home')
            ->with('flash_success', 'You\'ve been unsubscribed from our newsletter.');
    }
}
