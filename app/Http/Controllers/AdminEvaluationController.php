<?php

namespace App\Http\Controllers;

use App\Models\GameEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEvaluationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'all');

        $query = GameEvaluation::with('user')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $evaluations = $query->paginate(20)->withQueryString();

        return view('admin.evaluations.index', compact('evaluations', 'status'));
    }

    public function show(int $id): View
    {
        $evaluation = GameEvaluation::with('user')->findOrFail($id);

        return view('admin.evaluations.show', compact('evaluation'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $evaluation = GameEvaluation::findOrFail($id);

        $request->validate([
            'status'      => ['required', 'in:pending,reviewed,closed'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $evaluation->update([
            'status'      => $request->input('status'),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return back()->with('flash_success', 'Evaluation updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $evaluation = GameEvaluation::findOrFail($id);

        // Clean up stored images
        if (!empty($evaluation->image_paths)) {
            foreach ($evaluation->image_paths as $path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }

        $evaluation->delete();

        return redirect()->route('admin.evaluations.index')
            ->with('flash_success', 'Evaluation deleted.');
    }
}
