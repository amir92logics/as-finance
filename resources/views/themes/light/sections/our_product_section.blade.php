<!-- our-product -->
<section class="our-product">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="our-product-content">
                    <div class="bg-layer" style="background: url({{isset($our_product_section['single']['media']->banner_one_image)?getFile($our_product_section['single']['media']->banner_one_image->driver,$our_product_section['single']['media']->banner_one_image->path):''}});"></div>
                    <div class="our-product-content-inner">
                        <h6>{{$our_product_section['single']['banner_one_heading'] ?? ''}} </h6>
                        <h3>{{$our_product_section['single']['banner_one_subheading'] ?? ''}}</h3>
                        <p>{!! $our_product_section['single']['banner_one_short_description'] ?? '' !!}</p>
                    </div>
                    <div class="offer-wrapper">
                        @if(isset($our_product_section['single']['banner_one_offer']) && $our_product_section['single']['banner_one_offer'])
                            <h6>@lang('Up to')</h6>
                            <h4>{{$our_product_section['single']['banner_one_offer']??''}}</h4>
                            <h6>@lang('off')</h6>
                        @endif
                    </div>
                    <a href="{{$our_product_section['single']['media']->banner_one_button_link??''}}" class="btn-1">{{$our_product_section['single']['banner_one_button_name']}} <i class="fa-sharp fa-solid fa-arrow-right"></i> <span></span></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="our-product-content our-product-content-collumn">
                    <div class="bg-layer" style="background: url('{{isset($our_product_section['single']['media']->banner_two_image)?getFile($our_product_section['single']['media']->banner_two_image->driver,$our_product_section['single']['media']->banner_two_image->path):''}}');"></div>
                    <div class="our-product-content-inner">
                        <h6>{!! $our_product_section['single']['banner_two_heading']??'' !!} </h6>
                        <h3>{!! $our_product_section['single']['banner_two_subheading']??'' !!}</h3>
                        <p>{!! $our_product_section['single']['banner_three_short_description']??'' !!}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="our-product-content our-product-content-collumn">
                    <div class="bg-layer" style="background: url('{{isset($our_product_section['single']['media']->banner_three_image)?getFile($our_product_section['single']['media']->banner_three_image->driver,$our_product_section['single']['media']->banner_three_image->path):''}}');"></div>
                    <div class="our-product-content-inner">
                        <h6>{!! $our_product_section['single']['banner_three_heading']??'' !!}</h6>
                        <h3>{!! $our_product_section['single']['banner_three_subheading']??'' !!}</h3>
                        <p>{!! $our_product_section['single']['banner_three_short_description']??'' !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- our-product -->
