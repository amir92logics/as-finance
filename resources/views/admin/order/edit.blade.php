@extends('admin.layouts.app')
@section('page_title',__('Order Edit'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="{{route('admin.order.index')}}">@lang('Orders')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Order Edit')</li>
                        </ol>
                    </nav>

                    <div class="d-sm-flex align-items-sm-center">
                        <h1 class="page-header-title">@lang('Order') {{$order->order_number}}</h1>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>


        <div class="row">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header flex-wrap card-header-content-between">
                        <h4 class="card-header-title">@lang('Products')
                        </h4>

                        <div class="product-filter d-flex flex-wrap">
                            <select class="form-select" id="categorySelect">
                                <option value="">@lang('Select category')</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">@lang($category->name)</option>
                                @endforeach
                            </select>
                            <select class="form-select" name="subcategory_id" id="sub_category">
                                <option value="">@lang('Select Subcategory')</option>
                            </select>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                        <div class="products allProducts">
                            @foreach($products as $item)
                                <!-- Media -->
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-xl">
                                            <img class="img-fluid"
                                                 src="{{getFile($item->driver , $item->thumbnail_image)}}"
                                                 alt="Image Description">
                                        </div>
                                    </div>

                                    <div class="flex-grow-1 ms-3">
                                        <div class="row">
                                            <div class="col-md-4 mb-3 mb-md-0">
                                                <a class="h5 d-block"
                                                   href="javascript:void(0)">{{optional($item->details)->title}}</a>

                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center">
                                                <h5>{{currencyPosition($item->price)}}
                                                    /<span>{{$item->quantity+0}} {{$item->quantity_unit}}</span>
                                                </h5>
                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center">
                                                <h5>{{$item->status}}</h5>
                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center text-start">
                                                <h5>{{optional($item->subcategory)->name}}</h5>
                                            </div>
                                            <div class="col col-md-1 align-self-center text-end">
                                                <button class="btn btn-white btn-sm addToCart"
                                                        data-image="{{getFile($item->driver , $item->thumbnail_image)}}"
                                                        data-unit="{{$item->quantity_unit}}"
                                                        data-quantity="{{$item->quantity}}"
                                                        data-title="{{optional($item->details)->title}}"
                                                        data-price="{{$item->price}}" data-id="{{$item->id}}">
                                                    <i class="fa-sharp fa-thin fa-cart-shopping"></i>
                                                </button>
                                            </div>
                                            <!-- End Col -->
                                        </div>
                                        <!-- End Row -->
                                    </div>
                                </div>
                                <!-- End Media -->
                                <hr>
                            @endforeach
                        </div>
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->

            </div>

            <div class="col-lg-5">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Order Items')</h4>
                    </div>
                    <!-- End Header -->
                    <form action="{{route('admin.update.order',$order->id)}}" method="post">
                        @csrf
                        <!-- Body -->
                        <div class="card-body">
                            <div class="products orderItems">
                                @php
                                    $totalQuantity = [];
                                    $subTotal = [];
                                @endphp
                                @foreach($order->orderItem as $key =>  $item)
                                    @php
                                        $totalQuantity[optional($item->product)->id] = $item->quantity;
                                        $subTotal[optional($item->product)->id] = optional($item->product)->price * $item->quantity;
                                    @endphp
                                        <!-- Media -->

                                    <div class="d-flex" id="orderItm{{optional($item->product)->id}}">
                                        <input type="hidden" id="orderItm_product_id{{optional($item->product)->id}}"
                                               name="products[{{optional($item->product)->id}}][product_id]"
                                               value="{{optional($item->product)->id}}">
                                        <input type="hidden" id="orderItm_quantity{{optional($item->product)->id}}"
                                               name="products[{{optional($item->product)->id}}][quantity]" value="{{$item->quantity}}">
                                        <input type="hidden" id="orderItm_price{{optional($item->product)->id}}"
                                               name="products[{{optional($item->product)->id}}][price]" value="{{$item->price}}">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-xl">
                                                <img class="img-fluid"
                                                     src="{{getFile(optional($item->product)->driver , optional($item->product)->thumbnail_image)}}"
                                                     alt="Image Description">
                                            </div>
                                        </div>

                                        <div class="flex-grow-1 ms-3">
                                            <div class="row">
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <a class="h5 d-block"
                                                       href="javascript:void(0)">{{optional($item->product->details)->title}}</a>
                                                    <span>{{currencyPosition(optional($item->product)->price)}}
                                                            /{{optional($item->product)->quantity+0}} {{optional($item->product)->quantity_unit}}</span>
                                                </div>
                                                <div class="col col-md-2 align-self-center">
                                                    <h5 id="orderItm_subtotal{{optional($item->product)->id}}">{{currencyPosition($item->price * $item->quantity)}}</h5>
                                                </div>
                                                <div class="col col-md-4 align-self-center">
                                                    <div class="increment-decrement">
                                                        <div class="count-single">
                                                            <button type="button" class="decrement"
                                                                    data-id="{{optional($item->product)->id}}"><i
                                                                    class="fa-light fa-minus"></i></button>
                                                            <span class="number"
                                                                  id="orderItm_totalQuantity{{optional($item->product)->id}}">{{$item->quantity}}</span>
                                                            <button type="button" class="increment"
                                                                    data-id="{{optional($item->product)->id}}"><i
                                                                    class="fa-light fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" data-id="{{optional($item->product)->id}}" data-price="{{optional($item->product)->price}}" class="btn btn-white btn-sm removeOrderItem"><i class="fal fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                @endforeach
                            </div>
                        </div>
                        <!-- End Body -->

                        <div class="card-footer">
                            <button class="btn btn-primary"> @lang('Save Changes') </button>
                        </div>
                    </form>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>

        var subtotal = {!! json_encode($subTotal) !!};  // This will create a valid JavaScript number or object
        var totalQuantity = {!! json_encode($totalQuantity) !!};  // Same here

        // Add to Cart functionality
        $(document).on('click', '.addToCart', function () {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const price = Number($(this).data('price'));
            const quantity = $(this).data('quantity');
            const unit = $(this).data('unit');
            const image = $(this).data('image');

            // Check if the item already exists
            if ($(`#orderItm${id}`).length > 0) {
                // Item exists, increment quantity and update subtotal
                let orderItemQuantityElem = $(`#orderItm_totalQuantity${id}`);
                let orderItemQuantity = Number(orderItemQuantityElem.text()) + 1;
                orderItemQuantityElem.text(orderItemQuantity);
                $(`#orderItm_quantity${id}`).val(orderItemQuantity);
                // Update subtotal
                const itemPrice = Number($(`#orderItm_price${id}`).val());
                $(`#orderItm_subtotal${id}`).text(currencyPosition(orderItemQuantity * itemPrice));
                subtotal[id] = orderItemQuantity * itemPrice;
                totalQuantity[id] = orderItemQuantity;
                updateCalculation()

            } else {
                // Item doesn't exist, create a new entry
                let html = `
            <div class="d-flex" id="orderItm${id}">
                <input type="hidden" id="orderItm_product_id${id}" name="products[${id}][product_id]" value="${id}">
                <input type="hidden" id="orderItm_quantity${id}" name="products[${id}][quantity]" value="1">
                <input type="hidden" id="orderItm_price${id}" name="products[${id}][price]" value="${price}">
                <div class="flex-shrink-0">
                    <div class="avatar avatar-xl">
                        <img class="img-fluid" src="${image}" alt="Image Description">
                    </div>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <a class="h5 d-block" href="javascript:void(0)">${title}</a>
                            <span>${currencyPosition(price)} / ${Number(quantity)} ${unit}</span>
                        </div>
                        <div class="col-md-2 align-self-center">
                            <h5 id="orderItm_subtotal${id}">${currencyPosition(price)}</h5>
                        </div>
                        <div class="col-md-4 align-self-center">
                            <div class="increment-decrement">
                                <div class="count-single">
                                    <button type="button" class="decrement" data-id="${id}"><i class="fa-light fa-minus"></i></button>
                                    <span class="number" id="orderItm_totalQuantity${id}">1</span>
                                    <button type="button" class="increment" data-id="${id}"><i class="fa-light fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" data-id="${id}" data-price="${price}" class="btn btn-white btn-sm removeOrderItem"><i class="fal fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>`;
                $('.orderItems').append(html);

                subtotal[id] = price;
                totalQuantity[id] = 1;
                updateCalculation()
            }
        });

        // Increment functionality
        $(document).on('click', '.increment', function () {
            const id = $(this).data('id');
            let orderItemQuantityElem = $(`#orderItm_totalQuantity${id}`);
            let orderItemQuantity = Number(orderItemQuantityElem.text()) + 1;
            orderItemQuantityElem.text(orderItemQuantity);
            $(`#orderItm_quantity${id}`).val(orderItemQuantity);
            // Update subtotal
            const price = Number($(`#orderItm_price${id}`).val());
            $(`#orderItm_subtotal${id}`).text(currencyPosition(orderItemQuantity * price));
            subtotal[id] = orderItemQuantity * price;
            totalQuantity[id] = orderItemQuantity;
            updateCalculation()
        });

        // Decrement functionality
        $(document).on('click', '.decrement', function () {
            const id = $(this).data('id');
            let orderItemQuantityElem = $(`#orderItm_totalQuantity${id}`);
            let orderItemQuantity = Math.max(Number(orderItemQuantityElem.text()) - 1, 1); // Prevent quantity < 1
            orderItemQuantityElem.text(orderItemQuantity);
            $(`#orderItm_quantity${id}`).val(orderItemQuantity);

            // Update subtotal
            const price = Number($(`#orderItm_price${id}`).val());
            $(`#orderItm_subtotal${id}`).text(currencyPosition(orderItemQuantity * price));
            subtotal[id] = orderItemQuantity * price;
            totalQuantity[id] = orderItemQuantity;
            updateCalculation()
        });

        $(document).on('click','.removeOrderItem',function (){
            let id = $(this).data('id');
            let price = $(this).data('price');
            let quantity = $(`#orderItm_totalQuantity${id}`).text();
            delete subtotal[id];
            delete totalQuantity[id];
            $(`#orderItm${id}`).remove()
            updateCalculation()
        })

        // Currency formatting function
        function currencyPosition(amount) {

            var currencyPosition = @json(basicControl()->is_currency_position);
            var has_space_between_currency_and_amount = @json(basicControl()->has_space_between_currency_and_amount);
            var currency_symbol = @json(basicControl()->currency_symbol);
            var base_currency = @json(basicControl()->base_currency);
            amount = parseFloat(amount).toFixed(2);
            if (currencyPosition === 'left' && has_space_between_currency_and_amount) {
                return currency_symbol + '  ' + amount;
            } else if (currencyPosition === 'left' && !has_space_between_currency_and_amount) {
                return currency_symbol + ' ' + amount;
            } else if (currencyPosition === 'right' && has_space_between_currency_and_amount) {
                return amount + '  ' + base_currency;
            } else {
                return amount + '  ' + base_currency;
            }
        }

        function updateCalculation() {
            let totalPrice = 0;
            let quantity = 0;
            for (let key in subtotal) {
                totalPrice += subtotal[key]
            }
            for (let key in totalQuantity) {
                quantity += totalQuantity[key]
            }




            $.ajax({
                url: "{{ route('admin.get.order.calculation') }}?order_id={{ $order->id }}&quantity=" + quantity + "&price=" + totalPrice,
                method: "GET",
                success: function (res) {
                    $('.subtotal').text(currencyPosition(totalPrice));
                    $('.deliveryCharge').text(currencyPosition(res.delivery_charge));
                    $('.vat').text(currencyPosition(res.vat));
                    if (res?.discount) {
                        $('.discount').text(currencyPosition(res.discount.discountWithOutCurrency));
                        $('.total').text(currencyPosition((totalPrice + Number(res.delivery_charge) + Number(res.vat)) - Number(res.discount.discountWithOutCurrency)))
                    } else {
                        $('.discount').text(currencyPosition(0));
                        $('.total').text(currencyPosition(totalPrice + Number(res.delivery_charge) + Number(res.vat)))
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });


        }
        $(document).on('change','#categorySelect', function () {
            $('select[name="subcategory_id"]').html('');
            var category_id = $('#categorySelect').find(":selected").val();
            $.ajax({
                url: "{{ route('admin.product.getSubcategory') }}",
                type: "get",
                data: {
                    category_id: category_id,
                },
                success: function (res) {
                    var response = res.data;
                    $('select[name="subcategory_id"]').append('<option value="">Select Subcategory</option>');
                    $.each(response, function (key, value) {
                        $('select[name="subcategory_id"]').append('<option value=" ' + value.id + '">' + value.name + '</option>');
                    })
                },
            });
        })

        $(document).on('change','#sub_category',function (){
                let sucategory = $(this).val();
            $.ajax({
                url: '{{route('admin.filter.product')}}',
                type: 'GET',
                data: {
                    subcategory_id: sucategory,
                },
                dataType: 'json',
                beforeSend: function () {
                    Notiflix.Loading.dots('Finding menus...');
                },
                success: function (response) {
                    Notiflix.Loading.remove();
                    console.log(response)
                    generateHtml(response.products)
                },
                error: function (error) {
                    Notiflix.Loading.remove();
                }
            });
        })

        function generateHtml(products){
            let html = '';
            products.forEach((product)=>{
                html += ` <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-xl">
                                            <img class="img-fluid"
                                                 src="${product.image}"
                                                 alt="Image Description">
                                        </div>
                                    </div>

                                    <div class="flex-grow-1 ms-3">
                                        <div class="row">
                                            <div class="col-md-4 mb-3 mb-md-0">
                                                <a class="h5 d-block"
                                                   href="javascript:void(0)">${product?.details?.title}</a>

                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center">
                                                <h5>${currencyPosition(product.price)}
                                                    /<span>${product?.quantity + 0} ${product?.quantity_unit}</span>
                                                </h5>
                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center">
                                                <h5>${product?.status}</h5>
                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center text-start">
                                                <h5>${product?.subcategory?.name}</h5>
                                            </div>
                                            <div class="col col-md-1 align-self-center text-end">
                                                <button class="btn btn-white btn-sm addToCart"
                                                        data-image="${product?.image}"
                                                        data-unit="${product?.quantity_unit}"
                                                        data-quantity="${product?.quantity}"
                                                        data-title="${product?.details?.title}"
                                                        data-price="${product?.price}" data-id="${product?.id}">
                                                    <i class="fa-sharp fa-thin fa-cart-shopping"></i>
                                                </button>
                                            </div>
                                            <!-- End Col -->
                                        </div>
                                        <!-- End Row -->
                                    </div>
                                </div>
                                <hr>`
            })

            $('.allProducts').html(html);
        }
    </script>
@endpush

@push('css')
    <style>
        .products {
            height: 700px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
        }

        .orderItems {
            height: 635px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
        }

        .products ::-webkit-scrollbar {
            width: 5px !important;
            border-radius: 5px !important;
        }

        .incriment-dicriment .count-single {
            margin-top: 0;
        }

        .count-single {
            display: inline-flex;
            align-items: center;
            border: 1px solid #D9D9D9;
            border-radius: 2px;
        }

        .count-single button, .count-single span {
            width: 30px;
            height: 30px;
            background: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .count-single button, .count-single span {
            width: 30px;
            height: 30px;
            background: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .increment {
            border: none;
            border-left: 1px solid #D9D9D9;
        }

        .decrement {
            border: none;
            border-right: 1px solid #D9D9D9;
        }

        .bold {
            font-weight: 500;
            border: 1px solid black;
        }

        .product-filter{
            justify-content: space-between;
            gap: 10px;
        }

        .product-filter select{
            width: 200px;
        }

    </style>
@endpush

