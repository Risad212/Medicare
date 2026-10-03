@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">Edit <em>comment</em></h1>
        <p class="mc-sub">Review, correct, or moderate user feedback.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.comments.update', $comment->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">CM</div>
            <div class="k">Currently editing</div>
            <h2>{{ Str::limit($comment->name, 30) }}</h2>
            <p>{{ $comment->email }}</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Details</li>
                <li><span class="n">2</span>Status</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Details</h3><p>Who said what.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $comment->name) }}">
                    </div>
                    <div class="mc-f full">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $comment->email) }}">
                    </div>
                    <div class="mc-f full">
                        <label>Comment</label>
                        <textarea name="comment" class="form-control" rows="5">{{ old('comment', $comment->comment) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Status</h3><p>Approve or keep pending.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Status</label>
                        <label class="mc-check"><input type="checkbox" name="status" value="1" {{ $comment->status ? 'checked' : '' }}> Approved</label>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update comment</button>
                <a href="{{ route('admin.comments.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
