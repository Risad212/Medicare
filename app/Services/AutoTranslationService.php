<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Translates UI strings on the fly via an external provider.
 *
 * Each unique (source, locale) pair is cached forever (or per
 * `auto_translate.cache_ttl`), so the provider is hit only once per
 * string. Two drivers: 'google' (batched, keyed) and 'mymemory'
 * (keyless free tier, pooled concurrent requests).
 */
class AutoTranslationService
{
    public function translate(string $text, string $target, ?string $source = null): string
    {
        $text = trim($text);
        $source ??= (string) config('auto_translate.source', 'en');

        if ($text === '' || $target === $source) {
            return $text;
        }

        $key = 'autotranslate.'.md5($source.'|'.$target.'|'.$text);
        $ttl = config('auto_translate.cache_ttl');

        if ($ttl === null) {
            return Cache::rememberForever($key, fn () => $this->viaProvider($text, $target, $source));
        }

        return Cache::remember($key, (int) $ttl, fn () => $this->viaProvider($text, $target, $source));
    }

    /**
     * Translate many strings at once, preserving keys. Cached entries
     * never hit the provider.
     *
     * @param  array<int,string>  $texts
     * @return array<int,string>
     */
    public function translateMany(array $texts, string $target, ?string $source = null): array
    {
        $source ??= (string) config('auto_translate.source', 'en');
        $results = [];
        $missing = [];

        foreach ($texts as $i => $text) {
            $text = trim((string) $text);
            if ($text === '' || $target === $source) {
                $results[$i] = $text;

                continue;
            }
            $cached = Cache::get('autotranslate.'.md5($source.'|'.$target.'|'.$text));
            if (is_string($cached)) {
                $results[$i] = $cached;
            } else {
                $missing[$i] = $text;
            }
        }

        if ($missing !== []) {
            foreach ($this->viaProviderMany(array_values($missing), $target, $source) as $j => $translated) {
                $i = array_keys($missing)[$j];
                $results[$i] = $translated;
                $this->store($missing[$i], $translated, $target, $source);
            }
        }

        ksort($results);

        return $results;
    }

    protected function store(string $text, string $translated, string $target, string $source): void
    {
        $key = 'autotranslate.'.md5($source.'|'.$target.'|'.$text);
        $ttl = config('auto_translate.cache_ttl');

        if ($ttl === null) {
            Cache::forever($key, $translated);
        } else {
            Cache::put($key, $translated, (int) $ttl);
        }
    }

    /**
     * @param  array<int,string>  $texts
     * @return array<int,string>
     */
    protected function viaProviderMany(array $texts, string $target, string $source): array
    {
        $driver = (string) config('auto_translate.driver', 'mymemory');

        if ($driver === 'google') {
            return $this->viaGoogle($texts, $target, $source);
        }

        if ($driver === 'null') {
            return $texts;
        }

        return $this->viaMyMemory($texts, $target, $source);
    }

    protected function viaProvider(string $text, string $target, string $source): string
    {
        return $this->viaProviderMany([$text], $target, $source)[0] ?? $text;
    }

    /**
     * @param  array<int,string>  $texts
     * @return array<int,string>
     */
    protected function viaGoogle(array $texts, string $target, string $source): array
    {
        try {
            $response = Http::timeout(10)->post('https://translation.googleapis.com/language/translate/v2', [
                'key' => config('auto_translate.key'),
                'q' => array_values($texts),
                'source' => $source,
                'target' => $target,
                'format' => 'text',
            ]);

            if (! $response->successful()) {
                return array_values($texts);
            }

            $data = $response->json('data.translations', []);

            return collect($data)->map(fn ($t, $i) => (string) ($t['translatedText'] ?? array_values($texts)[$i] ?? ''))->all();
        } catch (\Throwable) {
            return array_values($texts);
        }
    }

    /**
     * @param  array<int,string>  $texts
     * @return array<int,string>
     */
    protected function viaMyMemory(array $texts, string $target, string $source): array
    {
        try {
            $responses = Http::pool(function ($pool) use ($texts, $target, $source) {
                foreach (array_values($texts) as $text) {
                    $pool->as(md5($text))->timeout(10)->get('https://api.mymemory.translated.net/get', [
                        'q' => mb_substr($text, 0, 450),
                        'langpair' => $source.'|'.$target,
                    ]);
                }
            });

            $out = [];
            foreach (array_values($texts) as $text) {
                $body = $responses[md5($text)] ?? null;
                $out[] = $this->extractMyMemory($body, $text);
            }

            return $out;
        } catch (\Throwable) {
            return array_values($texts);
        }
    }

    /**
     * MyMemory answers quota breaches and bad requests with HTTP 200
     * carrying the warning as translatedText — never let that leak
     * into pages; fall back to the source string instead.
     */
    protected function extractMyMemory(mixed $body, string $fallback): string
    {
        if (! is_object($body) || ! method_exists($body, 'json')) {
            return $fallback;
        }

        if ((int) $body->json('responseStatus') !== 200) {
            return $fallback;
        }

        $translated = (string) ($body->json('responseData.translatedText') ?? '');

        if ($translated === '' || str_contains($translated, 'MYMEMORY WARNING')) {
            return $fallback;
        }

        return $translated;
    }
}
