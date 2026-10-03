@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Frontend</p>
        <h1 class="mc-title">All <em>Sliders</em></h1>
        <p class="mc-sub">Homepage hero banners and call-to-action slides.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.sliders.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add slide</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Button Text</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sliders as $slider)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        @if($slider->bg_image)
                            <img src="{{ asset('storage/' . $slider->bg_image) }}" class="h-16 w-28 rounded-lg border border-line object-cover" alt="">
                        @else
                            <span class="mc-pill p-draft"><i></i>No image</span>
                        @endif
                    </td>
                    <td>{{ $slider->title }}</td>
                    <td>{{ Str::limit($slider->description, 60) }}</td>
                    <td>{{ $slider->button_text }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6"><div class="mc-empty"><b>Nothing on this chart</b>No sliders found. Add the first one to populate the homepage hero.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
