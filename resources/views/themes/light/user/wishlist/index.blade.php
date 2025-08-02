@extends($theme.'layouts.user')
@section('title',trans('Wishlist'))
@section('content')
    <div class="pagetitle">
        <h3 class="mb-1">@lang('Wishlist')</h3>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('user.dashboard')}}">@lang('Home')</a></li>
                <li class="breadcrumb-item active">@lang('Wishlist')</li>
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
                            <th scope="col">@lang('No')</th>
                            <th scope="col">@lang('Image')</th>
                            <th scope="col">@lang('Title')</th>
                            <th scope="col">@lang('Price')</th>
                            <th scope="col">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($wishlists as $key => $item)
                            <tr>
                                <td data-label="@lang('No')">{{$wishlists->firstItem()+$key}}</td>
                                <td data-label="@lang('Image')"><img src="{{getFile(optional($item->product)->driver,optional($item->product)->thumbnail_image)}}" class="img-thumbnail" alt="product image"> </td>
                                <td data-label="@lang('Title')">{{optional($item->product->details)->title}}</td>
                                <td data-label="@lang('Price')"> {{currencyPosition(optional($item->product)->price)}} </td>
                                <td data-label="@lang('Action')">
                                    <a href="{{route('product.details',optional($item->product->details)->slug??'slug')}}"
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
                @if(count($wishlists??[]) == 0)
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
            {{ $wishlists->appends($_GET)->links($theme.'partials.user-pagination') }}
        </nav>
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
