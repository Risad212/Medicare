@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Edit Comment</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.comments.update', $comment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $comment->name) }}">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $comment->email) }}">
                    </div>

                    <div class="form-group">
                        <label>Comment</label>
                        <textarea name="comment" class="form-control" rows="5">{{ old('comment', $comment->comment) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Status</label><br>
                        <input type="checkbox" name="status" value="1" {{ $comment->status ? 'checked' : '' }}> Approved
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Comment
                    </button>
                    <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">Cancel</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection