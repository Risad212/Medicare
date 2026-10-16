@extends('backend.layouts.app')

@section('content')
<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Taxonomy</p>
        <h1 class="mc-title">All <em>Tags</em></h1>
        <p class="mc-sub">Keywords used to classify blog posts.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.tags.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add new tag</a>
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
                    <th>Name</th>
                    <th>Slug</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tags as $tag)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $tag->name }}</td>
                    <td>{{ $tag->slug }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.tags.edit', $tag->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4"><div class="mc-empty"><b>Nothing on this chart</b>No tags found. Add the first one to start categorizing.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
