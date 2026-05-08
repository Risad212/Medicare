@extends('frontend.layouts.front-app')

@section('front-content')
<!--========== BreadCamb Section ==========-->
<div class="breadcrumb">
   <div class="container">
      <div class="col-xs-12">
         <div class="breadcrumb-content">
            <div class="breadcrumb-text-wrapper">
               <h2 class="page-name">Our Blogs</h2>
               <ul class="breadcrumb-list">
                  <li><a href="#">Home</a></li>
                  <li>Blog</li>
               </ul>
            </div><!--/.breadcrumb-text-wrapper-->
         </div><!--/.breadcrumb-content-->
      </div><!--/.col-xs-12-->
   </div>
</div>

<!--========== Blog Section ==========-->
<section class="blog-page">
   <div class="container">
      <div class="row">

         <div class="col-lg-4">
            <div class="blog-card">

               <div class="blog-img">
                  <a href="#">
                     <img class="img-fluid"
                        src="{{ asset('frontend-assets/media/blog/blog1.webp') }}"
                        alt="">
                  </a>
               </div>

               <div class="card-body">
                  <div class="cart-top">
                     <span class="author">
                        <!-- SVG -->
                        By Admin Rose
                     </span>

                     <span class="date">
                        <!-- SVG -->
                        sep, 10 2023
                     </span>
                  </div>

                  <h3 class="title">
                     <a href="#">What is The Success rate of a root canal?</a>
                  </h3>

                  <p>Nullam mauris vitae tortor sodales efficitur. Quisque orci ante. Proin amet turpis</p>
               </div>

            </div>
         </div>

         <div class="col-lg-4">
            <div class="blog-card">

               <div class="blog-img">
                  <a href="#">
                     <img class="img-fluid"
                        src="{{ asset('frontend-assets/media/blog/blog2.webp') }}"
                        alt="">
                  </a>
               </div>

               <div class="card-body">
                  <div class="cart-top">
                     <span class="author">
                        <!-- SVG -->
                        By Admin Rose
                     </span>

                     <span class="date">
                        <!-- SVG -->
                        oct, 20 2020
                     </span>
                  </div>

                  <h3 class="title">
                     <a href="#">How to handle your kids’ mystery ailments?</a>
                  </h3>

                  <p>Nullam mauris vitae tortor sodales efficitur. Quisque orci ante. Proin amet turpis</p>
               </div>

            </div>
         </div>

         <div class="col-lg-4">
            <div class="blog-card">

               <div class="blog-img">
                  <a href="#">
                     <img class="img-fluid"
                        src="{{ asset('frontend-assets/media/blog/blog3.jpeg') }}"
                        alt="">
                  </a>
               </div>

               <div class="card-body">
                  <div class="cart-top">
                     <span class="author">
                        <!-- SVG -->
                        By Admin Rose
                     </span>

                     <span class="date">
                        <!-- SVG -->
                        jan, 25 2019
                     </span>
                  </div>

                  <h3 class="title">
                     <a href="#">How to help the cardiology department</a>
                  </h3>

                  <p>Nullam mauris vitae tortor sodales efficitur. Quisque orci ante. Proin amet turpis</p>
               </div>

            </div>
         </div>

      </div>
   </div>
</section>
@endsection