@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">

            <div class="tile-title-w-btn">
                <h3 class="title">Edit Doctor</h3>
            </div>


            <div class="tile-body">


                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif



                <form action="{{ route('admin.doctors.update',$doctor->id) }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')


                    <div class="row">


                        <div class="col-md-6">


                            <div class="form-group mb-2">
                                <label>Doctor Name</label>

                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name',$doctor->name) }}">
                            </div>


                            <div class="form-group mb-2">
                                <label>Degree</label>

                                <textarea name="degree"
                                          class="form-control"
                                          rows="3">{{ old('degree',$doctor->degree) }}</textarea>
                            </div>


                           <div class="form-group mb-2">
                                <label>Department</label>
                                <select name="department" class="form-control">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}"
                                            {{ $doctor->department == $department->name ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="form-group mb-2">
                                <label>Specialist</label>

                                <textarea name="specialist"
                                          class="form-control"
                                          rows="3">{{ old('specialist',$doctor->specialist) }}</textarea>
                            </div>


                        </div>



                        <div class="col-md-6">


                            <div class="form-group mb-2">

                                <label>Doctor Image</label><br>


                                @if($doctor->image)

                                    <img src="{{ asset('storage/'.$doctor->image) }}"
                                         width="120"
                                         class="mb-2 d-block">

                                @endif


                                <input type="file"
                                       name="image"
                                       class="form-control">

                            </div>



                            <div class="form-group mb-2">

                                <label>Phone</label>

                                <input type="text"
                                       name="phone"
                                       class="form-control"
                                       value="{{ old('phone',$doctor->phone) }}">

                            </div>



                            <div class="form-group mb-2">

                                <label>Availability</label>

                                <input type="text"
                                       name="availability"
                                       class="form-control"
                                       value="{{ old('availability',$doctor->availability) }}">

                            </div>



                            <div class="form-group mb-2">

                                <label>Status</label>

                                <select name="status" class="form-control">

                                    <option value="1" {{ $doctor->status == 1 ? 'selected':'' }}>
                                        Active
                                    </option>

                                    <option value="0" {{ $doctor->status == 0 ? 'selected':'' }}>
                                        Inactive
                                    </option>

                                </select>

                            </div>


                        </div>



                        <div class="col-md-12">


                            <div class="form-group mb-2">

                                <label>Services</label>

                                <textarea name="services"
                                          class="form-control"
                                          rows="4">{{ old('services',$doctor->services) }}</textarea>

                            </div>


                        </div>


                    </div>



                    <button type="submit" class="btn btn-primary">

                        <i class="bi bi-save"></i>
                        Update Doctor

                    </button>


                    <a href="{{ route('admin.doctors.index') }}"
                       class="btn btn-secondary">

                        Cancel

                    </a>


                </form>


            </div>

        </div>

    </div>
</div>


@endsection