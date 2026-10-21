{{-- Single JSON-LD emitter for the whole frontend (Stage 4, T8).

     Callers pass a fully-built $jsonld array; nothing is assembled here.
     The payload is printed raw ({!! !!}) ONLY because it is pre-escaped
     server-side: json_encode with the JSON_HEX_* flags rewrites <, >,
     &, ' and " into unicode escapes, so stored values can never emit a
     closing </script> or dangling attribute quotes. --}}
@php
    $jsonldReady = isset($jsonld) && is_array($jsonld) && $jsonld !== [];
@endphp

@if($jsonldReady)
<script type="application/ld+json" data-notranslate>{!! json_encode($jsonld, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
