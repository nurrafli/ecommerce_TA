@extends('layouts.app')
@section('content')
<main>
  <style>
    .bg-soft.blue{
      background: linear-gradient(to right, #efeaec, #a0c4ff);
    }
  </style>
    <section class="swiper-container js-swiper-slider swiper-number-pagination slideshow " style="margin-top: -130px" data-settings='{
        "autoplay": {
          "delay": 5000
        },
        "slidesPerView": 1,
        "effect": "fade",
        "loop": true
      }'>
      <div class="swiper-wrapper">
        @foreach ($slides as $slide)

        <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_right-center">
              <img loading="lazy" src="{{ asset('uploads/slides') }}/{{$slide->image}}" width="542" height="733"
                alt="Woman Fashion 1"
                class="slideshow-character__img animate animate_fade animate_btt animate_delay-9 w-auto h-auto" />
              <div class="character_markup type2">
                <p
                  class="text-uppercase font-sofia mark-grey-color animate animate_fade animate_btt animate_delay-10 mb-0">
                  {{$slide->tagline}}</p>
              </div>
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              <h6 class="text_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                New Arrivals</h6>
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">{{$slide->title}}</h2>
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-5">{{$slide->subtitle}}</h2>
              <a href="{{$slide->link}}"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">Shop
                Now</a>
            </div>
          </div>
        </div>
        @endforeach
      </div>

      <div class="container">
        <div
          class="slideshow-pagination slideshow-number-pagination d-flex align-items-center position-absolute bottom-0 mb-5">
        </div>
      </div>
    </section>
    <div class="container mw-1620 bg-soft-blue border-radius-10">
      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>
    <style>
      .category-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 2px solid #ccc;
      }

      .category-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .category-link {
        color: #333;
        text-decoration: none;
        font-size: 1rem;
        text-align: center;
      }

    </style>

    <section class="category-carousel container text-center my-5">
      <h2 class="section-title mb-4 fw-bold">Kategori Produk</h2>
      <div class="d-flex flex-wrap justify-content-center gap-4">
        @foreach ($categories as $category)
          <div class="d-flex flex-column align-items-center">
            <div class="category-circle">
              <img loading="lazy" src="{{ asset('uploads/categories') }}/{{ $category->image }}"
                  alt="{{ $category->name }}">
            </div>
            <a href="{{ route('shop.index', ['categories' => $category->id]) }}" class="category-link fw-bold mt-2">
              {{ $category->name }}
            </a>
          </div>
        @endforeach
      </div>
    </section>

      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

      <section class="hot-deals container">
        <h2 class="section-title text-center mb-3 pb-xl-3 mb-xl-4">Collection</h2>
        <div class="row">
          <div
            class="col-md-6 col-lg-4 col-xl-20per d-flex align-items-center flex-column justify-content-center py-4 align-items-md-start">
            <h2>Produk Toko</h2>
            <h2 class="fw-bold">Pecah Belah</h2>

            <div class="position-relative d-flex align-items-center text-center pt-xxl-4 js-countdown mb-3"
              data-date="18-3-2024" data-time="06:50">
              <div class="day countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Days</span>
              </div>

              <div class="hour countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Hours</span>
              </div>

              <div class="min countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Mins</span>
              </div>

              <div class="sec countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Sec</span>
              </div>
            </div>

            <a href="{{route('shop.index')}}" class="btn-link default-underline text-uppercase fw-medium mt-3">View All</a>
          </div>
          <div class="col-md-6 col-lg-8 col-xl-80per">
            <div class="position-relative">
              <div class="swiper-container js-swiper-slider" data-settings='{
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": 4,
                  "slidesPerGroup": 4,
                  "effect": "none",
                  "loop": false,
                  "breakpoints": {
                    "320": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 2,
                      "spaceBetween": 14
                    },
                    "768": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 3,
                      "spaceBetween": 24
                    },
                    "992": {
                      "slidesPerView": 3,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    },
                    "1200": {
                      "slidesPerView": 4,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    }
                  }
                }'>
                <div class="swiper-wrapper">

                  @foreach ($sproducts as $sproduct)
                  <div class="swiper-slide product-card product-card_style3">
                    <div class="pc__img-wrapper">
                      <a href="{{route('shop.product.details',['product_slug'=>$sproduct->slug])}}">
                        <img loading="lazy" src="{{ asset('uploads/products') }}/{{$sproduct->image}}" width="258" height="313"
                          alt="{{$sproduct->name}}" class="pc__img">
                      </a>
                    </div>

                    <div class="pc__info position-relative">
                      <h6 class="pc__title"><a href="{{route('shop.product.details',['product_slug'=>$sproduct->slug])}}">{{$sproduct->name}}</a></h6>
                      <div class="product-card__price d-flex">
                        <span class="money price text-secondary">
                          @if($sproduct->sale_price)
                            <s>Rp {{ number_format($sproduct->regular_price, 0, ',', '.') }}</s>
                              Rp {{ number_format($sproduct->sale_price, 0, ',', '.') }}
                          @else
                              Rp {{ number_format($sproduct->regular_price, 0, ',', '.') }}
                          @endif
                        </span>
                      </div>                      
                    </div>
                  </div>
                  @endforeach
                  
                </div><!-- /.swiper-wrapper -->
              </div><!-- /.swiper-container js-swiper-slider -->
            </div><!-- /.position-relative -->
          </div>
        </div>
      </section>

      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

      <section class="products-grid container">
        <h2 class="section-title text-center mb-3 pb-xl-3 mb-xl-4">Featured Products</h2>

        <div class="row">
          @foreach ($fproducts as $fproduct)
          <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card product-card_style3 mb-3 mb-md-4 mb-xxl-5">
              <div class="pc__img-wrapper">
                <a href="{{route('shop.product.details',['product_slug'=>$fproduct->slug])}}">
                  <img loading="lazy" src="{{ asset('uploads/products') }}/{{$fproduct->image}}" width="330" height="400"
                    alt="{{$fproduct->name}}" class="pc__img">
                </a>
              </div>

              <div class="pc__info position-relative">
                <h6 class="pc__title"><a href="{{route('shop.product.details',['product_slug'=>$fproduct->slug])}}">{{$fproduct->name}}</a></h6>
                <div class="product-card__price d-flex align-items-center">
                  <span class="money price text-secondary">
                    @if($fproduct->sale_price)
                        <s>Rp {{ number_format($fproduct->regular_price, 0, ',', '.') }}</s>
                          Rp {{ number_format($fproduct->sale_price, 0, ',', '.') }}
                    @else
                          Rp {{ number_format($fproduct->regular_price, 0, ',', '.') }}
                    @endif
                  </span>
                </div>

                
              </div>
            </div>
          </div>
          @endforeach
          
        </div><!-- /.row -->

        <div class="text-center mt-2">
          <a class="btn-link btn-link_lg default-underline text-uppercase fw-medium" href="#">Load More</a>
        </div>
      </section>
    </div>

    <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

  </main>
@endsection