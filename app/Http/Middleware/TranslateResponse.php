<?php

namespace App\Http\Middleware;

use App\Services\AutoTranslationService;
use Closure;
use DOMDocument;
use DOMText;
use DOMXPath;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Translates visible text of frontend HTML pages into the active locale
 * on the fly. Staff areas, JSON, and the source locale pass through
 * untouched. Provider failures degrade to the original text.
 */
class TranslateResponse
{
    /**
     * @var array<int,string>
     */
    private const SKIP_TAGS = ['script', 'style', 'noscript', 'code', 'pre', 'textarea'];

    public function __construct(private AutoTranslationService $translator) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $this->shouldTranslate($request, $response)) {
            return $response;
        }

        $translated = $this->translateHtml((string) $response->getContent(), (string) app()->getLocale());

        if ($translated !== null) {
            $response->setContent($translated);
        }

        return $response;
    }

    private function shouldTranslate(Request $request, Response $response): bool
    {
        if (! config('auto_translate.enabled', true)) {
            return false;
        }

        if (config('auto_translate.driver', 'mymemory') === 'null') {
            return false;
        }

        if (! $request->isMethod('get')) {
            return false;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return false;
        }

        // Never send authenticated (potentially PHI-bearing) pages to an
        // external translation provider. Only public, guest-visible content
        // is eligible for auto-translation.
        if (auth()->check()) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return false;
        }

        $locale = (string) app()->getLocale();
        if ($locale === '' || $locale === (string) config('auto_translate.source', 'en')) {
            return false;
        }

        foreach (config('auto_translate.skip_prefixes', []) as $prefix) {
            if ($request->is(trim((string) $prefix, '/').'/*') || $request->is(trim((string) $prefix, '/'))) {
                return false;
            }
        }

        return true;
    }

    private function translateHtml(string $html, string $locale): ?string
    {
        if (trim($html) === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $loaded = $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

            if (! $loaded) {
                return null;
            }

            $xpath = new DOMXPath($dom);
            $skip = implode('|', self::SKIP_TAGS);
            /** @var \DOMNodeList $nodes */
            $nodes = $xpath->query(
                '//text()[normalize-space(.) != ""]'
                ."[not({$this->ancestorPredicate($skip)})]"
                .'[not(ancestor::*[@data-notranslate or @translate="no" or contains(concat(" ", normalize-space(@class), " "), " notranslate ")])]'
            );

            if ($nodes === false || $nodes->length === 0) {
                return null;
            }

            /** @var array<int,DOMText> $targets */
            $targets = [];
            /** @var array<int,string> $unique */
            $unique = [];
            /** @var array<string,int> $indexByHash */
            $indexByHash = [];

            foreach ($nodes as $node) {
                if (! $node instanceof DOMText) {
                    continue;
                }
                $text = trim($node->nodeValue ?? '');
                if ($text === '' || ! $this->isTranslatable($text)) {
                    continue;
                }
                $hash = md5($text);
                if (! isset($indexByHash[$hash])) {
                    $indexByHash[$hash] = count($unique);
                    $unique[] = $text;
                }
                $targets[] = $node;
            }

            if ($unique === []) {
                return null;
            }

            $translated = $this->translator->translateMany($unique, $locale);

            foreach ($targets as $node) {
                $text = trim($node->nodeValue ?? '');
                $i = $indexByHash[md5($text)] ?? null;
                if ($i === null) {
                    continue;
                }
                // Preserve surrounding whitespace of the original node.
                $original = $node->nodeValue ?? '';
                $leading = strspn($original, " \t\n\r\0\x0B");
                $trailing = strspn(strrev($original), " \t\n\r\0\x0B");
                $node->nodeValue = substr($original, 0, $leading).($translated[$i] ?? $text).($trailing > 0 ? substr($original, -$trailing) : '');
            }

            $out = $dom->saveHTML();

            return $out === false ? null : mb_convert_encoding($out, 'UTF-8', 'HTML-ENTITIES');
        } catch (\Throwable) {
            return null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function ancestorPredicate(string $skip): string
    {
        return implode(' or ', array_map(fn ($tag) => "ancestor::{$tag}", explode('|', $skip)));
    }

    private function isTranslatable(string $text): bool
    {
        // No letters at all (pure numbers, symbols, whitespace) — nothing to translate.
        if (! preg_match('/\p{L}/u', $text)) {
            return false;
        }

        // Emails, URLs, and template placeholders must survive verbatim.
        if (filter_var($text, FILTER_VALIDATE_EMAIL) || filter_var($text, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (str_contains($text, '{{') || str_contains($text, '@{{')) {
            return false;
        }

        return true;
    }
}
