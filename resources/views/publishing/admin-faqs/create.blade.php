@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">New <em>question</em></h1>
        <p class="mc-sub">A patient-facing question and its answer.</p>
    </div>
</div>

<form action="{{ route('admin.faqs.store') }}" method="POST">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">FQ</div>
            <div class="k">New record</div>
            <h2>Untitled question</h2>
            <p>Keep it short — one clear question per entry.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Question</li>
                <li><span class="n">2</span>Answer</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Question</h3><p>What patients ask.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Question <i class="req">*</i></label>
                        <input type="text" name="question" class="form-control" maxlength="255" placeholder="e.g. How do I book an appointment?" value="{{ old('question') }}">
                        @error('question') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Answer</h3><p>The reply shown to patients.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Answer <i class="req">*</i></label>
                        <textarea name="answer" rows="5" class="form-control" maxlength="5000" placeholder="e.g. Use the Appointment page or call reception...">{{ old('answer') }}</textarea>
                        @error('answer') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Display order</label>
                        <input type="number" name="sort_order" class="form-control" min="0" max="9999" placeholder="0" value="{{ old('sort_order', 0) }}">
                        @error('sort_order') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Status <i class="req">*</i></label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Published</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Draft</option>
                        </select>
                        @error('status') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save question</button>
                <a href="{{ route('admin.faqs.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
