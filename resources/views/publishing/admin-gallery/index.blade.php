@extends('backend.layouts.app')

@section('content')
<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">Photo <em>gallery</em></h1>
        <p class="mc-sub">Images shown on the public gallery page.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.gallery.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add photo</a>
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
                    <th>Preview</th>
                    <th>Caption</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($images as $image)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <img src="{{ asset('storage/' . $image->image) }}" alt="" style="width:74px;height:52px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0">
                    </td>
                    <td>{{ $image->caption ?? '–' }}</td>
                    <td class="mc-num">{{ $image->sort_order }}</td>
                    <td>
                        @if($image->status == 1)
                            <span class="mc-pill p-published"><i></i>Published</span>
                        @else
                            <span class="mc-pill p-draft"><i></i>Draft</span>
                        @endif
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.gallery.edit', $image->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.gallery.destroy', $image->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this photo? The image file is removed too.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6"><div class="mc-empty"><b>Nothing on this chart</b>No photos yet. Upload the first gallery image.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
