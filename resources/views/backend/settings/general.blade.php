@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">
            <div class="tile-body">

                <h3 class="mb-4">General Settings</h3>

                <form action="{{ route('settings.general.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- SITE IDENTITY --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Site Identity</h5>
                        </div>

                        <div class="card-body">

                            <div class="form-group mb-2">
                                <label class="mb-2">Site Name</label>

                                <input type="text"
                                       name="site_name"
                                       class="form-control"
                                       value="{{ $setting->site_name ?? '' }}">
                            </div>

                            <div class="row">

                                <div class="col-md-6">
                                    <label class="mb-2">Site Logo Recommended size: 200x60 px</label>

                                    <div class="border rounded p-3 text-center">

                                        @if(!empty($setting->logo))
                                            <img src="{{ asset('storage/'.$setting->logo) }}" width="100">
                                        @endif

                                        <input type="file"
                                               name="logo"
                                               class="form-control-file">

                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <label class="mb-2">Site Favicon (Recommended size: 32×32 px, PNG or ICO)</label>

                                    <div class="border rounded p-3 text-center">

                                        @if(!empty($setting->favicon))
                                            <img src="{{ asset('storage/'.$setting->favicon) }}" width="50">
                                        @endif

                                        <input type="file"
                                               name="favicon"
                                               class="form-control-file">

                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>


                    {{-- HEADER --}}
                    <div class="card mb-4">

                        <div class="card-header">
                            <h5 class="mb-0">Header</h5>
                        </div>


                        <div class="card-body">


                            <div class="form-group mb-2">

                                <label class="mb-2">Address</label>

                                <textarea name="address"
                                          class="form-control"
                                          rows="3">{{ $setting->address ?? '' }}</textarea>

                            </div>


                            <div class="form-group mb-2">

                                <label class="mb-2">Working Hours</label>

                                <textarea name="working_hours"
                                          class="form-control"
                                          rows="3">{{ $setting->working_hours ?? '' }}</textarea>

                            </div>


                            <h6 class="mt-4 mb-3">Social Links</h6>


                            <div class="row">


                                <div class="col-md-6 form-group mb-2">
                                    <label class="mb-2">Facebook</label>

                                    <input name="facebook"
                                           class="form-control"
                                           value="{{ $setting->facebook ?? '' }}">
                                </div>


                                <div class="col-md-6 form-group mb-2">
                                    <label class="mb-2">Twitter / X</label>

                                    <input name="twitter"
                                           class="form-control"
                                           value="{{ $setting->twitter ?? '' }}">
                                </div>


                                <div class="col-md-6 form-group">
                                    <label class="mb-2">LinkedIn</label>

                                    <input name="linkedin"
                                           class="form-control"
                                           value="{{ $setting->linkedin ?? '' }}">
                                </div>


                                <div class="col-md-6 form-group">
                                    <label class="mb-2">YouTube</label>

                                    <input name="youtube"
                                           class="form-control"
                                           value="{{ $setting->youtube ?? '' }}">
                                </div>


                            </div>


                        </div>

                    </div>


                    {{-- FOOTER --}}
                    <div class="card mb-4">

                    <div class="card-header">
                        <h5 class="mb-0">Footer</h5>
                    </div>

                    <div class="card-body">

                        <!-- Footer Logo -->
                        <div class="form-group mb-3">
                            <label class="mb-2">Footer Logo</label>

                            @if(!empty($setting->footer_logo))
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$setting->footer_logo) }}" width="150">
                                </div>
                            @endif

                            <input type="file"
                                name="footer_logo"
                                class="form-control-file">
                        </div>

                        <!-- Phone + Email in one line -->
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="mb-2">Phone Number</label>
                                    <input type="text"
                                        name="phone"
                                        class="form-control"
                                        value="{{ $setting->phone ?? '' }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="mb-2">Email</label>
                                    <input type="email"
                                        name="email"
                                        class="form-control"
                                        value="{{ $setting->email ?? '' }}">
                                </div>
                            </div>

                        </div>

                        <!-- Footer Description -->
                        <div class="form-group mb-3">
                            <label class="mb-2">Short Description</label>
                            <textarea name="footer_description"
                                    class="form-control"
                                    rows="4">{{ $setting->footer_description ?? '' }}</textarea>
                        </div>

                        <!-- Copyright -->
                        <div class="form-group">
                            <label class="mb-2">Copyright Text</label>
                            <input name="copyright"
                                class="form-control"
                                value="{{ $setting->copyright ?? '' }}">
                        </div>

                    </div>

                </div>


                    <button type="submit" class="btn btn-primary">
                        Save General Settings
                    </button>


                </form>

            </div>
        </div>

    </div>
</div>

@endsection