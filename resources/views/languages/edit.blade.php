@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">{{ __('messages.admin.edit_language') }}</h1>
    </div>
</div>

@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 list-disc pl-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.languages.update', $language) }}" method="POST">
    @csrf
    @method('PUT')

    <section class="mc-sec">
        <div class="mc-sec-bd">
            <div class="mc-f">
                <label>{{ __('messages.admin.language_name') }} <i class="req">*</i></label>
                <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('name', $language->name) }}">
            </div>
            <div class="mc-f">
                <label>{{ __('messages.admin.language_code') }} <i class="req">*</i></label>
                <input type="text" name="code" maxlength="10" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('code', $language->code) }}">
                <small class="text-mut">{{ __('messages.admin.language_code_hint') }}</small>
            </div>
            <div class="mc-f">
                <label class="d-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $language->is_active) ? 'checked' : '' }}>
                    {{ __('messages.common.active') }}
                </label>
            </div>
        </div>
    </section>

    <div class="mt-3 flex gap-2">
        <button type="submit" class="mc-btn">{{ __('messages.common.update') }}</button>
        <a href="{{ route('admin.languages.index') }}" class="mc-btn mc-btn-ghost">{{ __('messages.common.back') }}</a>
    </div>
</form>

@endsection
