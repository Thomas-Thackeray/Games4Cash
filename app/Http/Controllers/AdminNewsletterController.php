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
     * Send a test newsletter to the admin email only.
     */
    public function sendTest(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body'    => ['required', 'string', 'max:10000'],
        ]);

        $adminEmail = \App\Models\Setting::get('admin_notification_email', config('mail.from.address'));

        try {
            Mail::to($adminEmail)->send(new NewsletterMail(
                '[TEST] ' . $request->input('subject'),
                $request->input('body'),
                'test-token'
            ));
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('flash_error', 'Test email failed: ' . $e->getMessage());
        }

        return back()
            ->withInput()
            ->with('flash_success', "Test email sent to {$adminEmail}. Check your inbox (or spam).");
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
        $lastError = null;

        NewsletterSubscriber::whereNull('unsubscribed_at')
            ->orderBy('id')
            ->chunk(50, function ($batch) use ($subject, $body, &$sent, &$failed, &$lastError) {
                foreach ($batch as $subscriber) {
                    try {
                        Mail::to($subscriber->email)
                            ->send(new NewsletterMail($subject, $body, $subscriber->token));
                        $sent++;
                    } catch (\Throwable $e) {
                        $failed++;
                        $lastError = $e->getMessage();
                    }
                }
            });

        if ($sent === 0 && $failed > 0) {
            return back()
                ->withInput()
                ->with('flash_error', "All {$failed} send(s) failed. Last error: {$lastError}");
        }

        $message = "Newsletter sent to {$sent} subscriber(s).";
        if ($failed > 0) {
            $message .= " {$failed} failed — last error: {$lastError}";
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
