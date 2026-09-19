@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Content</p>
        <h1 class="mc-title">Serv<em>ices</em></h1>
        <p class="mc-sub">Cards shown on the home and services pages.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.services.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add service</a>
        <a href="{{ route('settings.service') }}" class="mc-btn ghost">Content & SEO</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><span>{{ $services->count() }} records</span></div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Service</th>
                    <th>Description</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="mc-who">
                            @if($service->icon)
                                <img src="{{ asset('storage/'.$service->icon) }}" alt="{{ $service->title }}" class="mc-av b" style="object-fit:contain;background:#fff">
                            @else
                                <span class="mc-av b"><i class="bi bi-heart-pulse"></i></span>
                            @endif
                            <span><b>{{ $service->title }}</b></span>
                        </div>
                    </td>
                    <td>{{ Str::limit($service->description, 60) ?: '–' }}</td>
                    <td>{{ $service->order }}</td>
                    <td>
                        @if($service->status)
                            <span class="mc-pill p-active"><i></i>Active</span>
                        @else
                            <span class="mc-pill p-inactive"><i></i>Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.services.edit', $service->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6"><div class="mc-empty"><b>Nothing on this chart</b>No services found. Add the first card to populate the site.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
