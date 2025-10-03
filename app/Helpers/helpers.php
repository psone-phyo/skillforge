<?php

use Illuminate\Support\Str;

if (!function_exists('normalize_slug')) {
    /**
     * Normalize string for categories/tags
     * Converts "Web Development" or "web-development" -> "webdevelopment"
     */
    function make_slug(string $value): string
    {
        $slug = Str::lower($value);

        $slug = preg_replace('/[\s\-_]/', '', $slug);

        $slug = preg_replace('/[^a-z0-9]/', '', $slug);

        return $slug;
    }
}
