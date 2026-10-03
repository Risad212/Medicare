@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Taxonomy</p>
        <h1 class="mc-title">Edit <em>category</em></h1>
        <p class="mc-sub">Rename or reassign.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">CA</div>
            <div class="k">Currently editing</div>
            <h2>{{ $category->name }}</h2>
            <p>Category topic</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Profile</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Profile</h3><p>Name the category.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Name <i class="req">*</i></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}">
                        @error('name') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update category</button>
                <a href="{{ route('admin.categories.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
