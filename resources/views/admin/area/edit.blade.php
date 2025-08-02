@extends('admin.layouts.app')
@section('page_title',__('Edit Area'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Edit Area")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Edit Area")</h1>
                </div>
            </div>
        </div>


        <div class="row d-flex justify-content-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Area information')</h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.area.update',$area->id) }}" class="mt-4">
                            @csrf
                            @method('put')
                            <div class="row">
                                <div class="col-sm-12 col-md-6 mb-3">
                                    <label for="area_name" class="mb-3"> @lang('Area Name') </label>
                                    <input type="text" name="area_name"
                                           id="area_name" class="form-control  @error('area_name') is-invalid @enderror"
                                           value="{{old('area_name',$area->area_name)}}" placeholder="@lang('Enter Area Name')">
                                    @error("area_name")
                                    <span class="invalid-feedback d-block" role="alert">
                                            {{ $message }}
                                            </span>
                                    @enderror
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3">
                                    <label for="post_code" class="mb-3"> @lang('Post Code') </label>
                                    <input type="text" name="post_code"
                                           id="post_code" class="form-control  @error('post_code') is-invalid @enderror"
                                           value="{{ old('post_code',$area->post_code) }}" placeholder="@lang('Enter Post Code')">
                                    @error("post_code")
                                    <span class="invalid-feedback d-block" role="alert">
                                            {{ $message }}
                                            </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-12 col-lg-6">
                                    <label for="order_from" class="mb-3">@lang('Order From')</label>
                                    <input type="number" id="order_from" class="form-control"
                                           placeholder="@lang('Enter Order Start Number')"/>
                                    <span class="invalid-feedback d-block" id="orderFromInvalid" role="alert"> </span>

                                </div>
                                <div class="col-sm-12 col-md-12 col-lg-6">
                                    <div class="d-flex justify-content-between">
                                        <label for="order_to" class="mb-3">@lang('Order To')</label>
                                        <div>
                                            <input id="upto" type="checkbox">
                                            <label for="upto" class="cursor-pointer">@lang('Upto')</label>
                                        </div>
                                    </div>

                                    <input type="number" id="order_to" class="form-control mb-3"
                                           placeholder="@lang('Enter Order End Number')"/>
                                    @error("shipping_price_range.0.order_to")
                                    <span class="invalid-feedback d-block" role="alert">
                                            {{ $message }}
                                            </span>
                                    @enderror

                                </div>

                                <div class="form-group col-md-12">
                                    <label for="deliveryCharge" class="mb-3">@lang('Delivery Charge')</label>
                                    <div class="d-flex">
                                        <div class="input-group me-3">
                                            <input type="text" class="form-control" id="deliveryCharge"
                                                   placeholder="@lang('Enter Deliver Charge')"/>
                                            <span type="button"
                                                  class="btn btn-primary input-group-text">{{basicControl()->currency_symbol}}</span>
                                        </div>
                                        <button type="button" class="btn btn-primary d-flex @if(old('shipping_price_range') && count(old('shipping_price_range')) && old('shipping_price_range')[count(old('shipping_price_range')) - 1]["order_to"] == "" ) disabled @endif" id="add-button"><i
                                                class="fas fa-plus mr-2"></i> Add
                                        </button>
                                    </div>

                                </div>

                            </div>


                            <div class="row my-3  @if(old('shipping_price_range', $area->shippingCharge)) @else d-none @endif" id="sheppingPriceRangeRow">
                                <table class="table table-bordered col-md-11 mx-auto  "
                                       id="order-price-table">
                                    <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">@lang("Order From")</th>
                                        <th scope="col">@lang("Order To")</th>
                                        <th scope="col">@lang("Delivery Charge")</th>
                                        <th scope="col">@lang("Action")</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(old('shipping_price_range',$area->shippingCharge))
                                        @foreach (old('shipping_price_range',$area->shippingCharge) as $index => $item)
                                            <tr>
                                                <td class="text-center">{{(int) $index+1}}</td>
                                                <input type="hidden" value="{{$item["order_from"]}}"
                                                       name="shipping_price_range[{{$index}}][order_from]"/>
                                                <td class="text-center">{{$item["order_from"]}}</td>
                                                <input type="hidden" value="{{$item["order_to"]}}"
                                                       name="shipping_price_range[{{$index}}][order_to]"/>
                                                <td class="text-center">@if($item["order_to"]) {{$item['order_to']}} @else Upto All Orders @endif</td>
                                                <input type="hidden" value="{{$item["delivery_charge"]}}"
                                                       name="shipping_price_range[{{$index}}][delivery_charge]"/>
                                                <td class="text-center">{{$item["delivery_charge"]}}</td>
                                                <td class="text-center">
                                                    @if(count(old('shipping_price_range',$area->shippingCharge)) == $index +1)
                                                        <span class="btn btn-danger cursor-pointer py-2" onclick="handelRemoveItem()"><i class="fa fa-times" aria-hidden="true"></i></span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>


                            <button type="submit"
                                    class="btn waves-effect waves-light btn-rounded btn-primary btn-block mt-3">@lang('Save')</button>
                        </form>
                    </div>
                    <!-- Body -->
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush
@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush
@php
    $oldShippingPrice = old('shipping_price_range') ?? null;
@endphp

@push('script')


    <script>
        "use strict"
        var orderPriceTable = $('#order-price-table');
        var tableBody = orderPriceTable.find('tbody');
        var orderFrom = $('#order_from');
        var orderTo = $('#order_to');
        var upTo = $('#upto');
        var deliveryCharge = $('#deliveryCharge');
        var orderPriceRange = @json($area->shippingCharge);




        $("#upto").on("click",function() {
            const isChecked = $(this).is(":checked");
            $('#order_to').val("");
            $('#order_to').prop("readonly", isChecked);
            $('#order_to').attr("placeholder", isChecked ? "@lang('Upto All Order')" : "@lang('Order End Number')");
        });



        $('#add-button').on("click", function () {
            if(orderPriceRange.length && orderPriceRange[orderPriceRange.length - 1].order_to == ""){
                return;
            }
            if (!orderFrom.val() || (!orderTo.val() && !upTo.is(":checked")) || !deliveryCharge.val()) {
                return;
            }
            $("#sheppingPriceRangeRow").removeClass('d-none');
            $('#orderFromInvalid').html("")

            const newOrderFrom = parseInt(orderFrom.val());
            const newOrderTo = parseInt(orderTo.val());

            if (orderPriceRange.length == 0 && newOrderFrom !== 1) {
                $('#orderFromInvalid').append('<p>Order From must start with 1.</p>');
                return;
            } else if (newOrderFrom >= newOrderTo) {
                $('#orderFromInvalid').append('<p>Order From must be smaller than Order To.</p>');
                return;
            } else if (orderPriceRange.length > 0 && newOrderFrom <= parseInt(orderPriceRange[orderPriceRange.length - 1].order_to)) {
                $('#orderFromInvalid').append('<p>Order From must be greater than the last Order To value.</p>');
                return;
            } else if (orderPriceRange[orderPriceRange.length - 1]?.order_to) {
                if (newOrderFrom !== parseInt(orderPriceRange[orderPriceRange.length - 1].order_to) + 1) {
                    $('#orderFromInvalid').append(`<p>Order From must be start with ${parseInt(orderPriceRange[orderPriceRange.length - 1].order_to) + 1}</p>`);
                    return;
                }
            }

            orderPriceRange.push({
                id: new Date(),
                order_from: orderFrom.val(),
                order_to: orderTo.val(),
                delivery_charge:deliveryCharge.val()
            });

            if(orderPriceRange[orderPriceRange.length - 1].order_to == ""){
                $('#add-button').addClass('disabled')
            }

            tableBody.html(" ");
            orderPriceRange.forEach((range, index) => {
                tableBody.append(tableMarkup(range, index, orderPriceRange.length))
            });

            $('#orderFromInvalid').html("")

            orderFrom.val('');
            orderTo.val('');
            deliveryCharge.val('');
        });


        const handelRemoveItem = () => {
            orderPriceRange.pop();
            tableBody.html(" ");
            orderPriceRange.forEach((range, index) => {
                tableBody.append(tableMarkup(range, index, orderPriceRange.length));
            });
            if (orderPriceRange.length && orderPriceRange[orderPriceRange.length-1].order_to != ""){
                $('#add-button').removeClass('disabled')
            }
            if(!orderPriceRange.length){
                $("#sheppingPriceRangeRow").addClass('d-none');
            }
        }

        var tableMarkup = (item, index, total) => {
            let deleteIcon = '';
            if (total == index + 1) {
                deleteIcon = `<span class="btn btn-danger cursor-pointer py-2" onclick="handelRemoveItem()"><i class="fa fa-times" aria-hidden="true"></i></span>`;
            }

            return `<tr>
        <td class="text-center">${index + 1}</td>
        <input type="hidden" value="${item.order_from}" name="shipping_price_range[${index}][order_from]"/>
        <td class="text-center">${item.order_from}</td>
        <input type="hidden" value="${item.order_to}" name="shipping_price_range[${index}][order_to]"/>
        <td class="text-center">${item.order_to ? item.order_to : "Upto All Orders"}</td>

        <input type="hidden" value="${item.delivery_charge}" name="shipping_price_range[${index}][delivery_charge]"/>
        <td class="text-center">${item.delivery_charge}</td>
        <td class="text-center">${deleteIcon}</td>
    </tr>`;
        };


    </script>
    </script>
@endpush

