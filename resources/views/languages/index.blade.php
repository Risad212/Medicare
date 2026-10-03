@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">{{ __('messages.admin.language_list') }}</h1>
        <p class="mc-sub">{{ __('messages.admin.language_list_sub') }}</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.languages.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> {{ __('messages.admin.add_language') }}</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.admin.language_name') }}</th>
                    <th>{{ __('messages.admin.language_code') }}</th>
                    <th>{{ __('messages.common.status') }}</th>
                    <th class="text-right">{{ __('messages.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($languages as $language)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        {{ $language->name }}
                        @if($language->is_default)
                            <span class="ml-2 rounded-full bg-teal-bg px-2 py-0.5 text-xs font-bold text-teal-dk">{{ __('messages.admin.default') }}</span>
                        @endif
                    </td>
                    <td><code>{{ $language->code }}</code></td>
                    <td>{{ $language->is_active ? __('messages.common.active') : __('messages.common.inactive') }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.languages.edit', $language) }}" class="mc-link">{{ __('messages.common.edit') }}</a>
                        @if(! $language->is_default)
                            <form action="{{ route('admin.languages.default', $language) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="mc-link">{{ __('messages.admin.set_default') }}</button>
                            </form>
                            <form action="{{ route('admin.languages.toggle', $language) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="mc-link">{{ $language->is_active ? __('messages.admin.deactivate') : __('messages.admin.activate') }}</button>
                            </form>
                            <form action="{{ route('admin.languages.destroy', $language) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.admin.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-link text-red-t">{{ __('messages.common.delete') }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-mut">{{ __('messages.admin.no_languages') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $languages->links() }}

@endsection
