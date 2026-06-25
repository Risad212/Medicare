@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">
            <h2 class="tile-title">Home Page</h2>

            <h4 class="section-title mb-2">About Section</h4>
            <div class="tile-body">

                <form action="{{ route('page-sections.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- CONTENT FIELDS IN ONE ROW -->
                    <div class="form-row column-2">

                        <div class="form-group col-md-12">
                            <label class="mb-2">Title</label>
                            <input type="text" name="content[title]" class="form-control"
                             value="{{ $section->content['title'] ?? '' }}">
                        </div>

                         <div class="form-group col-md-12">
                            <label class="mb-2">Description</label>
                           <textarea name="description" class="form-control" rows="5"></textarea>
                        </div>

                        <div class="form-group col-md-12">
                            <label class="mb-2">Button Text</label>
                            <input type="text" name="content[button_text]" class="form-control">
                        </div>

                        <div class="form-group col-md-12">
                            <label class="mb-2">Button Link</label>
                            <input type="text" name="content[button_link]" class="form-control">
                        </div>
                    </div>

                    <hr>

                    <!-- IMAGES IN ONE ROW -->
                    <div class="form-row mb-2 column-3">

                        <div class="form-group col-md-12">
                            <label class="mb-2 d-block">Left Top Image</label>
                            <input id="left_top_image" type="file" name="left_top_image" class="form-control-file d-block mb-3">
                            <img id="preview_left_top" width="50%">
                        </div>

                        <div class="form-group col-md-12">
                            <label class="mb-2 d-block">Left Bottom Image</label>
                            <input id="left_bottom_image" type="file" name="left_bottom_image" class="form-control-file d-block mb-3">
                            <img id="preview_left_bottom" width="50%">
                        </div>

                        <div class="form-group col-md-12">
                            <label class="mb-2 d-block">Right Image</label>
                            <input id="right_image" type="file" name="right_image" class="form-control-file d-block mb-3">
                            <img id="preview_right_image" width="50%">
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">
                        Save Section
                    </button>

                </form>

            </div>
        </div>

    </div>
</div>

@endsection