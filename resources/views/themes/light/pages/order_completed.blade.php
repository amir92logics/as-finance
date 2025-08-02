@extends($theme.'layouts.app')
@section('title',trans('Order Invoice'))
@section('content')

    <!-- Order Complete -->
    <section class="order-complate">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="order-complate-container">
                        <div class="order-complate-body">
                            <div class="order-complate-icon">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="order-complate-title">
                                <h3>@lang('Your Order is Completed') !</h3>
                                <p>@lang('Thank you, Your order has been received').</p>
                            </div>
                        </div>
                        <div class="order-complate-footer">
                            <div class="order-complate-footer-item">
                                <p>@lang('Order Number')</p>
                                <h5>{{$order->order_number}}</h5>
                            </div>
                            <div class="order-complate-footer-item">
                                <p>@lang('Date') </p>
                                <h5>{{dateTime($order->created_at)}}</h5>
                            </div>
                            <div class="order-complate-footer-item">
                                <p>@lang('Total')</p>
                                <h5>{{currencyPosition($order->total+0)}}</h5>
                            </div>
                            <div class="order-complate-footer-item">
                                <p>@lang('Payment Method')</p>
                                <h5>
                                    @if($order->gateway_id == 2000)
                                        @lang('Cash on Delivery')
                                    @elseif($order->gateway_id == 2100)
                                        @lang('Wallet')
                                    @else
                                        {{optional($order->gateway)->name}}
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="order-details">
                        <div class="order-details-header text-center">
                            <h3>@lang('Order Details')</h3>
                        </div>
                        <div class="order-details-body">
                            <ul>
                                <li>
                                    <h6>@lang('Product')</h6>
                                    <h6>@lang('Prices')</h6>
                                </li>
                                <li>
                                    <ul>
                                        @foreach($order->orderItem as $item)
                                            <li>
                                                <p>{{optional($item->product->details)->title}} </p>
                                                <p >{{currencyPosition($item->price * $item->quantity)}}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                                <li>
                                    <h6>@lang('Subtotal')</h6>
                                    <h6>{{currencyPosition($order->subtotal+0)}}</h6>
                                </li>
                                <li>
                                    <h6>@lang('Delivery Charge')</h6>
                                    <h6>{{currencyPosition($order->delivery_charge+0)}}</h6>
                                </li>
                                <li>
                                    <h6>@lang('Discount')</h6>
                                    <h6>{{currencyPosition($order->discount+0)}}</h6>
                                </li>
                            </ul>
                        </div>
                        <div class="order-details-footer">
                            <h5>@lang('Total Amount')</h5>
                            <span>{{currencyPosition($order->total+0)}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Order Complete -->


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
