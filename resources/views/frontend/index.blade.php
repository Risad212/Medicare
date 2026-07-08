@extends('frontend.layouts.front-app')

@section('meta_title', $seo->meta_title ?? 'Medicare')
@section('meta_description', $seo->meta_description ?? '')
@section('meta_keywords', $seo->meta_keywords ?? '')

@section('front-content')
<!--============== START SLIDER SECTION ===============-->
<section class="slider-section">
    <div class="carousel-single-item owl-carousel owl-theme">

        @foreach($sliders as $slider)
        <div class="single-slider" style="background-image: url('{{ asset('storage/' . $slider->bg_image) }}');">
            <div class="slider-overlay">
                <div class="container">
                    <div class="title">
                        <h2>{{ $slider->title }}</h2>
                        <p>{{ $slider->description }}</p>
                        <div class="btn-title-home">
                            <a href="#" class="btn-title">{{ $slider->button_text }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</section>

<!--============== FEATURE SECTION ===============-->
<section class="feature-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="feature-card">
                    <span class="icon">
                        <svg fill="#000000" width="64px" height="64px" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M2.7695312 9C1.2425313 9 2.9605947e-16 10.233 0 11.75L0 36.25C0 37.767 1.2425312 39 2.7695312 39L5 39C5 42.309 7.691 45 11 45C14.309 45 17 42.309 17 39L31 39C31 42.309 33.691 45 37 45C40.309 45 43 42.309 43 39L47.230469 39C48.757469 39 50.001953 37.767 50.001953 36.25L50.001953 29.25C50.001953 27.324 48.615328 25.348875 48.486328 25.171875L39.457031 11.207031C39.435031 11.172031 39.408812 11.137469 39.382812 11.105469L39.203125 10.890625C38.430125 9.960625 37.630937 9 35.960938 9L2.7695312 9 z M 32 14L38.853516 14L45.992188 25L32 25L32 14 z M 14 17L16 17L16 21L20 21L20 23L16 23L16 27L14 27L14 23L10 23L10 21L14 21L14 17 z M 11 35C13.206 35 15 36.794 15 39C15 41.206 13.206 43 11 43C8.794 43 7 41.206 7 39C7 36.794 8.794 35 11 35 z M 37 35C39.206 35 41 36.794 41 39C41 41.206 39.206 43 37 43C34.794 43 33 41.206 33 39C33 36.794 34.794 35 37 35 z">
                                </path>
                            </g>
                        </svg>
                    </span>
                    <h4 class="title">Quality &amp; Safety</h4>
                    <p>Our Delmont hospital utilizes state of the art technology and employs a team of true experts.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="feature-card">
                    <span class="icon">
                        <svg viewBox="0 0 32 32" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:serif="http://www.serif.com/" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M7.001,15.004l6.999,-0.004c0.335,-0 0.646,-0.167 0.832,-0.445l0.168,-0.253c0,-0 -0,2.697 -0,2.697c-0,0.405 0.243,0.769 0.617,0.924c0.374,0.155 0.804,0.07 1.09,-0.216l2.704,-2.704c0,-0 5.583,0.036 5.583,0.036c0.551,0.003 1.002,-0.442 1.006,-0.994c0.004,-0.552 -0.442,-1.003 -0.994,-1.006l-6,-0.039c-0.267,-0.002 -0.524,0.104 -0.713,0.293l-1.293,1.292c0,0 0.001,-3.585 0.001,-3.585c0,-0.441 -0.288,-0.83 -0.71,-0.957c-0.422,-0.128 -0.877,0.035 -1.122,0.402l-1.704,2.555c-0,0 -6.466,0.004 -6.466,0.004c-0.552,0 -0.999,0.449 -0.999,1.001c0,0.552 0.449,0.999 1.001,0.999Z">
                                </path>
                                <path d="M15.01,25.997l0,1.996l-4.009,-0.005c-0.552,-0.001 -1,0.446 -1.001,0.998c-0.001,0.552 0.447,1.001 0.999,1.002l10,0.012c0.552,0.001 1,-0.447 1.001,-0.999c0.001,-0.552 -0.447,-1 -0.999,-1.001l-3.991,-0.005l0,-1.998l9.99,0c0.796,0 1.559,-0.316 2.121,-0.878c0.563,-0.563 0.879,-1.326 0.879,-2.122l-0,-13.997c0,-0.796 -0.316,-1.559 -0.879,-2.121c-0.562,-0.563 -1.325,-0.879 -2.121,-0.879l-22,0c-0.796,0 -1.559,0.316 -2.121,0.879c-0.563,0.562 -0.879,1.325 -0.879,2.121c-0,3.463 -0,10.535 -0,13.997c-0,0.796 0.316,1.559 0.879,2.122c0.562,0.562 1.325,0.878 2.121,0.878l10.01,0Zm12.99,-3.997l-0,0.997c0,0.266 -0.105,0.52 -0.293,0.708c-0.187,0.187 -0.442,0.292 -0.707,0.292c-0,0 -22,0 -22,0c-0.265,0 -0.52,-0.105 -0.707,-0.292c-0.188,-0.188 -0.293,-0.442 -0.293,-0.708l-0,-0.997l24,0Zm-0,-2l-24,0l0,-11c-0,-0.265 0.105,-0.52 0.293,-0.707c0.187,-0.188 0.442,-0.293 0.707,-0.293c0,0 22,-0 22,-0c0.265,0 0.52,0.105 0.707,0.293c0.188,0.187 0.293,0.442 0.293,0.707l-0,11Z">
                                </path>
                            </g>
                        </svg>
                    </span>
                    <h4 class="title">Leading Technology</h4>
                    <p>Our Delmont hospital utilizes state of the art technology and employs a team of true experts.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="feature-card">
                    <span class="icon">
                        <svg version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <g>
                                    <path class="st0" d="M110.547,411.844c-5.234,5.813-9.141,12.5-11.328,19.266c-1.531,4.766-2.266,9.469-2.266,13.875 c0,2.688,0.266,5.25,0.844,7.672c0.438,1.797,1.031,3.516,1.828,5.094c0.594,1.203,1.313,2.344,2.156,3.375 c1.266,1.531,2.828,2.859,4.688,3.781c1.844,0.938,3.969,1.438,6.125,1.422v-9.766c-0.453,0-0.797-0.063-1.125-0.156 c-0.578-0.156-1.047-0.422-1.578-0.891c-0.375-0.359-0.781-0.828-1.156-1.469c-0.563-0.922-1.094-2.203-1.453-3.734 c-0.359-1.547-0.563-3.344-0.563-5.328c0-3.297,0.578-7.047,1.797-10.891c1.141-3.531,2.953-7.188,5.328-10.656 c3.547-5.219,8.391-9.984,13.984-13.391c2.781-1.703,5.781-3.078,8.891-4.016c3.125-0.938,6.391-1.438,9.766-1.438 c4.5,0,8.813,0.906,12.844,2.531c6.031,2.406,11.484,6.453,15.844,11.281c4.344,4.813,7.578,10.406,9.266,15.688 c1.234,3.844,1.813,7.594,1.813,10.891c0,2.016-0.219,3.844-0.594,5.391c-0.266,1.156-0.641,2.188-1.047,3 c-0.313,0.625-0.641,1.125-0.984,1.547c-0.5,0.609-1,1-1.484,1.25c-0.5,0.234-1.016,0.375-1.766,0.391v9.766 c1.281,0.016,2.547-0.172,3.75-0.5c2.109-0.578,4-1.672,5.547-3.094c1.156-1.063,2.125-2.281,2.922-3.609 c1.219-2,2.063-4.219,2.609-6.594s0.813-4.906,0.813-7.547c0-4.406-0.734-9.125-2.266-13.875 c-1.453-4.516-3.672-8.984-6.547-13.188c-4.313-6.297-10.063-12.016-16.969-16.234c-3.453-2.094-7.188-3.813-11.172-5 c-3.984-1.219-8.203-1.875-12.578-1.875c-5.828,0-11.391,1.188-16.469,3.234C122.375,401.109,115.781,406.047,110.547,411.844z">
                                    </path>
                                    <path class="st0" d="M165.594,452.109c-1.594,1.266-2.531,3.172-2.531,5.219v7.891c0,2.031,0.938,3.953,2.531,5.219 c1.594,1.25,3.688,1.719,5.656,1.25l5.266-1.25v-18.344l-5.266-1.25C169.281,450.375,167.188,450.844,165.594,452.109z">
                                    </path>
                                    <path class="st0" d="M121.719,450.844l-5.281,1.25v18.344l5.281,1.25c1.969,0.469,4.063,0,5.656-1.25 c1.594-1.266,2.531-3.188,2.531-5.219v-7.891c0-2.047-0.938-3.953-2.531-5.219S123.688,450.375,121.719,450.844z">
                                    </path>
                                    <path class="st0" d="M453.453,429.594c-2.016-7.531-4.859-14.281-8.359-20.281c-6.141-10.547-14.266-18.75-23.234-25.25 c-6.734-4.875-13.922-8.859-21.234-12.281c-10.953-5.125-22.156-9.063-32.5-12.891c-10.344-3.813-19.797-7.547-27.156-11.891 c-2.688-1.594-5.109-3.25-7.203-4.984c-3.125-2.609-5.563-5.391-7.328-8.5s-2.953-6.609-3.406-10.984 c-0.328-3.125-0.469-6.063-0.469-8.875c0-8.281,1.219-15.453,2.781-22.781c4.625-5.219,8.859-11.438,12.859-18.875 c4.016-7.484,7.828-16.219,11.625-26.563c2.438-1.109,4.891-2.438,7.281-4.063c5.469-3.656,10.656-8.781,14.984-15.609 c4.313-6.828,7.781-15.313,10.156-25.781c0.656-2.906,0.969-5.797,0.969-8.641c0.016-5.938-1.391-11.594-3.75-16.656 c-1.641-3.516-3.719-6.734-6.141-9.656c1.234-4.563,2.734-10.703,4.078-17.891c1.844-9.984,3.375-21.984,3.375-34.594 c0-8.141-0.641-16.547-2.297-24.844c-1.234-6.219-3.063-12.391-5.625-18.297c-3.859-8.891-9.469-17.25-17.266-24.156 c-7.219-6.391-16.266-11.484-27.188-14.75c-3.859-4.625-7.734-8.563-11.703-11.906c-3.438-2.875-6.953-5.297-10.547-7.281 c-5.406-3-11-4.984-16.734-6.188s-11.578-1.641-17.641-1.641c-8.359,0-17.156,0.828-26.875,1.781 c-3.672,0.375-6.75,0.828-9.422,1.297c-3.984,0.719-6.969,1.453-9.359,1.938c-1.203,0.234-2.25,0.422-3.281,0.547 c-1.031,0.109-2.031,0.188-3.172,0.188c-1.531,0-3.297-0.125-5.609-0.453c-7.078-1.031-13.547-2.625-18.625-4.188 c-2.531-0.766-4.719-1.531-6.438-2.188c-0.859-0.328-1.609-0.625-2.203-0.875c-0.609-0.25-1.063-0.484-1.328-0.609l0.016,0.016 c-0.781-0.406-1.531-0.625-2.203-0.75C182.609,0.031,182.016,0,181.5,0c-1.078,0-1.844,0.156-2.453,0.313s-1.078,0.344-1.5,0.516 c-0.813,0.328-1.438,0.672-2.063,1.047c-1.141,0.688-2.234,1.453-3.391,2.344c-2.016,1.563-4.234,3.5-6.594,5.781 c-3.531,3.406-7.313,7.531-10.75,12.031c-1.719,2.234-3.328,4.578-4.781,7s-2.734,4.906-3.75,7.516 c-4.969,12.922-8.25,24.828-10.281,35.813c-2.047,10.984-2.828,21.047-2.828,30.281c0,15.109,2.109,27.922,4.141,38.75 c0,0.516,0.016,1,0.047,1.516c0.063,1.016,0.172,2.063,0.281,3.156c0.172,1.625,0.359,3.297,0.5,4.703 c0.078,0.703,0.141,1.328,0.188,1.813c0.016,0.234,0.031,0.453,0.031,0.609l0.016,0.156v0.047v0.016v0.922l1.984,8.828 c-2.859,3.125-5.328,6.625-7.25,10.469c-2.688,5.344-4.281,11.375-4.281,17.75c0,2.813,0.328,5.719,0.984,8.609 c1.563,6.984,3.641,13.078,6.125,18.391c3.719,7.984,8.438,14.188,13.656,18.844c4.047,3.625,8.375,6.266,12.656,8.219 c3.781,10.344,7.594,19.063,11.609,26.547c4,7.453,8.219,13.656,12.844,18.875c1.563,7.328,2.781,14.516,2.797,22.797 c0,2.813-0.156,5.75-0.484,8.875c-0.313,3-0.969,5.594-1.922,7.938c-1.422,3.5-3.5,6.484-6.328,9.313 c-2.828,2.781-6.438,5.391-10.703,7.813c-4.328,2.453-9.344,4.75-14.797,6.938c-9.563,3.875-20.469,7.531-31.516,11.953 c-8.281,3.297-16.672,7.063-24.672,11.766c-6,3.531-11.766,7.625-17.078,12.484c-7.953,7.281-14.813,16.359-19.547,27.578 c-4.75,11.234-7.391,24.531-7.375,40.25c0,2.219,0.469,4.328,1.234,6.281c0.703,1.828,1.688,3.5,2.844,5.094 c2.188,2.969,5,5.625,8.453,8.188c6.063,4.469,14.109,8.656,24.531,12.594c15.625,5.891,36.563,11.188,63.641,15.031 c27.063,3.844,60.266,6.25,100.266,6.25c34.703,0,64.266-1.797,89.156-4.781c18.656-2.25,34.703-5.156,48.313-8.484 c10.219-2.484,19.078-5.219,26.672-8.094c5.688-2.156,10.688-4.406,15.031-6.719c3.25-1.734,6.125-3.516,8.672-5.344 c3.813-2.766,6.875-5.609,9.203-8.844c1.172-1.609,2.125-3.328,2.828-5.203c0.703-1.844,1.125-3.875,1.125-5.969 C456.984,447.813,455.75,438.203,453.453,429.594z M327.266,358.094l-50.156,78.328l-5.594-38.453l14.234-15.063l-9.219-15.375 l38.906-20.453c1.078,1.391,2.219,2.703,3.422,3.953C321.438,353.672,324.266,356,327.266,358.094z M183.078,87.156 c45.219,10.031,133.641-9.141,133.641-9.141s0.953,21.922,16.031,42.047c5.938,7.906,10.828,20.266,14.5,32.016 c-0.984-1.828-3.297-2.516-6.75-2.953c-7.75-1.047-19.266-1.719-32.234-1.094c-38.531,1.891-35.672,5.391-50.797,5.391 s-12.266-3.5-50.797-5.391c-12.969-0.625-24.484,0.047-32.25,1.094c-4.031,0.531-6.563,1.344-7.141,4.031 c-0.203,1-0.516,2.125-1.906,2.672C169.641,139.891,181.516,119.531,183.078,87.156z M339.922,176.469 c0,0.219-0.156,22.313-15.188,29.859c-5.109,2.578-11.516,4-18.031,4.016c-6.875,0-13.156-1.563-18.172-4.516 c-5.547-3.25-9.281-8.078-11.109-14.313c-0.438-1.453-0.828-2.906-1.234-4.313c-1.188-4.297-4.391-16.234,2.406-21.484 c4.375-3.422,17.953-5.578,30.969-5.578c11.828,0,23.891,1.609,27.422,5.297C339.313,167.875,340.219,172.219,339.922,176.469z M238.75,187.203c-0.406,1.406-0.813,2.859-1.234,4.313c-1.828,6.234-5.563,11.063-11.094,14.313 c-5.031,2.953-11.313,4.516-18.188,4.516c-6.516-0.016-12.906-1.438-18.031-4.016c-15.031-7.547-15.172-29.641-15.188-29.859 c-0.297-4.25,0.609-8.594,2.922-11.031c3.547-3.688,15.609-5.297,27.438-5.297c13,0,26.594,2.156,30.984,5.578 C243.141,170.969,239.938,182.906,238.75,187.203z M188.547,264.063c-3.922-7.313-7.828-16.406-11.844-27.75l-1.328-3.703 l-3.688-1.359c-2.563-0.938-5.063-2.156-7.453-3.766c-3.609-2.422-7.031-5.734-10.172-10.672s-5.953-11.563-7.984-20.516 c-0.391-1.703-0.547-3.328-0.547-4.922c0-3.594,0.859-7,2.5-10.25c1.344-2.703,3.219-5.25,5.5-7.563 c3.844,5.813,7.031,10.422,8.188,11.578c2.203,2.203,3.297,0.078,3.469-4.047c1.359,9.172,5.719,24.313,19.797,31.797 c20.266,10.766,50.516,6.734,60.781-17.234c4.641-10.813,4.703-21.375,11.703-21.375c6.984,0,7.063,10.563,11.703,21.375 c10.281,23.969,40.531,28,60.797,17.234c20.25-10.766,20.391-37.422,20.391-39.297c0-0.969,0.922-1.703,2.234-1.844 c1.719,7.234,2.609,12.141,2.609,12.141s1.938-3.703,4.844-8.641c1.734,2.031,3.172,4.219,4.234,6.5 c1.422,3.063,2.188,6.266,2.188,9.594c0,1.609-0.172,3.25-0.563,4.938c-1.344,5.969-3.047,10.906-4.953,15 c-2.875,6.125-6.188,10.344-9.656,13.438c-3.453,3.094-7.141,5.109-10.969,6.531l-3.703,1.344l-1.313,3.719 c-4.016,11.344-7.938,20.453-11.859,27.75c-3.938,7.313-7.844,12.813-11.906,17.094l-1.609,1.703l-0.5,2.266 c-1.813,8.359-3.625,17.594-3.625,28.531c0,3.375,0.172,6.891,0.547,10.594c0.453,4.344,1.453,8.422,2.938,12.172 c0.063,0.172,0.156,0.359,0.219,0.516l-50.891,26.766l-56.406-26.172c1.734-4.063,2.906-8.5,3.406-13.281 c0.391-3.703,0.547-7.219,0.547-10.594c0.016-10.938-1.797-20.188-3.625-28.547l-0.5-2.266l-1.609-1.688 C196.391,276.844,192.469,271.375,188.547,264.063z M188.094,355.594c2.938-2.359,5.641-5,8.031-7.969l43.016,19.969l-9.188,15.313 l14.219,15.063l-5.25,36.203l-54.875-75.609C185.438,357.609,186.797,356.625,188.094,355.594z M440.219,458.5 c-0.016,0.094-0.125,0.406-0.422,0.906c-0.563,0.969-1.875,2.531-4.094,4.313c-1.922,1.547-4.516,3.281-7.781,5.063 c-5.734,3.141-13.5,6.406-23.344,9.5c-14.781,4.656-34.297,8.906-58.922,12c-24.625,3.063-54.359,4.969-89.672,4.969 c-34.094,0-63-1.781-87.125-4.672c-18.094-2.172-33.5-4.984-46.344-8.109c-9.656-2.359-17.875-4.906-24.703-7.5 c-5.141-1.938-9.5-3.906-13.078-5.828c-2.688-1.438-4.953-2.859-6.797-4.172c-2.75-1.969-4.5-3.766-5.375-5 c-0.438-0.594-0.656-1.063-0.734-1.281c-0.047-0.094-0.063-0.156-0.063-0.188c0-9.375,1.063-17.406,2.906-24.375 c1.609-6.094,3.828-11.391,6.531-16.078c4.719-8.203,10.922-14.641,18.297-20.063c5.5-4.078,11.672-7.563,18.203-10.672 c7.328-3.484,15.109-6.484,22.922-9.375v16.875h5.859h5.859v-21.203c7.469-2.797,14.75-5.672,21.531-9.109l86.703,119.453 l75.75-118.266c0.234,0.359,0.469,0.719,0.688,1.063c3.156,5.078,5.359,10.609,6.828,16.875c1.453,6.25,2.125,13.25,2.125,21.047 c0,18.063,0,33.797,0,44.391H318.75v11.734h67v-11.734h-27.219c0-10.594,0-26.328,0-44.391c0-11.359-1.297-21.703-4.516-31.141 c-0.281-0.813-0.578-1.625-0.891-2.422c9.156,3.609,18.734,6.859,28.016,10.547c7.953,3.141,15.672,6.578,22.688,10.656 c5.281,3.063,10.172,6.5,14.516,10.406c6.516,5.922,11.859,12.906,15.703,21.859C437.875,433.516,440.219,444.516,440.219,458.5 L440.219,458.5z">
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </span>
                    <h4 class="title">Experts by Experience</h4>
                    <p>Our Delmont hospital utilizes state of the art technology and employs a team of true experts.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!--============== ABOUT SECTION ===============-->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-12 col-lg-6">
                <div class="sotry-content">

                    <h3 class="title">
                        {{ $homeSetting->about_title }}
                    </h3>

                    <p class="mb-2">
                        {{ $homeSetting->about_description }}
                    </p>

                    <a class="about-btn" href="{{ route('about') }}">
                        {{ $homeSetting->about_button_text }}
                    </a>

                </div>
            </div>

            <div class="col-12 col-lg-6">
            <div class="row no-gutters">

                <div class="col-6">

                    <div class="single-thumb">
                        <img class="lazy img-fluid" src="{{ asset('storage/'.$homeSetting->about_image_one) }}" alt="">
                    </div>

                    <div class="single-thumb">
                        <img class="lazy img-fluid" src="{{ asset('storage/'.$homeSetting->about_image_two) }}" alt="">
                    </div>

                </div>

                <div class="col-6">

                    <div class="single-thumb">
                        <img class="lazy img-fluid" src="{{ asset('storage/'.$homeSetting->about_image_three) }}" alt="">
                    </div>

                </div>

            </div>
        </div>

        </div>
    </div>
</section>

<!--============== SERVICE SECTION ===============-->
<section class="service-section">
    <div class="container">
        <div class="title-head">
            <span class="subtitle">OUR SERVICES</span>
            <h3 class="title">We Care Our Patients</h3>
        </div>
        @include('frontend.components.service')
    </div>
</section>

<!--============== COUNTER SECTION ===============-->
<section id="counter" class="parallax100">
    <div class="container">
        <div class="row">

            <!-- 1 -->
            <div class="col-md-3">
                <div class="item text-center wow fadeInUp" data-wow-delay=".4s" data-wow-duration=".5s">

                    <span class="icon">
                        <!-- SVG 1 STATIC -->
                        <svg viewBox="-4.5 0 32 32" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19.469 12.594l3.625 3.313c0.438 0.406 0.313 0.719-0.281 0.719h-2.719v8.656c0 0.594-0.5 1.125-1.094 1.125h-4.719v-6.063c0-0.594-0.531-1.125-1.125-1.125h-2.969c-0.594 0-1.125 0.531-1.125 1.125v6.063h-4.719c-0.594 0-1.125-0.531-1.125-1.125v-8.656h-2.688c-0.594 0-0.719-0.313-0.281-0.719l10.594-9.625c0.438-0.406 1.188-0.406 1.656 0l2.406 2.156v-1.719c0-0.594 0.531-1.125 1.125-1.125h2.344c0.594 0 1.094 0.531 1.094 1.125v5.875z"></path>
                        </svg>
                    </span>

                    <h3 class="count">{{ $homeSetting->counter_one_number }}</h3>
                    <h6>{{ $homeSetting->counter_one_text }}</h6>

                </div>
            </div>

            <!-- 2 -->
            <div class="col-md-3">
                <div class="item text-center wow fadeInUp" data-wow-delay=".8s" data-wow-duration=".5s">

                    <span class="icon">
                        <!-- SVG 2 STATIC -->
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="6" r="4"></circle>
                            <path d="M20 17.5C20 19.9853 20 22 12 22C4 22 4 19.9853 4 17.5C4 15.0147 7.58172 13 12 13C16.4183 13 20 15.0147 20 17.5Z"></path>
                        </svg>
                    </span>

                    <h3 class="count">{{ $homeSetting->counter_two_number }}</h3>
                    <h6>{{ $homeSetting->counter_two_text }}</h6>

                </div>
            </div>

            <!-- 3 -->
            <div class="col-md-3">
                <div class="item text-center wow fadeInUp" data-wow-delay="1.2s" data-wow-duration=".5s">

                    <span class="icon">
                        <!-- SVG 3 STATIC -->
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                           <g>
                              <g>
                                 <path d="M256,0C114.837,0,0,114.843,0,256s114.837,256,256,256s256-114.843,256-256S397.163,0,256,0z M333.913,144.696 c18.413,0,33.391,14.978,33.391,33.391s-14.978,33.391-33.391,33.391s-33.391-14.978-33.391-33.391S315.5,144.696,333.913,144.696 z M178.087,144.696c18.413,0,33.391,14.978,33.391,33.391s-14.978,33.391-33.391,33.391s-33.391-14.978-33.391-33.391 S159.674,144.696,178.087,144.696z M256,411.826c-85.924,0-155.826-69.903-155.826-155.826c0-9.223,7.479-16.696,16.696-16.696 s16.696,7.473,16.696,16.696c0,67.511,54.924,122.435,122.435,122.435c67.511,0,122.435-54.924,122.435-122.435 c0-9.223,7.479-16.696,16.696-16.696s16.696,7.473,16.696,16.696C411.826,341.924,341.924,411.826,256,411.826z">
                                 </path>
                              </g>
                           </g>
                        </g>
                     </svg>
                    </span>

                    <h3 class="count">{{ $homeSetting->counter_three_number }}</h3>
                    <h6>{{ $homeSetting->counter_three_text }}</h6>

                </div>
            </div>

            <!-- 4 -->
            <div class="col-md-3">
                <div class="item text-center wow fadeInUp" data-wow-delay="1.6s" data-wow-duration=".5s">

                    <span class="icon">
                        <!-- SVG 4 STATIC -->
                        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                           <title>check-square-solid</title>
                           <g id="Layer_2" data-name="Layer 2">
                              <g id="invisible_box" data-name="invisible box">
                                 <rect width="48" height="48" fill="none"></rect>
                              </g>
                              <g id="icons_Q2" data-name="icons Q2">
                                 <path d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2H42a2,2,0,0,0,2-2V6A2,2,0,0,0,42,4ZM35.4,18.4l-14,14a1.9,1.9,0,0,1-2.8,0l-5.9-5.9a2.2,2.2,0,0,1-.4-2.7,2,2,0,0,1,3.1-.2L20,28.2,32.6,15.6a2,2,0,0,1,2.8,2.8Z">
                                 </path>
                              </g>
                           </g>
                        </g>
                     </svg>
                    </span>

                    <h3 class="count">{{ $homeSetting->counter_four_number }}</h3>
                    <h6>{{ $homeSetting->counter_four_text }}</h6>

                </div>
            </div>

        </div>
    </div>
</section>

<!--============== DOCTORS SECTION ===============-->
@include('frontend.components.doctor')

<!--============== TESTIMONIAL SECTION ===============-->
@include('frontend.components.testimonial')

<!--============== BLOG SECTION ===============-->
<section class="blog-section">
    <div class="container">
        <div class="title-head">
            <span class="subtitle">Our Blog</span>
            <h3 class="title">Recent Articles and News</h3>
        </div>
        <div class="row g-5">
            <div class="col-lg-6 col-md-12">
                <div class="blog-left">
                    <div class="img-wrapper">
                        <a href="blog-details.html">
                            <img class="img-fluid" src="{{ asset('frontend-assets/media/home/blog.jpeg') }}" alt="">
                        </a>
                        <div class="text-box">
                            <h3>What is The Success rate of a root canel?</h3>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.
                                Aenean massa. Cum sociis natoque penatibus et.</p>
                            <a href="blog-details.html" class="button">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="blog-right">
                    <div class="row single-box">
                        <div class="col-md-6 col-sm-12">
                            <div class="img-wrapper">
                                <a href="blog-details.html">
                                    <img class="img-fluid" src="{{ asset('frontend-assets/media/home/blog2.jpg') }}" alt="">
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <h3>What is The Success rate of a root canel?</h3>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo...</p>
                            <a href="blog-details.html" class="button">Read More</a>
                        </div>
                    </div>
                    <div class="row single-box">
                        <div class="col-md-6 col-sm-12">
                            <div class="img-wrapper">
                                <a href="blog-details.html">
                                    <img class="img-fluid" src="{{ asset('frontend-assets/media/home/blog3.jpg') }}" alt="">
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <h3>How to handle your kids' mystery ailments?</h3>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo...</p>
                            <a href="blog-details.html" class="button">Read More</a>
                        </div>
                    </div>
                    <div class="row single-box">
                        <div class="col-md-6 col-sm-12">
                            <div class="img-wrapper">
                                <a href="blog-details.html">
                                    <img class="img-fluid" src="{{ asset('frontend-assets/media/home/blog4.jpg') }}" alt="">
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <h3>How to help the cardiology department</h3>
                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo...</p>
                            <a href="blog-details.html" class="button">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!--============== SISTER AND CONCERNS SECTION ===============-->
<section class="sister-concern-section">
    <div class="container">
        <div class="title-head">
            <span class="subtitle">sister soncerns</span>
            <h3 class="title">Our Brands</h3>
        </div>
        <div class="clients-slider owl-carousel owl-theme">
            <div class="clients-member">
                <a href="#" target="_blank">
                    <div class="clients-photo">
                        <img class="img-fluid" src="{{ asset('frontend-assets/media/home/brand1.png') }}" alt="Member">
                    </div>
                </a>
            </div>
            <div class="clients-member">
                <a href="#" target="_blank">
                    <div class="clients-photo">
                        <img class="img-fluid" src="{{ asset('frontend-assets/media/home/brand2.png') }}" alt="Member">
                    </div>
                </a>
            </div>
            <div class="clients-member">
                <a href="#" target="_blank">
                    <div class="clients-photo">
                        <img class="img-fluid" src="{{ asset('frontend-assets/media/home/brand3.png') }}" alt="Member">
                    </div>
                </a>
            </div>
            <div class="clients-member">
                <a href="#" target="_blank">
                    <div class="clients-photo">
                        <img class="img-fluid" src="{{ asset('frontend-assets/media/home/brand4.png') }}" alt="Member">
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection