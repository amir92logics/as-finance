@extends('admin.layouts.app')
@section('page_title',__('Order Details'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Order details')</li>
                        </ol>
                    </nav>

                    <div class="d-sm-flex align-items-sm-center">
                        <h1 class="page-header-title">@lang('Order') {{$order->order_number}}</h1>
                        @if($order->payment_status == 1)
                            <span class="badge bg-soft-success text-success ms-sm-3">
                            <span class="legend-indicator bg-success"></span> @lang('Paid')
                        </span>
                        @elseif($order->payment_status == 2)
                            <span class="badge bg-soft-danger text-danger ms-sm-3">
                            <span class="legend-indicator bg-danger"></span> @lang('Canceled')
                        </span>
                        @else
                            <span class="badge bg-soft-warning text-warning ms-sm-3">
                            <span class="legend-indicator bg-warning"></span> @lang('Pending')
                        @endif
                        <span class="ms-2 ms-sm-3">
                        <i class="bi-calendar-week"></i> {{dateTime($order->created_at)}}
                      </span>
                    </div>

                    <div class="mt-2">
                        <div class="d-flex gap-2">
                            <a class="text-body me-3" href="javascript:" onclick="getPrintInvoice({{$order->id}})">
                                <i class="bi-printer me-1"></i> Invoice
                            </a>

                            <!-- Dropdown -->
                            <div class="dropdown">
                                <a class="text-body" href="javascript:;" id="moreOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    More options <i class="bi-chevron-down"></i>
                                </a>

                                <div class="dropdown-menu mt-1" aria-labelledby="moreOptionsDropdown">
                                    @if($order->order_status == 0)
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="handelOrderStatus(1,{{$order->id}})">
                                            <i class="fa-thin fa-badge-check dropdown-item-icon"></i> Accept
                                        </a>
                                    @elseif($order->order_status == 1)
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="handelOrderStatus(2,{{$order->id}})">
                                            <i class="bi-archive dropdown-item-icon"></i> Delivered
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="handelOrderStatus(3,{{$order->id}})">
                                            <i class="bi-x dropdown-item-icon"></i> Cancel order
                                        </a>
                                    @elseif($order->order_status == 2)
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="handelOrderStatus(3,{{$order->id}})">
                                            <i class="bi-x dropdown-item-icon"></i> Cancel order
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <!-- End Dropdown -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>


        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Order details') <span
                                class="badge bg-soft-dark text-dark rounded-circle ms-1">{{$order->orderItem?count($order->orderItem):0}}</span>
                        </h4>
                        <a class="link" href="{{route('admin.order.edit', $order->id)}}">Edit</a>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                        @foreach($order->orderItem as $item)
                            @if($item->product)
                                <!-- Media -->
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-xl">
                                            <img class="img-fluid"
                                                 src="{{getFile(optional($item)->product->driver,optional($item)->product->thumbnail_image)}}"
                                                 alt="Image Description">
                                        </div>
                                    </div>

                                    <div class="flex-grow-1 ms-3">
                                        <div class="row">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <a class="h5 d-block"
                                                   href="javascript:void(0)">@lang(optional(optional($item)->product)->details->title)</a>

                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center">
                                                <h5>{{currencyPosition($item->price)}}
                                                    /<span>{{optional($item->product)->quantity+0}} {{optional($item->product)->quantity_unit}}</span>
                                                </h5>
                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center">
                                                <h5>{{$item->quantity}}</h5>
                                            </div>
                                            <!-- End Col -->

                                            <div class="col col-md-2 align-self-center text-end">
                                                <h5>{{currencyPosition($item->price * $item->quantity)}}</h5>
                                            </div>
                                            <!-- End Col -->
                                        </div>
                                        <!-- End Row -->
                                    </div>
                                </div>
                                <!-- End Media -->
                            @endif


                            <hr>
                        @endforeach

                        <div class="row justify-content-md-end mb-3">
                            <div class="col-md-8 col-lg-7">
                                <dl class="row text-sm-end">
                                    <dt class="col-sm-6">@lang('Subtotal'):</dt>
                                    <dd class="col-sm-6">{{currencyPosition($order->subtotal)}}</dd>
                                    <dt class="col-sm-6">@lang('Delivery Charge'):</dt>
                                    <dd class="col-sm-6">{{currencyPosition($order->delivery_charge)}}</dd>
                                    @if(basicControl()->vat_status)
                                        <dt class="col-sm-6">@lang('Vat'):</dt>
                                        <dd class="col-sm-6">{{currencyPosition($order->vat)}}</dd>
                                    @endif
                                    <dt class="col-sm-6">@lang('Discount'):</dt>
                                    <dd class="col-sm-6">{{currencyPosition($order->discount)}}</dd>
                                    <dt class="col-sm-6">@lang('Total'):</dt>
                                    <dd class="col-sm-6">{{currencyPosition($order->total)}}</dd>
                                    <dt class="col-sm-6">@lang('Paid'):</dt>
                                    <dd class="col-sm-6">{{currencyPosition($order->total - $order->due)}}</dd>
                                    <dt class="col-sm-6">@lang('Dues'):</dt>
                                    <dd class="col-sm-6">{{currencyPosition($order->due+0)}}</dd>
                                </dl>
                                <!-- End Row -->
                            </div>
                            <!-- End Col -->
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->

            </div>

            <div class="col-lg-4">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Customer')</h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                        <!-- List Group -->
                        <ul class="list-group list-group-flush list-group-no-gutters">

                            <li class="list-group-item">
                                <a class="d-flex align-items-center"
                                   href="{{$order->user?route('admin.user.view.profile',$order->user->id):'javascript:void(0)'}}">
                                    @if($order->user)
                                        <div class="avatar avatar-circle">
                                            <img class="avatar-img"
                                                 src="{{getFile(optional($order->user)->image_driver, optional($order->user)->image)}}"
                                                 alt="Image Description">
                                        </div>
                                    @else
                                        <div class="avatar avatar-circle">
                                            <img class="avatar-img"
                                                 src="{{getFile('local','images')}}"
                                                 alt="Image Description">
                                        </div>

                                    @endif
                                    <div class="flex-grow-1 ms-3">
                                        @if($order->first_name)
                                            <span
                                                class="text-body text-inherit">{{$order->first_name. ' ' . $order->last_name}}</span>
                                        @else
                                            <span
                                                class="text-body text-inherit">@lang('Unknown User')</span>
                                        @endif

                                    </div>
                                    <div class="flex-grow-1 text-end">
                                        <i class="bi-chevron-right text-body"></i>
                                    </div>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('User info')</h5>
                                </div>

                                <ul class="list-unstyled list-py-2 text-body">
                                    <li>{{$order->first_name. ' ' . $order->last_name}}</li>
                                    @if($order->user)
                                        <li>@lang('Username : ')@lang('@'.optional($order->user)->username)</li>
                                    @else
                                        <li>@lang('Username : ') @lang('Guest User')</li>
                                    @endif

                                </ul>
                            </li>
                            @if($order->users)
                                <li class="list-group-item">
                                    <a class="d-flex align-items-center" href="">
                                        <div class="icon icon-soft-info icon-circle">
                                            <i class="bi-basket"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <span class="text-body text-inherit">{{count($userOrders)}} orders</span>
                                        </div>
                                        <div class="flex-grow-1 text-end">
                                            <i class="bi-chevron-right text-body"></i>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            @if($order->gateway)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>@lang('Contact info')</h5>
                                    </div>

                                    <ul class="list-unstyled list-py-2 text-body">
                                        @if($order->users)
                                            <li>
                                                <i class="bi-at me-2"></i>@lang(optional($order->user)->email)
                                            </li>
                                        @endif
                                        <li><i class="bi-phone me-2"></i>@lang($order->phone)</li>
                                    </ul>
                                </li>
                            @endif
                            @if($order->coupon_code)
                                <li class="list-group-item">
                                    <a class="d-flex align-items-center" href="javascript:void(0)">
                                        <div class="flex-grow-1 ms-3">
                                            <span class="text-body text-inherit">@lang('Coupon')</span>
                                        </div>
                                        <div class="flex-grow-1 text-end">
                                            <i class="bi-chevron-right text-body">@lang($order->coupon_code)</i>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            @if($order->area_id)

                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>@lang('Delivery Address')</h5>
                                    </div>

                                    <span class="text-body">
                                       @lang(optional($order->area)->area_name) ,
                                      </span>
                                    <span class="text-body">
                                       @lang($order->address)
                                      </span>
                                </li>
                            @endif
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Order Status')</h5>
                                    <span
                                        class='badge  text-uppercase bg-soft-success text-primary bg-soft-primary text-primary'><span
                                            class='legend-indicator bg-primary'></span>

                                    @if($order->order_status == 0)
                                            @lang('Pending')
                                        @elseif($order->order_status == 1)
                                            @lang('Order Placed')
                                        @elseif($order->order_status == 2)
                                            @lang('Delivered')
                                        @else
                                            @lang('Canceled')
                                        @endif
                                    </span>

                                </div>

                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Billing Details')</h5>
                                </div>
                                @if($order->gateway_id != null)
                                    @if($order->gateway_id == 2000 )
                                        <div class=" d-flex">
                                            <span
                                                class="badge badge-primary bg-primary">@lang('Cash on Delivery')</span>
                                        </div>

                                    @elseif($order->gateway_id == 3000)
                                        <div class=" d-flex">
                                            <span class="badge badge-primary bg-primary">@lang('Wallet')</span>
                                        </div>

                                    @else
                                        <div class="avatar avatar-circle">
                                            <img class="avatar-img"
                                                 src="{{getFile(optional($order->gateway)->driver, optional($order->gateway)->image)}}"
                                                 alt="Image Description">
                                        </div>
                                    @endif
                                @endif
                            </li>
                        </ul>
                        <!-- End List Group -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        function getPrintInvoice(id) {
            var url = '{{route('admin.order.show.invoice')}}';
            url = url + `/${id}`
            var LeftPosition = (screen.width) ? (screen.width - 500) / 2 : 0;
            var TopPosition = (screen.height) ? (screen.height - 700) / 2 : 0;
            var settings = 'height=700,width=500,top=' + TopPosition + ',left=' + LeftPosition + ',scrollbars=yes,resizable';
            window.open(url, 'popUpWindow', settings);
        }

        const orderStatus = {
            1 : 'Order Placed',
            2 : 'Delivered',
            3 : 'Cancelled'
        }
        function handelOrderStatus(status, orderId) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{route('admin.update.order.status')}}",
                data: {
                    status: status,
                    orderId: orderId,
                },
                method: "PUT",
                datatType: 'json',
                success: function (data) {


                    $('#order_status_' + orderId).html(` `)

                    if (data.status == 'Accept'){

                        let orderStatusOption = Object.entries(orderStatus).map(
                            ([key, status]) => `<option value="${key}" ${key == data.status ? 'selected' : ''}>${status}</option>`
                        ).join('');

                        $('#order_status_' + orderId).append(`${orderStatusOption}`);
                    }else if(data.status == 'Delivered'){
                        let orderStatusOption =  `
                        <option value="2" ${data.status == 'Delivered' ? 'selected' : ''}>Delivered</option>
                        <option value="3" ${data.status == 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                        `;
                        $('#order_status_' + orderId).append(`${orderStatusOption}`);
                    }else{
                        let orderStatusOption =  `
                        <option value="3" selected>Cancelled</option>
                        `;
                        $('#order_status_' + orderId).append(`${orderStatusOption}`);

                        $('.order_status_' + orderId).html(` `)
                        $('.order_status_' + orderId).append(`<option value="3" selected>Cancelled</option>`)
                        $('#paymentStatus_' + orderId).html(" ");
                        $('#paymentStatus_' + orderId).append('<span class="badge bg-soft-danger text-danger"><span class="legend-indicator bg-danger"></span>Cancelled</span>')
                    }
                    if (data.error === 1){
                        Notiflix.Notify.failure(data.message);
                    }else{
                        Notiflix.Notify.success("Order status updated");
                    }
                    location.reload()

                },
                error:function (err){
                    Notiflix.Loading.remove();
                    Notiflix.Notify.failure(err.statusText);
                },

            });
        }
    </script>
@endpush

