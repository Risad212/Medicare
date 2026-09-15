@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Laboratory</p>
        <h1 class="mc-title">Lab <em>tests</em></h1>
        <p class="mc-sub">All registered lab tests, categories and pricing.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.lab-tests.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add New Test</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-bar">
    <form action="{{ route('admin.lab-tests.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search test or category..." value="{{ request('search') }}" autocomplete="off">
    </form>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Normal Range</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($labTests as $key => $test)
                    <tr>
                        <td class="mc-idx">{{ $labTests->firstItem() + $key }}</td>
                        <td>
                            <b class="text-ink">{{ $test->name }}</b>
                            @if($test->description)
                                <div class="text-xs text-mut">{{ \Illuminate\Support\Str::limit(strip_tags($test->description), 60) }}</div>
                            @endif
                        </td>
                        <td>{{ $test->category ?? 'N/A' }}</td>
                        <td class="mc-num">{{ number_format($test->price, 2) }}</td>
                        <td>{{ $test->normal_range ?? 'N/A' }}</td>
                        <td>{{ $test->unit ?? 'N/A' }}</td>
                        <td>
                            @if($test->status)
                                <span class="mc-pill p-active"><i></i>Active</span>
                            @else
                                <span class="mc-pill p-inactive"><i></i>Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="mc-acts">
                                <a href="{{ route('admin.lab-tests.edit', $test->id) }}" class="mc-btn sm"><i class="bi bi-pencil"></i> Edit</a>
                                <form action="{{ route('admin.lab-tests.destroy', $test->id) }}" method="POST"  onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No lab tests found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $labTests->firstItem() ?? 0 }}–{{ $labTests->lastItem() ?? 0 }} of {{ $labTests->total() }}</span>
        {{ $labTests->links() }}
    </div>
</div>

@endsection
