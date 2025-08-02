
@extends('admin.layouts.app')
@section('page_title', __('Coupon'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">
                                    @lang('Dashboard')
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a class="breadcrumb-link" href="{{ route('admin.product.coupon') }}">
                                    @lang('Coupon')
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Edit')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Coupon Edit Form')</h1>
                </div>
            </div>
        </div>
        <form action="{{ route('admin.product.coupon.update', $coupon->id) }}" method="POST">
            @csrf
            <div class="row d-flex justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 h5 text-secondary">@lang('Add Coupon Information')</h5>
                        </div>
                        <div class="card-body">
                            <div id="coupon_form">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label"
                                           for="code">@lang('Coupon code')</label>
                                    <div class="col-lg-9">
                                        <div class="d-flex">
                                            <input type="text" placeholder="Coupon code" id="code" name="coupon_code"
                                                   class="form-control coupon_code w-75 me-3" value="{{ old('coupon_code', $coupon->coupon_code) }}" autocomplete="off">
                                            <button class="generateBtn btn btn-sm btn-success" type="button">@lang('Generate code')</button>
                                        </div>
                                        <div class="invalid-feedback d-inline-block">
                                            @error('coupon_code') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label"
                                           for="code">@lang('Minimum Order Price')</label>
                                    <div class="col-lg-9">
                                        <input type="text" placeholder="Minimum Order Price" id="minimum_order_price" name="minimum_order_price"
                                               class="form-control" value="{{ old('minimum_order_price', $coupon->minimum_order_price) }}" autocomplete="off">
                                        <div class="invalid-feedback d-inline-block">
                                            @error('minimum_order_price') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label"
                                           for="code">@lang('Expiration Validity')</label>
                                    <div class="col-lg-9">
                                        <div class="input-group mb-3 custom">
                                            <input type="text" id="filter_date_range" name="expiration_validity" value="{{ $expirationValidity }}"
                                                   class="js-flatpickr form-control"
                                                   placeholder="Select dates"
                                                   data-hs-flatpickr-options='{
                                                                 "dateFormat": "d/m/Y",
                                                                 "mode": "range"
                                                               }' aria-describedby="flatpickr_filter_date_range">
                                            <span class="input-group-text" id="flatpickr_filter_date_range">
                                                                <i class="bi bi-arrow-counterclockwise"></i>
                                                            </span>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback d-inline-block">
                                        @error('expiry_date') @lang($message) @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label" for="code">@lang('Discount')</label>
                                    <div class="col-lg-9">
                                        <div class="input-group">
                                            <input type="number" placeholder="Discount"
                                                   name="discount" value="{{ old('discount', $coupon->discount) }}"
                                                   class="form-control me-2">
                                            <select
                                                class="js-select form-select @error('discount_type') is-invalid @enderror"
                                                name="discount_type" id="discount_type">
                                                <option
                                                    value="Fixed" {{ $coupon->discount_type == 'Fixed' ? 'selected' : '' }}>@lang('Amount')</option>
                                                <option
                                                    value="Percent" {{ $coupon->discount_type == 'Percent' ? 'selected' : '' }}>@lang('Percent')</option>
                                            </select>
                                            <div class="invalid-feedback d-inline-block">
                                                @error('discount') @lang($message) @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label" for="code">@lang('Maximum Order')</label>
                                    <div class="col-lg-9">
                                        <input type="text" placeholder="Maximum Order" id="maximum_order"
                                               name="maximum_order"
                                               class="form-control" value="{{ old('maximum_order',$coupon->maximum_order) }}"
                                               autocomplete="off">
                                        <div class="invalid-feedback d-inline-block">
                                            @error('maximum_order') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-from-label" for="code">@lang('Applicable Products') <sub>(optional)</sub></label>
                                    <div class="col-lg-9">
                                        <div class="tom-select-custom tom-select-custom-with-tags">
                                            <select class="js-select form-select" name="applicable_products[]" autocomplete="off" multiple
                                                    data-hs-tom-select-options='{
                                                                        "placeholder": "Select a products..."
                                                                      }'>
                                                <option disabled>Select a products...</option>
                                                @foreach($products as $product)
                                                    <option value="{{$product->id}}" @selected(in_array($product->id,$applicableProducts))>{{optional($product->details)->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3 mt-3">
                                <button type="submit" class="btn btn-primary col-md-2">@lang('Save')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush

@push('script')
    <script>
        $(document).ready(function () {
            HSCore.components.HSFlatpickr.init('.js-flatpickr')
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250
            })
            new HSFileAttach('.js-file-attach')
        })

        $('.generateBtn').click(function() {
            var randomNumber = Math.floor(Math.random() * 100) + 1;
            var code = "save" + randomNumber;
            $('.coupon_code').val(code);
        });

    </script>
@endpush



