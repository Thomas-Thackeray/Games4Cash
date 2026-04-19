<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminNewsletterController extends Controller
{
    public function index(): View
    {
        $subscribers = NewsletterSubscriber::orderByDesc('subscribed_at')->paginate(30);
        $activeCount = NewsletterSubscriber::activeCount();
        $totalCount  = NewsletterSubscriber::count();

        return view('admin.newsletter', compact('subscribers', 'activeCount', 'totalCount'));
    }

    /**
     * Send a newsletter to all active subscribers.
     */
    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body'    => ['required', 'string', 'max:10000'],
        ]);

        $subject = $request->input('subject');
        $body    = $request->input('body');

        $sent   = 0;
        $failed = 0;

        NewsletterSubscriber::whereNull('unsubscribed_at')
            ->orderBy('id')
            ->chunk(50, function ($batch) use ($subject, $body, &$sent, &$failed) {
                foreach ($batch as $subscriber) {
                    try {
                        Mail::to($subscriber->email)
                            ->send(new NewsletterMail($subject, $body, $subscriber->token));
                        $sent++;
                    } catch (\Throwable) {
                        $failed++;
                    }
                }
            });

        $message = "Newsletter sent to {$sent} subscriber(s).";
        if ($failed > 0) {
            $message .= " {$failed} failed to send.";
        }

        return back()->with('flash_success', $message);
    }

    /**
     * Remove a subscriber (hard delete from admin).
     */
    public function destroy(int $id): RedirectResponse
    {
        NewsletterSubscriber::findOrFail($id)->delete();

        return back()->with('flash_success', 'Subscriber removed.');
    }
}
