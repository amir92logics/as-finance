@extends($theme.'layouts.app')
@section('title',trans('Checkout'))
@section('content')

    <!-- checkout -->
    <section class="checkout">
        <div class="container">
            <form action="{{route('order')}}" method="post">
                @csrf
            <div class="row">
                <div class="col-lg-6">
                    <div class="billing-container">
                        <h5>@lang('Billing Details')</h5>

                            <div class="billing-form">
                                <div class="row">
                                    <div class="col-lg-6 mb-3">
                                        <input type="text" name="first_name" class="billing-input mb-0" placeholder="@lang('First Name')*" required>
                                        @error('first_name')
                                            <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input type="text" name="last_name" class="billing-input mb-0" placeholder="@lang('Last Name')*" required>
                                        @error('last_name')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input type="email" name="email" class="billing-input mb-0" placeholder="@lang('Email')*" required>
                                        @error('email')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input type="number" name="phone" class="billing-input mb-0" placeholder="@lang('Phone')*" required>
                                        @error('phone')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input type="text" name="address" class="billing-input mb-0" placeholder="@lang('Address')*" required>
                                        @error('address')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                       <select class="form-control billing-input mb-0" name="area" id="area" required>
                                           <option  disabled>@lang('Select Area')</option>
                                           @foreach($areas as $area)
                                               <option value="{{$area->id}}">@lang($area->area_name)</option>
                                           @endforeach
                                       </select>
                                        @error('area')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input type="text" name="city" class="billing-input mb-0" placeholder="@lang('City/Town')*" required>
                                        @error('city')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input type="text" name="zip" class="billing-input mb-0" placeholder="@lang('ZIP Code')*">
                                        @error('zip')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="col-lg-12">
                                        <textarea  class="billing-input" name="additional_information" placeholder="@lang('Additional Information')"></textarea>
                                        @error('additional_information')
                                        <span class="d-block text-danger mt-0">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="order-summery">
                        <h5>@lang('Order Summery')</h5>
                        <div class="order-summery-bottom">
                            <ul class="list">
                                <li>@lang('Sub total')<span class="subTotalPrice">{{currencyPosition(cartTotal(session('cart')??[]))}}</span></li>
                                @if(basicControl()->vat_status)
                                    <li>@lang('Vat')<span>{{basicControl()->vat}}%</span></li>
                                @endif
                                <li>@lang('Delivery Charge')<span class="shippingCharge">{{currencyPosition(0)}}</span></li>
                                <li>@lang('Discount')<span class="discountPrice">{{currencyPosition(session('discount'))}}</span></li>
                                <li>@lang('Total') ({{basicControl()->base_currency}})<span class="TotalPrice">$970.00</span></li>
                            </ul>
                        </div>
                    </div>
                        <div class="billing-container">
                            <h5>@lang('Payment')</h5>
                            <ul class="billing-list">
                                <li class="item">
                                    <input class="form-check-input" value="checkout" type="radio" name="payment_method" id="wallet" checked>
                                    <label class="form-check-label" for="wallet">
                                        <span class="payment-list-content">
                                            <span class="payment-list-info">
                                                <span class="payment-list-title">@lang('Checkout')</span>
                                            </span>
                                        </span>
                                    </label>
                                </li>
                                <li class="item">
                                    <input class="form-check-input" value="wallet" type="radio" name="payment_method" id="walletTwo" >
                                    <label class="form-check-label" for="walletTwo">
                                        <span class="payment-list-content">
                                            <span class="payment-list-info">
                                                <span class="payment-list-title">@lang('Wallet Payment')</span>
                                            </span>
                                        </span>
                                    </label>
                                </li>
                                <li class="item">
                                    <input class="form-check-input" value="cash" type="radio" name="payment_method" id="walletThree" >
                                    <label class="form-check-label" for="walletThree">
                                        <span class="payment-list-content">
                                            <span class="payment-list-info">
                                                <span class="payment-list-title">@lang('Cash on delivery')</span>
                                            </span>
                                        </span>
                                    </label>
                                </li>
                            </ul>
                            <button type="submit" class="btn-1">@lang('Continue to Payment')</button>
                        </div>

                </div>
            </div>
            </form>
        </div>
    </section>
    <!-- checkout -->


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
        $(document).on('change','#area',function (){
            $.ajax({
                url: "{{route('shipping.charge')}}",
                method: "post",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: "application/json",
                data: JSON.stringify({
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    area_id: $(this).val(),
                }),
                success: function (res) {
                    $('.shippingCharge').text(currencyPosition(res.charge));
                    let total = '{{(cartTotal(session('cart') ?? []) + vat(cartTotal(session('cart') ?? []))) - (session('discount') ?? 0)}}';
                    $('.TotalPrice').text(currencyPosition(Number(total) + Number(res.charge)))
                },
                error: function (error) {
                    var errorMessage = error.responseJSON.errors.charge[0];

                    console.log(errorMessage)
                }
            });
        })

        $(document).ready(function (){

            $.ajax({
                url: "{{route('shipping.charge')}}",
                method: "post",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: "application/json",
                data: JSON.stringify({
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    area_id: $('#area').val(),
                }),
                success: function (res) {
                    $('.shippingCharge').text(currencyPosition(res.charge));
                    let total = '{{(cartTotal(session('cart') ?? []) + vat(cartTotal(session('cart') ?? []))) - (session('discount') ?? 0)}}';

                    $('.TotalPrice').text(currencyPosition(Number(total) + Number(res.charge)))
                },
                error: function (error) {
                    var errorMessage = error.responseJSON.errors.charge[0];
                }
            });
        })
    </script>
@endpush

@push('style')
    <style>
        .theme-btn{
            display: none;
        }
    </style>
@endpush
