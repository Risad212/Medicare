<?php

namespace App\Services;

/**
 * Sanitizes admin-authored blog HTML before it is rendered with {!! !!}
 * on the public blog page. Allows basic formatting only.
 */
class BlogSanitizer
{
    public static function sanitize(string $html): string
    {
        $html = strip_tags($html, '<p><br><b><strong><i><em><ul><ol><li>');

        return preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    }
}
