@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Pharmacy</p>
        <h1 class="mc-title">Medi<em>cines</em></h1>
        <p class="mc-sub">Stock levels, prices and expiry.</p>
    </div>
    <div class="mc-head-acts">
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.medicines.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add medicine</a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="mc-ecg"><span>Pharmacy stock</span><span>{{ $lowStockCount }} low-stock</span></div>

<div class="mc-bar">
    <form action="{{ route('admin.medicines.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search medicine…" value="{{ request('search') }}" autocomplete="off">
    </form>
    <a href="{{ route('admin.medicines.index', ['low_stock' => 1]) }}" class="mc-btn sm ghost">Low stock only</a>
    @if(request('low_stock'))
        <a href="{{ route('admin.medicines.index') }}" class="mc-btn sm ghost">Clear</a>
    @endif
</div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicine</th>
                    <th>Stock</th>
                    <th>Price</th>
                    <th>Expiry</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($medicines as $key => $medicine)
                <tr>
                    <td class="mc-idx">{{ $medicines->firstItem() + $key }}</td>
                    <td>
                        <b>{{ $medicine->name }}</b>
                        @if($medicine->generic_name)<span class="mc-sub2">{{ $medicine->generic_name }}</span>@endif
                    </td>
                    <td class="mc-num">
                        {{ $medicine->stock_quantity }} {{ $medicine->unit }}
                        @if($medicine->is_low_stock)<div><span class="mc-pill p-pending">Low stock</span></div>@endif
                    </td>
                    <td class="mc-num">{{ $medicine->unit_price }}</td>
                    <td class="mc-num">
                        {{ $medicine->expiry_date?->format('Y-m-d') ?? '—' }}
                        @if($medicine->is_expired)<div><span class="mc-pill p-cancelled">Expired</span></div>@endif
                    </td>
                    <td>
                        @if(auth()->user()->role === 'admin')
                            <div class="mc-acts">
                                <a href="{{ route('admin.medicines.edit', $medicine->id) }}" class="mc-btn sm dark">Edit</a>
                                <form action="{{ route('admin.medicines.destroy', $medicine->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mc-btn sm danger-ghost" onclick="return confirm('Delete this medicine?')">Delete</button>
                                </form>
                            </div>
                        @else
                            <span class="text-mut">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6"><div class="mc-empty"><b>Empty shelf</b>No medicines in stock records yet.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($medicines->hasPages())
        <div class="px-4.5 py-4">{{ $medicines->links() }}</div>
    @endif
</div>

@endsection
