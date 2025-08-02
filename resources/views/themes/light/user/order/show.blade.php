@extends($theme.'layouts.user')
@section('title',trans('Order Details'))
@section('content')
    <div class="pagetitle">
        <h3 class="mb-1">@lang('Order Details')</h3>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('user.dashboard')}}">@lang('Home')</a></li>
                <li class="breadcrumb-item active">@lang('Order Details')</li>
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
                            <th scope="col">@lang('SL')</th>
                            <th scope="col">@lang('Image')</th>
                            <th scope="col">@lang('Title')</th>
                            <th scope="col">@lang('Price')</th>
                            <th scope="col">@lang('Quantity')</th>
                            <th scope="col">@lang('Subtotal')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orderItems as $key => $item)
                            <tr>
                                <td data-label="@lang('SL')">{{$key + 1}}</td>
                                <td data-label="@lang('Image')"><img src="{{getFile(optional($item->product)->driver,optional($item->product)->thumbnail_image)}}" alt="order item" class="img-thumbnail w-50" /></td>
                                <td data-label="@lang('Title')">{{optional($item->product->details)->title}}</td>
                                <td data-label="@lang('Price')"> {{currencyPosition($item->price + 0)}} </td>
                                <td data-label="@lang('Quantity')">
                                    {{$item->quantity}}
                                </td>
                                <td data-label="@lang('Subtotal')">
                                    {{currencyPosition($item->quantity * $item->price)}}
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
                @if(count($orderItems??[]) == 0)
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
@endsection

@push('style')
    <style>
        .img-thumbnail {
            padding: .25rem;
            background-color: var(--bs-body-bg);
            border: var(--bs-border-width) solid var(--bs-border-color);
            border-radius: var(--bs-border-radius);
            height: 60px;
            width: 70px !important;
        }
    </style>
@endpush
