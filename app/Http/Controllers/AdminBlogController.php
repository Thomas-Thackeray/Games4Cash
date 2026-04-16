<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::latest('created_at')->paginate(20);

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'author'       => ['required', 'string', 'max:100'],
            'image'        => ['required', 'in:gaming,news,review,deals'],
            'excerpt'      => ['nullable', 'string', 'max:300'],
            'published_at' => ['nullable', 'date'],
            'publish_now'  => ['nullable'],
        ]);

        $post = new BlogPost();
        $post->title   = $data['title'];
        $post->slug    = BlogPost::generateSlug($data['title']);
        $post->content = $data['content'];
        $post->author  = $data['author'];
        $post->image   = $data['image'];
        $post->excerpt = !empty($data['excerpt']) ? $data['excerpt'] : null;

        if ($request->filled('publish_now')) {
            $post->published_at = now();
        } elseif (!empty($data['published_at'])) {
            $post->published_at = $data['published_at'];
        }

        $post->save();

        // Auto-generate excerpt if none provided
        if (is_null($post->excerpt)) {
            $post->excerpt = $post->generateExcerpt();
            $post->saveQuietly();
        }

        return redirect()->route('admin.blog.index')
                         ->with('flash_success', 'Post "' . $post->title . '" created.');
    }

    public function edit(int $id): View
    {
        $post = BlogPost::findOrFail($id);

        return view('admin.blog.edit', compact('post'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $post = BlogPost::findOrFail($id);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'author'       => ['required', 'string', 'max:100'],
            'image'        => ['required', 'in:gaming,news,review,deals'],
            'excerpt'      => ['nullable', 'string', 'max:300'],
            'published_at' => ['nullable', 'date'],
            'publish_now'  => ['nullable'],
        ]);

        $post->title   = $data['title'];
        $post->slug    = BlogPost::generateSlug($data['title'], $post->id);
        $post->content = $data['content'];
        $post->author  = $data['author'];
        $post->image   = $data['image'];
        $post->excerpt = !empty($data['excerpt']) ? $data['excerpt'] : $post->generateExcerpt();

        if ($request->filled('publish_now')) {
            $post->published_at = $post->published_at ?? now();
        } elseif ($request->has('published_at')) {
            $post->published_at = !empty($data['published_at']) ? $data['published_at'] : null;
        }

        $post->save();

        return redirect()->route('admin.blog.index')
                         ->with('flash_success', 'Post updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        BlogPost::findOrFail($id)->delete();

        return redirect()->route('admin.blog.index')
                         ->with('flash_success', 'Post deleted.');
    }
}
