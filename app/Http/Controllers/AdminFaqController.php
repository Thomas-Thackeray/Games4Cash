<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.faqs', compact('faqs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $maxOrder = Faq::max('sort_order') ?? 0;

        Faq::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'sort_order'  => $maxOrder + 1,
        ]);

        return back()->with('flash_success', 'FAQ added.');
    }

    public function edit(int $id): View
    {
        $faq = Faq::findOrFail($id);

        return view('admin.faq-edit', compact('faq'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $faq = Faq::findOrFail($id);
        $faq->update([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'sort_order'  => $request->input('sort_order', $faq->sort_order),
        ]);

        return redirect()->route('admin.faqs.index')->with('flash_success', 'FAQ updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Faq::findOrFail($id)->delete();

        return back()->with('flash_success', 'FAQ deleted.');
    }
}
