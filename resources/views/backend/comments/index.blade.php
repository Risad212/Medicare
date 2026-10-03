@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">Blog <em>Comments</em></h1>
        <p class="mc-sub">Moderate user feedback on published posts.</p>
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
                    <th>Blog</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comments as $comment)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ Str::limit($comment->blog->title, 30) }}</td>
                    <td>{{ $comment->name }}</td>
                    <td>{{ $comment->email }}</td>
                    <td>{{ Str::limit($comment->comment, 50) }}</td>
                    <td>
                        @if($comment->status == 1)
                            <span class="mc-pill p-active"><i></i>Approved</span>
                        @else
                            <span class="mc-pill p-pending"><i></i>Pending</span>
                        @endif
                    </td>
                    <td class="mc-num">{{ $comment->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="mc-acts">
                            @if($comment->status == 0)
                            <form action="{{ route('admin.comments.update', $comment->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="mc-btn sm dark"><i class="bi bi-check"></i> Approve</button>
                            </form>
                            @endif
                            <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No comments found. They appear when readers engage with your posts.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
