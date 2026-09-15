@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">Bl<em>og</em></h1>
        <p class="mc-sub">Patient education pipeline — drafts become trust.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blogs.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> New post</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><svg viewBox="0 0 400 22" preserveAspectRatio="none"><polyline points="0,11 60,11 70,11 76,11 82,3 88,19 94,11 150,11 160,11 166,11 172,4 178,18 184,11 260,11 400,11" fill="none" stroke="#05d3b0" stroke-width="1.6"/></svg><span>{{ $blogs->count() }} records</span></div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Post</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Tag</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($blogs as $blog)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="mc-who">
                            @if($blog->image)
                                <img class="mc-av" src="{{ asset('storage/' . $blog->image) }}" alt="">
                            @else
                                <span class="mc-av a">✚</span>
                            @endif
                            <span><b>{{ Str::limit($blog->title, 50) }}</b><span class="mc-sub2">{{ Str::limit($blog->excerpt, 60) }}</span></span>
                        </div>
                    </td>
                    <td>{{ $blog->author ?? '–' }}</td>
                    <td>{{ $blog->category ?? '–' }}</td>
                    <td>{{ $blog->tags ?? '–' }}</td>
                    <td>
                        @if($blog->status == 1)
                            <span class="mc-pill p-published"><i></i>Published</span>
                        @else
                            <span class="mc-pill p-draft"><i></i>Draft</span>
                        @endif
                    </td>
                    <td class="mc-num">{{ $blog->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No posts yet. Publish the first one to start building trust.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
