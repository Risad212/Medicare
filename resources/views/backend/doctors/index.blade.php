@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">

            <div class="tile-title-w-btn d-flex justify-content-between align-items-center mb-3">

                <h3 class="tile-title mb-0">All Doctors</h3>

                <form action="{{ route('admin.doctors.index') }}" method="GET" class="d-flex">
                    <input type="text"
                           name="search"
                           style="width: 300px;"
                           class="form-control me-2"
                           placeholder="Search doctor..."
                           value="{{ request('search') }}">

                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>
                </form>

            </div>


            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif


            <div class="table-responsive">

                <table class="table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Specialist</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>


                    <tbody>

                    @forelse($doctors as $doctor)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            @if($doctor->image)
                                <img src="{{ asset('storage/'.$doctor->image) }}" width="80">
                            @else
                                No Image
                            @endif
                        </td>

                        <td>{{ $doctor->name }}</td>

                        <td>{{ $doctor->department }}</td>

                        <td>{{ $doctor->specialist }}</td>

                        <td>{{ $doctor->phone }}</td>


                        <td>
                            @if($doctor->status == 1)
                                <span class="status-active">
                                    Active
                                </span>
                            @else
                                <span class="status-inactive">
                                    Inactive
                                </span>
                            @endif
                        </td>


                        <td>

                            <a href="{{ route('admin.doctors.edit',$doctor->id) }}"
                               class="btn btn-sm btn-info">

                                <i class="bi bi-pencil"></i> Edit

                            </a>


                            <form action="{{ route('admin.doctors.destroy',$doctor->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Are you sure?')">

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
                        <td colspan="8" class="text-center">
                            No doctors found.
                        </td>
                    </tr>

                    @endforelse


                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

@endsection