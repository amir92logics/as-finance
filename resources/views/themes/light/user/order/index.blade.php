@extends($theme.'layouts.user')
@section('title',trans('Orders'))
@section('content')
    <div class="pagetitle">
        <h3 class="mb-1">@lang('Orders')</h3>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('user.dashboard')}}">@lang('Home')</a></li>
                <li class="breadcrumb-item active">@lang('Orders')</li>
            </ol>
        </nav>
    </div>

    <div class="card mt-50">
        <div class="card-body">
            <div class="cmn-table">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th scope="col">@lang('Order')</th>
                            <th scope="col">@lang('Total')</th>
                            <th scope="col">@lang('Status')</th>
                            <th scope="col">@lang('Date')</th>
                            <th scope="col">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $key => $order)
                            <tr>
                                <td data-label="@lang('Order')">{{$order->order_number}}</td>
                                <td data-label="@lang('Total')">{{currencyPosition($order->total + 0)}}</td>
                                <td data-label="@lang('Status')">{!! $order->orderStatus() !!}</td>
                                <td data-label="@lang('Date')"> {{dateTime($order->created_at)}} </td>
                                <td data-label="@lang('Action')">
                                    <a href="{{route('user.orderItems',$order->id)}}"
                                       class="btn-1 ">
                                        <i class="fal fa-eye"></i>
                                        @lang('View') <span></span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
                @if(count($orders??[]) == 0)
                    <div class="row d-flex text-center justify-content-center">
                        <div class="col-4">
                            <img src="{{ asset('assets/admin/img/oc-error.svg') }}" id="no-data-image" class="no-data-image" alt="" srcset="">
                            <p>@lang('No data to show')</p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
    <div class="pagination-section">
        <nav aria-label="...">
            {{ $orders->appends($_GET)->links($theme.'partials.user-pagination') }}
        </nav>
    </div>
@endsection
