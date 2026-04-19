<?php

namespace App\Mail;

use App\Models\GameEvaluation;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewEvaluationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public GameEvaluation $evaluation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] New Game Evaluation Request — ' . $this->evaluation->game_title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-evaluation',
            with: [
                'evaluation' => $this->evaluation,
                'adminUrl'   => url('/admin/evaluations/' . $this->evaluation->id),
            ],
        );
    }
}
