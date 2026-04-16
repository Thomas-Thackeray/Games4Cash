<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'author',
        'image',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Scope: only live/published posts
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    // Generate a unique slug from a title
    public static function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 2;

        while (
            static::where('slug', $slug)
                  ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                  ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    // Map image key → public path
    public static function imagePath(string $key): string
    {
        return asset('images/blog/blog-' . $key . '.svg');
    }

    // Human-readable image label
    public static function imageLabel(string $key): string
    {
        return match($key) {
            'gaming'  => 'Gaming',
            'news'    => 'News & Events',
            'review'  => 'Reviews',
            'deals'   => 'Deals & Prices',
            default   => ucfirst($key),
        };
    }

    public static function imageOptions(): array
    {
        return ['gaming', 'news', 'review', 'deals'];
    }

    // Auto-generate excerpt from HTML content (plain text, truncated)
    public function generateExcerpt(int $length = 160): string
    {
        $text = strip_tags($this->content);
        $text = preg_replace('/\s+/', ' ', trim($text));
        return Str::limit($text, $length, '…');
    }
}
