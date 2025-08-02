@extends($theme.'layouts.app')
@section('title',trans('Products'))
@section('content')
    <!-- product shop -->
    <section class="shop-left">
        <div class="container">
            <div class="shop-offcanvas-container">
                <div class="shop-offcanvas-left">
                    <form method="get" action="">
                        <div class="shop-left-aside">
                            <div class="shop-filter">
                                <h6>@lang('Filters')</h6>
                                <div class="clear-all">
                                    @if(request()->has('status') || request()->has('category') || request()->has('min') || request()->has('max'))
                                        <a href="{{route('products')}}">@lang('Clear')<i
                                                class="fa-regular fa-xmark"></i></a>
                                    @endif
                                </div>
                            </div>

                            <!--Accordian Box-->
                            <div class="accordion" id="accordionPanelsStayOpenExample">
                                <div class="accordion-item clear-btn-group">
                                    <h5 class="accordion-header" id="panelsStayOpen-headingOne">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne"
                                                aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                            @lang('Availability')
                                        </button>
                                    </h5>
                                    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <div class="filter-form">
                                                <form>
                                                    <label class="custom-checkbox">
                                                        <input type="checkbox" name="status[]"
                                                               @checked(in_array('Available',request()->status??[])) value="Available"
                                                               class="checkbox">
                                                        <span class="checkmark"></span>
                                                        <span class="label-text">@lang('In Stock') </span>
                                                    </label>
                                                    <label class="custom-checkbox">
                                                        <input type="checkbox" name="status[]"
                                                               @checked(in_array('Stock Out',request()->status??[])) value="Stock Out"
                                                               class="checkbox">
                                                        <span class="checkmark"></span>
                                                        <span class="label-text">@lang('Out of Stock') </span>
                                                    </label>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item clear-btn">
                                    <h5 class="accordion-header" id="panelsStayOpen-headingThree">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree"
                                                aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">
                                            @lang('Product Category')
                                        </button>
                                    </h5>
                                    <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <div class="filter-form">
                                                @foreach($categories as $category)
                                                    <label class="custom-checkbox">
                                                        <input type="checkbox" name="category[]"
                                                               @checked(in_array($category->id,request()->category??[])) value="{{$category->id}}"
                                                               class="checkbox">
                                                        <span class="checkmark"></span>
                                                        <span class="label-text">{{$category->name}}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item clear-btn">
                                    <h5 class="accordion-header" id="panelsStayOpen-headingFour">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseFour"
                                                aria-expanded="true" aria-controls="panelsStayOpen-collapseFour">
                                            @lang('Price')
                                        </button>
                                    </h5>
                                    <div id="panelsStayOpen-collapseFour" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <div class="filter-form">
                                                <label
                                                    class="price-label">@lang('From') {{basicControl()->currency_symbol}}</label>
                                                <input type="number" name="min" value="{{request()->min}}"
                                                       class="price-input" placeholder="0">
                                                <label
                                                    class="price-label">@lang('To') {{basicControl()->currency_symbol}}</label>
                                                <input type="number" name="max" value="{{request()->max}}"
                                                       class="price-input" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <button type="submit" class="btn-1 w-100 text-center justify-content-center">@lang('Apply')
                                <span></span></button>

                        </div>
                    </form>
                </div>

                <div class="shop-offcanvas-right">
                    <div class="filter-body">
                        <div class="filter-body-top">
                            <div class="sorting-container">
                                <div class="sorting-buttton">
                                    <button class="offcanvas-taggle-btn"><i class="fa-light fa-sliders"></i></button>
                                    <h4>@lang('Our Products for Sell')</h4>
                                </div>
                                <form action="{{route('products')}}" id="sortingForm">
                                    <div class="sorting d-flex gap-1 align-items-center" id="Sorting">
                                        <p>@lang('Sort by'):</p>
                                        <select class="selectpicker nice-select" name="sorting">
                                            <option value="best_selling" @selected(request()->sorting=='best_selling')>@lang('Best Selling')</option>
                                            <option value="asc" @selected(request()->sorting=='asc')>@lang('Alphabetically, A-Z')</option>
                                            <option value="desc" @selected(request()->sorting=='desc')>@lang('Alphabetically, Z-A')</option>
                                            <option value="low_to_high" @selected(request()->sorting=='low_to_high')>@lang('Price, low to high')</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="product-wrapper">
                            <div class="row">
                                @foreach($products as $product)
                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="new-arrival-single wow fadeInUp" data-wow-delay="200ms"
                                             data-wow-duration="1000ms">
                                            <div class="new-arrival-image-container">
                                                <div class="new-arrival-image">
                                                    <a href="{{route('product.details',optional($product->details)->slug??'slug')}}"><img
                                                            src="{{getFile($product->driver,$product->thumbnail_image)}}"
                                                            alt="product"></a>
                                                </div>
                                                <div class="new-arrival-icon-list">
                                                    <ul>
                                                        @if(auth()->user())
                                                            <li><a href="javascript:void(0)"
                                                                   class="addToWishlist {{auth()->user()?wishlist($product->wishlist)?'addedWishlist':'':''}}"
                                                                   data-id="{{$product->id}}"><i
                                                                        class="fa-light fa-heart"></i></a></li>
                                                        @else
                                                            <li><a href="{{route('login')}}"><i
                                                                        class="fa-light fa-heart"></i></a></li>
                                                        @endif
                                                        <li>
                                                            <a href="{{route('product.details',optional($product->details)->slug??'slug')}}"
                                                               class="quick-view-btn"><i
                                                                    class="fa-light fa-eye"></i></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="product-single-content">
                                                <div class="product-single-review">
                                                    <ul>
                                                        {!! avgRating($product->averageRating) !!}
                                                    </ul>
                                                </div>
                                                <p>{{optional($product->details)->title}}</p>
                                                <h6>{{currencyPosition($product->price)}}
                                                    <span>/{{$product->quantity+0}} {{$product->quantity_unit}}</span>
                                                </h6>
                                                <div class="product-single-button">
                                                    <a href="javascript:void(0)" class="btn-1 addToCart"
                                                       data-ordernow="true"
                                                       data-id="{{$product->id}}">@lang('Order Now') <span></span></a>
                                                    <a href="javascript:void(0)" class="btn-1 addToCart"
                                                       data-id="{{$product->id}}">@lang('Add to Cart') <span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="paigination">
                                    {{ $products->appends($_GET)->links($theme.'partials.pagination') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- shope left -->

    <!-- subscribe -->
    <section class="subscribe">
        <div class="container">
            <div class="subscribe-container"
                 style=" background: url({{isset($subscribe_section['single']['media']->image)?getFile($subscribe_section['single']['media']->image->driver,$subscribe_section['single']['media']->image->path):getFile('local','image')}}) no-repeat;">
                <div class="subscribe-content">
                    <h3>{!! styleSentence($subscribe_section['single']['heading']??'',5) !!}</h3>
                    <div class="subscribe-btn">
                        <a href="{{$subscribe_section['single']['media']->button_link??'#'}}"
                           class="btn-1">{!! $subscribe_section['single']['button_name']??'Create An Account' !!} <i
                                class="fa-sharp fa-solid fa-arrow-right"></i> <span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $(document).on('change', '#Sorting', function () {
            $('#sortingForm').submit()
        })
    </script>
@endpush
