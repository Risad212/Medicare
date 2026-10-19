<div class="breadcrumb">
    <div class="container">
        <div class="col-xs-12">
            <div class="breadcrumb-content">
                <div class="breadcrumb-text-wrapper">
                    <h1 class="page-name">{{ $title }}</h1>
                    <ul class="breadcrumb-list">
                        <li><a href="{{ route('home') }}">{{ __('messages.nav.home') }}</a></li>
                        <li>{{ $title }}</li>
                    </ul>
                </div><!--/.breadcrumb-text-wrapper-->
            </div><!--/.breadcrumb-content-->
        </div><!--/.col-xs-12-->
    </div>
</div>