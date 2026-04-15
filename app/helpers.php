<?php

if (! function_exists('star_rating')) {
    function star_rating(float $rating): string
    {
        $stars = round($rating / 20);
        $html  = '<span class="stars">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<span class="star ' . ($i <= $stars ? 'filled' : '') . '">★</span>';
        }
        $html .= '</span>';
        return $html;
    }
}

if (! function_exists('rating_class')) {
    function rating_class(float $rating): string
    {
        if ($rating >= 80) return 'rating--high';
        if ($rating >= 60) return 'rating--mid';
        return 'rating--low';
    }
}

if (! function_exists('format_date')) {
    function format_date(int $timestamp): string
    {
        return date('d M Y', $timestamp);
    }
}

if (! function_exists('truncate_text')) {
    function truncate_text(string $text, int $length = 120): string
    {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '…';
    }
}

if (! function_exists('igdb_img')) {
    function igdb_img(string $imageId, string $size = 'cover_big'): string
    {
        $path    = "/t_{$size}/{$imageId}.jpg";
        $encoded = rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
        return url('/img/' . $encoded);
    }
}
