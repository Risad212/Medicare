@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="#" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- About Section --}}
            <div class="tile mb-4">
                <div class="tile-title-w-btn">
                    <h3 class="title">About Section</h3>
                </div>
                <div class="tile-body">
                    <div class="form-group mb-2">
                        <label>Title</label>
                        <input type="text" name="about_title" class="form-control"
                            value="{{ old('about_title', $setting->about_title ?? '') }}">
                    </div>
                    <div class="form-group mb-2">
                        <label>Description</label>
                        <textarea name="about_description" class="form-control" rows="4">{{ old('about_description', $setting->about_description ?? '') }}</textarea>
                    </div>
                      <div class="form-group mb-2">
                         <label>Button Text</label>
                         <input type="text" name="about_button_text" class="form-control"
                                    value="{{ old('about_button_text', $setting->about_button_text ?? '') }}">
                     </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Left Top (Recommended size: 370 × 270 px)</label>
                                @if(!empty($setting->about_image_one))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $setting->about_image_one) }}" height="80" class="img-thumbnail">
                                    </div>
                                @endif
                                <input type="file" name="about_image_one" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Left Bottom (Recommended upload size: 370 × 270 px)</label>
                                @if(!empty($setting->about_image_two))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $setting->about_image_two) }}" height="80" class="img-thumbnail">
                                    </div>
                                @endif
                                <input type="file" name="about_image_two" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Right (Recommended image size: 501 × 750 px)</label>
                                @if(!empty($setting->about_image_three))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $setting->about_image_three) }}" height="80" class="img-thumbnail">
                                    </div>
                                @endif
                                <input type="file" name="about_image_three" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Counter Up Section --}}
           <div class="tile mb-4">
            <div class="tile-title-w-btn">
                <h3 class="title">Counter Up Section</h3>
            </div>

       <div class="tile-body">

        <!-- 1 -->
        <div class="row mb-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" name="counter_one_text" class="form-control"
                        value="{{ old('counter_one_text', $setting->counter_one_text ?? 'Hospital Rooms') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Number</label>
                    <input type="number" name="counter_one_number" class="form-control"
                        value="{{ old('counter_one_number', $setting->counter_one_number ?? '3468') }}">
                </div>
            </div>
        </div>

        <!-- 2 -->
        <div class="row mb-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" name="counter_two_text" class="form-control"
                        value="{{ old('counter_two_text', $setting->counter_two_text ?? 'Specialist Doctors') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Number</label>
                    <input type="number" name="counter_two_number" class="form-control"
                        value="{{ old('counter_two_number', $setting->counter_two_number ?? '557') }}">
                </div>
            </div>
        </div>

        <!-- 3 -->
        <div class="row mb-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" name="counter_three_text" class="form-control"
                        value="{{ old('counter_three_text', $setting->counter_three_text ?? 'Happy Patients') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Number</label>
                    <input type="number" name="counter_three_number" class="form-control"
                        value="{{ old('counter_three_number', $setting->counter_three_number ?? '2000') }}">
                </div>
            </div>
        </div>

        <!-- 4 -->
            <div class="row mb-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" name="counter_four_text" class="form-control"
                            value="{{ old('counter_four_text', $setting->counter_four_text ?? 'Years of Experience') }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Number</label>
                        <input type="number" name="counter_four_number" class="form-control"
                            value="{{ old('counter_four_number', $setting->counter_four_number ?? '30') }}">
                    </div>
                </div>
             </div>
            </div>
        </div>

            {{-- Save Button --}}
            <div class="tile">
                <div class="tile-body">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Home Settings
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection