@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">

            <div class="tile-title-w-btn">
                <h3 class="title">Create Doctor</h3>
            </div>

            <hr>

            <div class="tile-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.doctors.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group mb-2">
                                <label class="mb-2">Doctor Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Enter doctor name" value="{{ old('name') }}">
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Degree</label>
                                <textarea name="degree" class="form-control" rows="3"
                                    placeholder="MBBS, FCPS">{{ old('degree') }}</textarea>
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Department</label>
                                <select name="department" class="form-control">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}"
                                            {{ old('department') == $department->name ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Specialist</label>
                                <textarea name="specialist" class="form-control" rows="3"
                                    placeholder="Heart specialist">{{ old('specialist') }}</textarea>
                            </div>

                             <div class="form-group mb-2">
                                <label class="mb-2">Services</label>
                                <textarea name="services" class="form-control" rows="4"
                                    placeholder="Consultation, Surgery, Treatment">{{ old('services') }}</textarea>
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group mb-2">
                                <label class="mb-2">Doctor Image</label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input">
                                    <label class="custom-file-label">Choose image</label>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="+880" value="{{ old('phone') }}">
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Availability</label>
                                <input type="text" name="availability" class="form-control"
                                    placeholder="Everyday 9AM - 8PM" value="{{ old('availability') }}">
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Status</label>
                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="doctor@email.com" value="{{ old('email') }}">
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group mb-2">
                                <label class="mb-2">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Min 6 characters">
                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Doctor
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>

@endsection