<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use RuntimeException;

class BlogContentSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'blockquote', 'br', 'code', 'em', 'figcaption', 'figure',
        'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'li', 'ol',
        'p', 'pre', 's', 'strong', 'table', 'tbody', 'td', 'th', 'thead',
        'tr', 'u', 'ul',
    ];

    private const DROP_CONTENT_TAGS = [
        'audio', 'button', 'embed', 'form', 'iframe', 'input', 'link',
        'meta', 'object', 'script', 'select', 'style', 'svg', 'textarea',
        'video',
    ];

    public function sanitize(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="blog-content">'.$html.'</div>',
                LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (! $loaded) {
            throw new RuntimeException('Unable to parse blog content for sanitization.');
        }

        $container = $document->getElementById('blog-content');
        if (! $container) {
            throw new RuntimeException('Unable to locate the sanitized blog content.');
        }

        $this->sanitizeChildren($container);

        $content = '';
        foreach ($container->childNodes as $child) {
            $content .= $document->saveHTML($child);
        }

        return $content;
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                $parent->removeChild($child);

                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_CONTENT_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            $this->sanitizeChildren($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            $this->sanitizeAttributes($child, $tag);
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowedAttributes = match ($tag) {
            'a' => ['href', 'title', 'target'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'ol' => ['start'],
            'td', 'th' => ['colspan', 'rowspan'],
            default => [],
        };

        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! in_array(strtolower($attribute->name), $allowedAttributes, true)) {
                $element->removeAttributeNode($attribute);
            }
        }

        if ($tag === 'a') {
            $href = $element->getAttribute('href');
            if ($href !== '' && ! $this->isSafeUrl($href, true)) {
                $element->removeAttribute('href');
            }

            if ($element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
            }
        }

        if ($tag === 'img') {
            $src = $element->getAttribute('src');
            if ($src === '' || ! $this->isSafeUrl($src, false)) {
                $element->parentNode?->removeChild($element);
            }
        }
    }

    private function isSafeUrl(string $url, bool $allowMailAndTel): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '//') || preg_match('/[\x00-\x20]/', $url)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme === null) {
            return true;
        }

        $allowedSchemes = $allowMailAndTel ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'];

        return in_array(strtolower($scheme), $allowedSchemes, true);
    }
}
