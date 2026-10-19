@extends('frontend.layouts.front-app')

@section('meta_title', $seo->meta_title ?? __('messages.public.faq_title'))
@section('meta_description', $seo->meta_description ?? '')
@section('meta_keywords', $seo->meta_keywords ?? '')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => __('messages.public.faq_title')
])

<section class="py-5">
    <div class="container" style="max-width:860px">
        <p class="text-muted mb-4">{{ __('messages.public.faq_intro') }}</p>

        @if($faqs->count())
        <style>
            .mc-faq-list{display:grid;gap:12px}
            .mc-faq-item{background:#fff;border:1px solid #e3e9f0;border-radius:14px;transition:border-color .25s,box-shadow .25s}
            .mc-faq-item:hover{border-color:#cfdbe7;box-shadow:0 2px 10px rgba(22,32,43,.05)}
            .mc-faq-item.open{border-color:#0e7c66;box-shadow:0 3px 14px rgba(11,92,76,.08)}
            .mc-faq-q{display:flex;align-items:center;justify-content:space-between;gap:16px;width:100%;padding:17px 20px;background:none;border:0;text-align:start;color:#16202b;font-weight:600;font-size:15.5px;line-height:1.45;min-height:52px;border-radius:14px;cursor:pointer}
            .mc-faq-q:focus-visible{outline:2px solid #05d3b0;outline-offset:2px}
            .mc-faq-item.open .mc-faq-q{color:#0e7c66}
            .mc-faq-icon{flex:0 0 auto;width:9px;height:9px;border-right:2px solid #94a3b8;border-bottom:2px solid #94a3b8;transform:rotate(45deg);margin-bottom:4px;transition:transform .3s,border-color .3s}
            .mc-faq-item.open .mc-faq-icon{transform:rotate(225deg);border-color:#0e7c66;margin-bottom:0}
            .mc-faq-a{padding:0 20px 18px;color:#64748b;font-size:14.5px;line-height:1.65}
            @media (max-width:575px){.mc-faq-q{padding:14px 16px;min-height:48px}.mc-faq-a{padding:0 16px 15px}}
        </style>
        <div class="mc-faq-list" id="faqAccordion">
            @foreach($faqs as $faq)
            <div class="mc-faq-item {{ $loop->first ? 'open' : '' }}">
                <h2 class="m-0">
                    <button class="mc-faq-q" type="button" data-bs-toggle="collapse" data-bs-target="#faq-answer-{{ $faq->id }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        <span>{{ $faq->question }}</span>
                        <span class="mc-faq-icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-answer-{{ $faq->id }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                    <div class="mc-faq-a">{!! nl2br(e($faq->answer)) !!}</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <div class="alert alert-light border">{{ __('messages.public.faq_empty') }}</div>
        @endif
    </div>
</section>

<script>
    (function () {
        var list = document.getElementById('faqAccordion');
        if (!list) return;

        list.querySelectorAll('.mc-faq-item').forEach(function (item) {
            var body = item.querySelector('.collapse');
            if (!body) return;

            body.addEventListener('show.bs.collapse', function () {
                item.classList.add('open');
            });
            body.addEventListener('hide.bs.collapse', function () {
                item.classList.remove('open');
            });
        });
    })();
</script>

@endsection
