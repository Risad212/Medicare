@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">Blog Comments</h3>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Blog</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comments as $comment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ Str::limit($comment->blog->title, 30) }}</td>
                            <td>{{ $comment->name }}</td>
                            <td>{{ $comment->email }}</td>
                            <td>{{ Str::limit($comment->comment, 50) }}</td>
                            <td>
                            @if($comment->status == 1)
                                <span class="status-active">
                                    Approved
                                </span>
                            @else
                                <span class="status-inactive comment">
                                    Pending
                                </span>
                            @endif
                        </td>
                            <td>{{ $comment->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($comment->status == 0)
                                <form action="{{ route('admin.comments.update', $comment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check"></i> Approve
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No comments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection