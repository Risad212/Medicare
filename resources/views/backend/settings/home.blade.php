@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('settings.home.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- About Section --}}
            <div class="tile mb-4">
                <div class="tile-title-w-btn">
                    <h3 class="title">About Section</h3>
                </div>
                    <div class="tile-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="mb-2">Title</label>
                                <input type="text" name="about_title" class="form-control"
                                    value="{{ old('about_title', $homeSetting->about_title ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="mb-2">Button Text</label>
                                <input type="text" name="about_button_text" class="form-control"
                                    value="{{ old('about_button_text', $homeSetting->about_button_text ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="mb-2">Description</label>
                        <textarea name="about_description" class="form-control" rows="4">{{ old('about_description', $homeSetting->about_description ?? '') }}</textarea>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-2">Left Top (Recommended size: 370 × 270 px)</label>
                                @if(!empty($homeSetting->about_image_one))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $homeSetting->about_image_one) }}" width="10%" class="img-thumbnail">
                                    </div>
                                @endif
                                <input type="file" name="about_image_one" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-2">Left Bottom (Recommended upload size: 370 × 270 px)</label>
                                @if(!empty($homeSetting->about_image_two))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $homeSetting->about_image_two) }}" width="10%" class="img-thumbnail">
                                    </div>
                                @endif
                                <input type="file" name="about_image_two" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="mb-2">Right (Recommended image size: 501 × 750 px)</label>
                                @if(!empty($homeSetting->about_image_three))
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $homeSetting->about_image_three) }}" width="10%" class="img-thumbnail">
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
                    <label class="mb-2">Label</label>
                    <input type="text" name="counter_one_text" class="form-control"
                        value="{{ old('counter_one_text', $homeSetting->counter_one_text ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="mb-2">Number</label>
                    <input type="number" name="counter_one_number" class="form-control"
                        value="{{ old('counter_one_number', $homeSetting->counter_one_number ?? '') }}">
                </div>
            </div>
        </div>

        <!-- 2 -->
        <div class="row mb-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="mb-2">Label</label>
                    <input type="text" name="counter_two_text" class="form-control"
                        value="{{ old('counter_two_text', $homeSetting->counter_two_text ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="mb-2">Number</label>
                    <input type="number" name="counter_two_number" class="form-control"
                        value="{{ old('counter_two_number', $homeSetting->counter_two_number ?? '') }}">
                </div>
            </div>
        </div>

        <!-- 3 -->
        <div class="row mb-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="mb-2">Label</label>
                    <input type="text" name="counter_three_text" class="form-control"
                        value="{{ old('counter_three_text', $homeSetting->counter_three_text ?? '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="mb-2">Number</label>
                    <input type="number" name="counter_three_number" class="form-control"
                        value="{{ old('counter_three_number', $homeSetting->counter_three_number ?? '') }}">
                </div>
            </div>
        </div>

        <!-- 4 -->
            <div class="row mb-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="mb-2">Label</label>
                        <input type="text" name="counter_four_text" class="form-control"
                            value="{{ old('counter_four_text', $homeSetting->counter_four_text ?? '') }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="mb-2">Number</label>
                        <input type="number" name="counter_four_number" class="form-control"
                            value="{{ old('counter_four_number', $homeSetting->counter_four_number ?? '') }}">
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