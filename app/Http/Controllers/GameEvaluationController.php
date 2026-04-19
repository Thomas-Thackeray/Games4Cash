<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewEvaluationMail;
use App\Models\GameEvaluation;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GameEvaluationController extends Controller
{
    private const CONDITIONS = [
        'new'      => 'New / Sealed',
        'complete' => 'Complete (In Case)',
        'disk'     => 'Disc Only',
        'other'    => 'Other / Not Sure',
    ];

    public function create(): View
    {
        return view('evaluations.create', [
            'platforms'  => config('igdb.all_platforms'),
            'conditions' => self::CONDITIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'game_title'  => ['required', 'string', 'max:255'],
            'platform'    => ['required', 'string', 'max:100'],
            'condition'   => ['required', 'string', 'in:' . implode(',', array_keys(self::CONDITIONS))],
            'description' => ['nullable', 'string', 'max:2000'],
            'images'      => ['nullable', 'array', 'max:5'],
            'images.*'    => ['image', 'max:5120'], // 5 MB per image
        ], [
            'images.max'    => 'You may upload a maximum of 5 images.',
            'images.*.image'=> 'Each file must be an image (JPEG, PNG, GIF, WebP).',
            'images.*.max'  => 'Each image must be under 5 MB.',
        ]);

        $user       = auth()->user();
        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store("evaluations/{$user->id}", 'public');
                $imagePaths[] = $path;
            }
        }

        $evaluation = GameEvaluation::create([
            'user_id'     => $user->id,
            'game_title'  => $request->input('game_title'),
            'platform'    => $request->input('platform'),
            'condition'   => self::CONDITIONS[$request->input('condition')],
            'description' => $request->input('description'),
            'image_paths' => $imagePaths,
        ]);

        // Notify admin
        $adminEmail = Setting::get('admin_notification_email', config('mail.from.address'));
        try {
            Mail::to($adminEmail)->send(new AdminNewEvaluationMail($evaluation));
        } catch (\Throwable) {}

        return redirect()->route('evaluations.index')
            ->with('flash_success', 'Your evaluation request has been submitted. We\'ll be in touch shortly.');
    }

    public function index(): View
    {
        $evaluations = GameEvaluation::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('evaluations.index', compact('evaluations'));
    }
}
