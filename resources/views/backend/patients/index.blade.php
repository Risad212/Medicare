@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">

            <div class="tile-title-w-btn d-flex justify-content-between align-items-center mb-3">

                <h3 class="tile-title mb-0">All Patients</h3>

                <form action="{{ route('admin.patients.index') }}" method="GET" class="d-flex">
                    <input type="text"
                           name="search"
                           style="width: 300px;"
                           class="form-control me-2"
                           placeholder="Search patient..."
                           value="{{ request('search') }}">
                </form>

            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($patients as $key => $patient)

                        <tr>

                            <td>
                                {{ $patients->firstItem() + $key }}
                            </td>

                            <td>
                                {{ $patient->name }}
                            </td>

                            <td>
                                {{ $patient->email }}
                            </td>

                            <td>
                                {{ $patient->phone ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $patient->gender ? ucfirst($patient->gender) : 'N/A' }}
                            </td>

                            <td>
                                {{ $patient->date_of_birth ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $patient->created_at->format('Y-m-d') }}
                            </td>

                            <td>

                               <a href="{{ route('admin.patients.show', $patient->id) }}"
                                  class="btn btn-sm btn-info">
                                    View
                                </a>
                                <a href="{{ route('admin.patients.edit', $patient->id) }}"
                                   class="btn btn-sm btn-primary">
                                    Edit
                                </a>

                                <form action="{{ route('admin.patients.destroy', $patient->id) }}"
                                      method="POST"
                                      style="display:inline;">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this patient?')">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center">
                                No patients found.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Pagination --}}
            <div class="mt-3 table-pagination">
                {{ $patients->links() }}
            </div>

        </div>

    </div>
</div>

@endsection