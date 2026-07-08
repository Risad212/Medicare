@extends('frontend.layouts.front-app')

@section('meta_title', $seo->meta_title ?? 'Medicare')
@section('meta_description', $seo->meta_description ?? '')
@section('meta_keywords', $seo->meta_keywords ?? '')


@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'Our Blog'
])

<!--========== Blog Section ==========-->
<section class="blog-page">
   <div class="container">
      <div class="row">

         @forelse($blogs as $blog)
         <div class="col-lg-4">
            <div class="blog-card">

               <div class="blog-img">
                  <a href="{{ route('blog.show', $blog->slug) }}">
                     <img class="img-fluid"
                        src="{{ asset('storage/' . $blog->image) }}"
                        alt="{{ $blog->title }}">
                  </a>
               </div>

               <div class="card-body">
                  <div class="cart-top">
                     <span class="author">
                        By {{ $blog->author }}
                     </span>
                     <span class="date">
                        {{ $blog->created_at->format('M, d Y') }}
                     </span>
                  </div>

                  <h3 class="title">
                     <a href="{{ route('blog.show', $blog->slug) }}">{{ $blog->title }}</a>
                  </h3>

                  <p>{{ Str::limit($blog->excerpt, 100) }}</p>
               </div>

            </div>
         </div>
         @empty
         <div class="col-12 text-center">
            <p>No blog posts found.</p>
         </div>
         @endforelse

      </div>
   </div>
</section>

@endsection