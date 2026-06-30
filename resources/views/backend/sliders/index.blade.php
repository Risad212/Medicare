@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

<div class="tile">
    <h3 class="tile-title">All Sliders</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Button Text</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sliders as $slider)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if($slider->bg_image)
                            <img src="{{ asset('storage/' . $slider->bg_image) }}" width="150">
                        @else
                            No Image
                        @endif
                    </td>
                    <td>{{ $slider->title }}</td>
                    <td>{{ Str::limit($slider->description, 60) }}</td>
                    <td>{{ $slider->button_text }}</td>
                    <td>
                        <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
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
                    <td colspan="6" class="text-center">No sliders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
  </div>
</div>

@endsection