@extends($theme.'layouts.app')
@section('title',trans('Products'))
@section('content')
    <!-- wishlist -->
    <section class="cart-section">
        <div class="container">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 table-column">
                    <div class="mk_table-outer">
                        <table class="mk_cart-table">
                            <thead class="cart-header">
                            <tr>
                                <th class="prod-column">@lang('Product Details')</th>
                                <th class="nbsp-3">&nbsp;</th>
                                <th class="nbsp-3">&nbsp;</th>
                                <th class="price">@lang('Unit Price')</th>
                                <th class="quantity">@lang('Quantity')</th>
                                <th>@lang('Total')</th>
                                <th>@lang('Remove')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse(session('cart')??[] as $item)
                                <tr class="table-row-1 cart-single cart-single-1" id="cartItems{{$item['id']}}">
                                    <td colspan="3" class="prod-column">
                                        <div class="column-box">
                                            <div class="prod-thumb">
                                                <img src="{{$item['image']}}" alt="image">
                                            </div>
                                            <div class="prod-title">
                                                {{$item['name']}}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price">{{currencyPosition($item['price'])}}
                                        /{{$item['product_quantity']}} {{$item['quantity_unit']}}</td>
                                    <td class="qty">
                                        <div class="incriment-dicriment">
                                            <div class="count-single">
                                                <button type="button" class="decrement" data-id="{{$item['id']}}"><i
                                                        class="fa-light fa-minus"></i></button>
                                                <span class="number" id="no1">{{$item['quantity']}}</span>
                                                <button type="button" class="increment" data-id="{{$item['id']}}"><i
                                                        class="fa-light fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="sub-total">{{currencyPosition($item['price'] * $item['quantity'])}}</td>
                                    <td>
                                        <div class="remove-btn remove-btn-1 cart-clear-1 removeCartItem"
                                             data-id="{{$item['id']}}">
                                            <i class="fa-light fa-trash-can"></i>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <form id="couponForm1">
                    <div class="mk_othre-content">
                        <div class="mk_coupon-box">
                            <h5>@lang('Got Any Coupon')?</h5>
                            <input type="text" id="coupon1" placeholder="Input Code">
                            <button type="submit" class="btn-1">@lang('Apply Coupon') <span></span></button>
                        </div>
                    </div>
                        <span class="invalid-feedback d-block" id="coupon-error1" role="alert"> </span>
            </form>
            <div class="mk_cart-total">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="mk_cart-total-image">
                            <div class="bg-layer"
                                 style="background: url('{{isset($cart_section['single']['media']->banner)?getFile($cart_section['single']['media']->banner->driver,$cart_section['single']['media']->banner->path):''}}');"></div>
                            <div class="mk_cart-total-content">
                                <h4>{{$cart_section['single']['heading']??''}}</h4>
                                <h5>{{$cart_section['single']['subheading']??''}}</h5>
                                <h6><span>{{$cart_section['single']['text_1']??''}}</span> {{$cart_section['single']['text_2']??''}} </h6>
                                @if(isset($cart_section['single']['button_name']) && $cart_section['single']['button_name'])
                                    <a href="{{$cart_section['single']['media']->button_link}}" class="btn-1">{{$cart_section['single']['button_name']}} <span></span></a>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 cart-column">
                        <div class="mk_total-cart-box clearfix">
                            <h3 class="fs_24 fw_sbold lh_30 d_block">@lang('Cart Totals')</h3>
                            <ul class="list clearfix mb_30">
                                <li>@lang('Sub total')<span
                                        class="subTotalPrice">{{currencyPosition(cartTotal(session('cart')??[]))}}</span>
                                </li>
                                @if(basicControl()->vat_status)
                                    <li>@lang('Vat')<span>{{basicControl()->vat}}%</span></li>
                                @endif
                                <li>@lang('Discount')<span
                                        class="discountPrice">{{currencyPosition(session('discount'))}}</span></li>
                                <li>@lang('Total'):<span
                                        class="totalPriceIncludingVat">{{ currencyPosition((cartTotal(session('cart') ?? []) + vat(cartTotal(session('cart') ?? []))) - (session('discount') ?? 0)) }}</span>
                                </li>
                            </ul>
                            <a href="{{route('checkout')}}" class="theme-btn theme-btn-eight">@lang('Proceed to Checkout') <i
                                    class="icon-4"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- wishlist -->

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
