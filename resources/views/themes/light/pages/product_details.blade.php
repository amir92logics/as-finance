@extends($theme.'layouts.app')
@section('title',trans('Product Details'))
@section('content')
    <!-- product details -->
    <section class="product-details">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="product-details-left-image">
                        <img src="{{getFile($product->driver,$product->thumbnail_image)}}" alt="image">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="product-details-right-container">
                        <div class="product-details-right-content">
                            <div class="product-details-right-rating">
                                <ul>
                                    {!! $avg_rating !!}
                                    <li>({{count($reviews??[])}})</li>
                                </ul>
                            </div>
                            <div class="product-details-right-title">
                                <h4>{{optional($product->details)->title}}</h4>
                                <h5>{{currencyPosition($product->price)}}
                                    <sub>/{{$product->quantity+0}} {{$product->quantity_unit}}</sub></h5>
                            </div>
                            <div class="product-details-info product-details-cmn-content">
                                <h6>@lang('Product Types') : <span>{{optional($product->subcategory)->name}}</span></h6>
                                <h6>@lang('Stock update') : <span>{{$product->status}}</span></h6>
                            </div>
                            <div class="product-details-quantity product-details-cmn-content">
                                <div class="product-details-quantity-inner">
                                    <h6>@lang('Quantity'):</h6>
                                    <div class="incriment-dicriment">
                                        <div class="count-single">
                                            <button type="button" class="decrement"><i class="fa-light fa-minus"></i>
                                            </button>
                                            <span class="number detailsPageQuantity">1</span>
                                            <button type="button" class="increment"><i class="fa-light fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="cart-btn">
                                        <a href="javascript:void(0);" class="btn-1 addToCart" data-id="{{$product->id}}" >@lang('Add to Cart') <span></span></a>
                                    </div>
                                </div>
                                <div class="buy-btn">
                                    <a href="javascript:void(0);" class="btn-1 addToCart" data-ordernow="true" data-id="{{$product->id}}">@lang('Order Now') <span></span></a>
                                </div>
                            </div>
                            <div class="product-details-info product-details-cmn-content">
                                <h6>@lang('Categories'): <span>{{optional($product->category)->name}}</span></h6>
                                <div class="social-area">
                                    <h6>@lang('Share'):</h6>
                                    <ul id="socialShare">

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- product details -->


    <!-- product dectription  -->
    <section class="product-description">
        <div class="container">
            <div class="shop-list-tab">
                <div class="quote-tab">
                    <!--Start Quote Tab Button-->
                    <div class="quote-tab__button">
                        <ul class="tabs-button-box">
                            <li data-tab="#quote1" class="tab-btn-item active-btn-item">
                                <div class="quote-tab__button-inner">
                                    <h6>@lang('Description')</h6>
                                </div>
                            </li>
                            <li data-tab="#quote3" class="tab-btn-item">
                                <div class="quote-tab__button-inner">
                                    <h6>@lang('Reviews')</h6>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <!--End Quote Tab Button-->

                    <!--Start Tabs Content Box-->
                    <div class="tabs-content-box">
                        <!--Start Tab-->
                        <div class="tab-content-box-item tab-content-box-item-active" id="quote1">
                            <div class="quote-tab-content-box-item">
                                <div class="description-content">
                                    <p>{!! optional($product->details)->description !!}</p>
                                </div>
                            </div>
                        </div>
                        <!--End Tab-->


                        <!--Start Tab-->
                        <div class="tab-content-box-item" id="quote3">
                            <div class="quote-tab-content-box-item">
                                <div class="review-container">
                                    <div class="row">
                                        <div class="col-lg-7">
                                            @foreach($reviews as $review)
                                                <div class="review_box">
                                                    <h4>({{count($reviews??[])}}) @lang('Reviews')</h4>
                                                    <div class="review-content">
                                                        <div class="reviwe-image">
                                                            <img src="{{ getFile(optional($review->user)->image_driver, optional($review->user)->image) }}" alt="image">
                                                        </div>
                                                        <div class="review-item">
                                                            <h6>@lang(optional($review->user)->firstname) @lang(optional($review->user)->lastname)</h6>
                                                            <div class="review-icon-list">
                                                                <ul>
                                                                    {!! $review->stars !!}
                                                                </ul>
                                                            </div>
                                                            <div class="review-comments">
                                                                <p>{!! $review->comment->comment !!}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                        <div class="col-lg-5">
                                            <div class="description-content">
                                                <h4>@lang('Write A Review')</h4>
                                                <form action="{{route('user.addRating')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$product->id}}" name="product_id">
                                                    <div class="review-container">

                                                        <div class="review">
                                                            <p>@lang('Your Rating')</p>
                                                            <div class="review-border"></div>
                                                            <div class="product-details-right-rating">
                                                                <div class="rating_icon d-flex align-items-center ml_10 mb-1" id="half" ></div>
                                                            </div>
                                                        </div>
                                                        <div class="description-review-form">

                                                            <textarea name="massage" placeholder="@lang('Your Reviews')" required></textarea>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <input type="text" name="name" placeholder="@lang('Your Name')" required>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <input type="email" name="email"
                                                                           placeholder="@lang('Your Email')" required>
                                                                </div>
                                                            </div>
                                                            <button type="submit" class="btn-1">@lang('Submit Review')
                                                                <span></span></button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End Tab-->

                    </div>
                    <!--End Tabs Content Box-->

                </div>
            </div>
        </div>
    </section>
    <!-- product dectription  -->


    <!-- best selling product -->
    <section class="best-selling">
        <div class="container">
            <div class="common-title">
                <h3>@lang('Related') <span>@lang('Products') <img
                            src="{{asset($themeTrue.'images/shape/title-bottom-shape.svg')}}" alt="shape"></span></h3>
                <p>@lang('Different term invest made your money secure and choice the best plan for your future asset') </p>
            </div>
            <div class="row">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="col-lg-3 col-md-6">
                        <div class="new-arrival-single wow fadeInUp" data-wow-delay="200ms" data-wow-duration="1000ms">
                            <div class="new-arrival-image-container">
                                <div class="new-arrival-image">
                                    <a href="{{route('product.details',optional($relatedProduct->details)->slug??'slug')}}"><img src="{{getFile($relatedProduct->driver,$relatedProduct->thumbnail_image)}}" alt="product"></a>
                                </div>
                                <div class="new-arrival-icon-list">
                                    <ul>
                                        @if(auth()->user())
                                            <li><a href="javascript:void(0)" class="addToWishlist {{auth()->user()?wishlist($relatedProduct->wishlist)?'addedWishlist':'':''}}" data-id="{{$product->id}}"><i class="fa-light fa-heart"></i></a></li>
                                        @else
                                            <li><a href="{{route('login')}}"><i class="fa-light fa-heart"></i></a></li>
                                        @endif
                                        <li><a href="{{route('product.details',optional($relatedProduct->details)->slug??'slug')}}" class="quick-view-btn"><i
                                                    class="fa-light fa-eye"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="product-single-content">
                                <div class="product-single-review">
                                    <ul>
                                        {!! avgRating($relatedProduct->averageRating) !!}
                                    </ul>
                                </div>
                                <p>{{optional($relatedProduct->details)->title}} </p>
                                <h6>{{currencyPosition($relatedProduct->price+0)}}/<span>{{$relatedProduct->quantity+0}} {{$relatedProduct->quantity_unit}}</span></h6>
                                <div class="product-single-button">
                                    <a href="javascript:void(0)" class="btn-1 addToCart" data-ordernow="true" data-id="{{$relatedProduct->id}}">@lang('Order Now') <span></span></a>
                                    <a href="javascript:void(0)" class="btn-1 addToCart" data-id="{{$relatedProduct->id}}">@lang('Add to Cart') <span></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- best selling product -->


    <!-- subscribe -->
    <section class="subscribe">
        <div class="container">
            <div class="subscribe-container" style=" background: url({{isset($subscribe_section['single']['media']->image)?getFile($subscribe_section['single']['media']->image->driver,$subscribe_section['single']['media']->image->path):getFile('local','image')}}) no-repeat;">
                <div class="subscribe-content">
                    <h3>{!! styleSentence($subscribe_section['single']['heading']??'',5) !!}</h3>
                    <div class="subscribe-btn">
                        <a href="{{$subscribe_section['single']['media']->button_link??'#'}}" class="btn-1">{!! $subscribe_section['single']['button_name']??'Create An Account' !!} <i class="fa-sharp fa-solid fa-arrow-right"></i> <span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('js-lib')
    <script src="{{asset($themeTrue.'js/jquery.raty.js')}}"></script>
@endpush

<!-- Plan_modal_end -->



@push('script')
    @if(count($errors) > 0 )
        <script>
            @foreach($errors->all() as $key => $error)
            Notiflix.Notify.failure("@lang($error)");
            @endforeach
        </script>
    @endif
    <script>
        $(document).ready(function (){
            $('#half').raty({
                half:  true,
                hints: [['bad 1/2', 'bad'], ['poor 1/2', 'poor'], ['regular 1/2', 'regular'], ['good 1/2', 'good'], ['gorgeous 1/2', 'gorgeous']],
                starHalf: '{{asset($themeTrue.'images/star-half.png')}}',
                starOff: '{{asset($themeTrue.'images/star-off.png')}}',
                starOn: '{{asset($themeTrue.'images/star-on.png')}}',
                scoreName: 'rating',

            });
        })
    </script>
@endpush

@push('script')
    <script>
        $('#socialShare').socialSharingPlugin({
            url: window.location.href,
            title: $('meta[property="og:title"]').attr('content'),
            description: $('meta[property="og:description"]').attr('content'),
            img: $('meta[property="og:image"]').attr('content'),
            enable: ['copy', 'facebook', 'twitter', 'pinterest', 'linkedin']
        });

    </script>
@endpush


