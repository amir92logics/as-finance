@if(basicControl()->ecommerce)
    <!-- search popup -->
    <div class="theme-btn search-toggler">
        <img src="{{asset($themeTrue.'images/icons/shopping-bag.png')}}" alt="icon">
        <h4 class="search-toggler-items"><span class="cartItems">{{count(session('cart')??[])}}</span> <span> ITEMS</span>
        </h4>
        <h5 class="search-toggler-number subTotalPrice">{{currencyPosition(cartTotal(session('cart')??[]))}}</h5>
    </div>

    <div id="search-popup" class="search-popup">
        <div class="popup-inner">
            <div class="overlay-layer"></div>
            <div class="search-container">
                <div class="search-popup-header">
                    <div class="search-popup-header-title">
                        <h5>@lang('Your Bag') <span>(<span class="cartItems">{{count(session('cart')??[])}}</span>)</span></h5>
                    </div>
                    <div class="close-search theme-btn">@lang('Close')</div>
                </div>
                <div class="search-popup-body">

                    <ul id="showHtml">

                        @forelse(session('cart')??[] as $item)
                            <li class="search-bag-items" id="cartItem{{$item['id']}}">
                                <div class="search-bag-content">
                                    <div class="search-bag-image">
                                        <img src="{{$item['image']}}" alt="product">
                                    </div>
                                    <div class="search-bag-title">
                                        <h6>{{$item['name']}} </h6>
                                        <div class="search-bag-count">
                                            <p>{{currencyPosition($item['price'])}}/{{$item['product_quantity']+0}} {{$item['quantity_unit']}}</p>
                                            <div class="incriment-dicriment">
                                                <div class="count-single">
                                                    <button type="button"  class="decrement" data-id="{{$item['id']}}"><i class="fa-light fa-minus"></i></button>
                                                    <span class="number" id="no{{$item['id']}}">{{$item['quantity']}}</span>
                                                    <button type="button" class="increment" data-id="{{$item['id']}}"><i class="fa-light fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="serch-bag-amount">
                                    <h6>{{currencyPosition($item['quantity'] * $item['price'])}}</h6>
                                </div>
                                <div class="serch-bag-close">
                                    <div class="close-btn removeCartItem" data-id="{{$item['id']}}">
                                        <i class="fa-regular fa-xmark"></i>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="search-popup-empty">
                                <div class="empty-image">
                                    <img src="{{asset($themeTrue.'images/empty.png')}}" alt="">
                                    <h5>@lang('No Item Added the cart')</h5>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
                <div class="search-popup-footer">
                    <h6><i class="fa-light fa-circle-check"></i> @lang('Have any Special code')?</h6>
                    <form id="couponForm">
                        @csrf
                        <div class="search-popup-form">
                            <input type="text" name="code" id="coupon" placeholder="Discount Code">
                            <button  class="search-popup-form-btn" type="submit">@lang('Go')</button>
                        </div>
                        <span class="invalid-feedback d-block" id="coupon-error" role="alert"></span>
                    </form>
                    <h5>@lang('Total') <span>@if(basicControl()->vat_status) (@lang('before vat')) @endif</span> <span class="search-popup-footer-total totalPrice">{{session('discountPrice')?currencyPosition(session('discountPrice')):currencyPosition(cartTotal(session('cart')??[]))}}</span>
                    </h5>
                    <a href="{{route('cart')}}" class="btn-1">@lang('View Cart') <span></span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- search popup -->

@endif
