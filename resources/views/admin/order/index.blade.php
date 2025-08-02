@extends('admin.layouts.app')
@section('page_title',__('Orders'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang("Order List")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Order List")</h1>
                </div>
            </div>
        </div>


        <div class="card" id="test">
            <div class="card-header card-header-content-between">
                <div class="mb-2 mb-md-0">

                    <div class="input-group input-group-merge navbar-input-group">
                        <div class="input-group-prepend input-group-text">
                            <i class="bi-search"></i>
                        </div>
                        <input type="search" id="datatableSearch"
                               class="search form-control form-control-sm"
                               placeholder="@lang('Search order')"
                               aria-label="@lang('Search order')"
                               autocomplete="off">
                        <a class="input-group-append input-group-text display-none" href="javascript:void(0)">
                            <i id="clearSearchResultsIcon" class="bi-x"></i>
                        </a>
                    </div>

                </div>


            </div>

            <div class=" table-responsive datatable-custom  ">
                <table id="datatable"
                       class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                       "columnDefs": [{
                          "targets": [0, 7],
                          "orderable": false
                        }],
                       "order": [],
                       "info": {
                         "totalQty": "#datatableWithPaginationInfoTotalQty"
                       },
                       "search": "#datatableSearch",
                       "entries": "#datatableEntries",
                       "pageLength": 15,
                       "isResponsive": false,
                       "isShowPaging": false,
                       "pagination": "datatablePagination"
                     }'>
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('Order Number')</th>
                        <th>@lang('Order Date')</th>
                        <th>@lang('Customer')</th>
                        <th>@lang('Total')</th>
                        <th>@lang('Payment Status')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Payment Method')</th>
                        <th>@lang('Action')</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm mb-2 mb-sm-0">
                        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                            <span class="me-2">@lang('Showing:')</span>
                            <!-- Select -->
                            <div class="tom-select-custom">
                                <select id="datatableEntries"
                                        class="js-select form-select form-select-borderless w-auto" autocomplete="off"
                                        data-hs-tom-select-options='{
                                            "searchInDropdown": false,
                                            "hideSearch": true
                                          }'>
                                    <option value="10">10</option>
                                    <option value="15" selected>15</option>
                                    <option value="20">20</option>
                                </select>
                            </div>
                            <span class="text-secondary me-2">@lang('of')</span>
                            <span id="datatableWithPaginationInfoTotalQty"></span>
                        </div>
                    </div>


                    <div class="col-sm-auto">
                        <div class="d-flex  justify-content-center justify-content-sm-end">
                            <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
@push('js-lib')
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush
@push('script')
    <script>
        const orderStatus = {
            1 : 'Order Placed',
            2 : 'Delivered',
            3 : 'Cancelled'
        }
        function handelOrderStatus(event, orderId) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{route('admin.update.order.status')}}",
                data: {
                    status: event.target.value,
                    orderId: orderId,
                },
                method: "PUT",
                datatType: 'json',
                beforeSend : function (){
                    Notiflix.Loading.dots('Update Status...');
                },
                success: function (data) {
                    Notiflix.Loading.remove();
                    console.log(data)

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

                },
                error:function (err){
                    Notiflix.Loading.remove();
                    Notiflix.Notify.failure(err.statusText);
                },

            });
        }
        function getPrintInvoice(id) {
            var url = '{{route('admin.order.show.invoice')}}';
            url = url + `/${id}`
            var LeftPosition = (screen.width) ? (screen.width - 500) / 2 : 0;
            var TopPosition = (screen.height) ? (screen.height - 700) / 2 : 0;
            var settings = 'height=700,width=500,top=' + TopPosition + ',left=' + LeftPosition + ',scrollbars=yes,resizable';
            window.open(url, 'popUpWindow', settings);
        }
        $(document).ready(function () {
            (function() {
                // INITIALIZATION OF SELECT
                // =======================================================
                HSCore.components.HSTomSelect.init('.js-select')
            })();

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Reinitialize tooltips after every AJAX call
            $(document).ajaxComplete(function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            HSCore.components.HSDatatables.init($('#datatable'), {
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route("admin.orderData.search") }}",
                },

                columns: [
                    {data: 'order_number', name: 'order_number'},
                    {data: 'order_date', name: 'order_date'},
                    {data: 'name', name: 'name'},
                    {data: 'total', name: 'total'},
                    {data: 'status', name: 'status'},
                    {data: 'order_status', name: 'order_status'},
                    {data: 'method', name: 'method'},
                    {data: 'action', name: 'action'},
                ],

                language: {
                    zeroRecords: `<div class="text-center p-4">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                    <p class="mb-0">No data to show</p>
                    </div>`,
                    processing: `<div><div></div><div></div><div></div><div></div></div>`
                },
            });
        })
    </script>
@endpush
