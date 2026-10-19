@extends('backend.layouts.app')

@section('content')
<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">Frequently asked <em>questions</em></h1>
        <p class="mc-sub">Answers shown on the public FAQ page.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.faqs.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add new question</a>
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
                    <th>Question</th>
                    <th>Answer</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faqs as $faq)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $faq->question }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($faq->answer, 80) }}</td>
                    <td class="mc-num">{{ $faq->sort_order }}</td>
                    <td>
                        @if($faq->status == 1)
                            <span class="mc-pill p-published"><i></i>Published</span>
                        @else
                            <span class="mc-pill p-draft"><i></i>Draft</span>
                        @endif
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6"><div class="mc-empty"><b>Nothing on this chart</b>No questions yet. Add the first FAQ to start helping patients.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
