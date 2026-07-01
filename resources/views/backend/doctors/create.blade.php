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

                <form action="{{ route('admin.doctors.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group mb-2">
                                <label class="mb-2">
                                    Doctor Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       placeholder="Enter doctor name">
                            </div>


                            <div class="form-group mb-2">
                                <label class="mb-2">Degree</label>

                                <textarea name="degree"
                                          class="form-control"
                                          rows="3"
                                          placeholder="MBBS, FCPS"></textarea>
                            </div>


                            <div class="form-group mb-2">
                                <label class="mb-2">Department</label>

                                <input type="text"
                                       name="department"
                                       class="form-control"
                                       placeholder="Cardiology">
                            </div>


                            <div class="form-group mb-2">
                                <label class="mb-2">Specialist</label>

                                <textarea name="specialist"
                                          class="form-control"
                                          rows="3"
                                          placeholder="Heart specialist"></textarea>
                            </div>


                        </div>


                        <div class="col-md-6">


                            <div class="form-group mb-2">

                                <label class="mb-2">Doctor Image</label>

                                <div class="custom-file">
                                    <input type="file"
                                           name="image"
                                           class="custom-file-input">

                                    <label class="custom-file-label">
                                        Choose image
                                    </label>
                                </div>

                            </div>



                            <div class="form-group mb-2">
                                <label class="mb-2">Phone</label>

                                <input type="text"
                                       name="phone"
                                       class="form-control"
                                       placeholder="+880">
                            </div>



                            <div class="form-group mb-2">
                                <label class="mb-2">Availability</label>

                                <input type="text"
                                       name="availability"
                                       class="form-control"
                                       placeholder="Everyday 9AM - 8PM">
                            </div>



                            <div class="form-group mb-2">

                                <label class="mb-2">Status</label>

                                <select name="status" class="form-control">

                                    <option value="1">
                                        Active
                                    </option>

                                    <option value="0">
                                        Inactive
                                    </option>

                                </select>

                            </div>


                        </div>


                        <div class="col-md-12">


                            <div class="form-group mb-2">

                                <label class="mb-2">Services</label>

                                <textarea name="services"
                                          class="form-control"
                                          rows="4"
                                          placeholder="Consultation, Surgery, Treatment"></textarea>

                            </div>


                        </div>


                    </div>



                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Save Doctor
                    </button>


                </form>

            </div>

        </div>

    </div>
</div>

@endsection