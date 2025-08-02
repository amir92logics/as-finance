@extends('admin.layouts.app')
@section('page_title',__('Product List'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Product Setting")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Products") <span class="badge bg-soft-dark text-dark ms-2">{{ $totalProducts }}</span></h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header card-header-content-md-between">
                        <div class="mb-2 mb-md-0">
                            <div class="input-group input-group-merge navbar-input-group">
                                <div class="input-group-prepend input-group-text">
                                    <i class="bi-search"></i>
                                </div>
                                <input type="search" id="datatableSearch"
                                       class="search form-control form-control-sm"
                                       placeholder="@lang('Search Product')"
                                       aria-label="@lang('Search Product')"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <div class="datatableCounterInfo" id="datatableCounterInfo">
                                <div class="d-flex align-items-center">
                                    <span class="fs-5 me-3">
                                      <span class="datatableCounter">0</span>
                                      @lang('Selected')
                                    </span>
                                    <a class="btn btn-outline-success btn-sm" href="javascript:void(0)" data-bs-toggle="modal"
                                       data-bs-target="#actionModal">
                                        <i class="bi-gear"></i> @lang('Action')
                                    </a>
                                </div>
                            </div>

                            <div class="datatableCounterInfo" id="datatableCounterInfo">
                                <div class="d-flex align-items-center">
                                    <a class="btn btn-white btn-sm mb-2 mb-sm-0 me-2" href="javascript:void(0)" data-bs-toggle="modal"
                                       data-bs-target="#publishMultipleModal">
                                        <i class="bi-upload"></i> @lang('Publish')
                                    </a>
                                    <a class="btn btn-white btn-sm mb-2 mb-sm-0 me-2" href="javascript:void(0)" data-bs-toggle="modal"
                                       data-bs-target="#unpublishMultipleModal">
                                        <i class="bi-x-lg"></i> @lang('Unpublish')
                                    </a>
                                    <a class="btn btn-outline-danger btn-sm" href="javascript:void(0)" data-bs-toggle="modal"
                                       data-bs-target="#userDeleteMultipleModal">
                                        <i class="bi-trash"></i> @lang('Delete')
                                    </a>
                                </div>
                            </div>

                            <a href="{{ route('admin.product.create') }}" class="btn btn-primary" id="clearLocalStorage">@lang('Create')</a>
                            <div class="dropdown">
                                <button type="button" class="btn btn-white btn-sm w-100"
                                        id="dropdownMenuClickable" data-bs-auto-close="false"
                                        id="usersFilterDropdown"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <i class="bi-filter me-1"></i> @lang('Filter')
                                </button>

                                <div
                                    class="dropdown-menu dropdown-menu-sm-end dropdown-card card-dropdown-filter-centered filter_dropdown"
                                    aria-labelledby="dropdownMenuClickable">
                                    <div class="card">
                                        <div class="card-header card-header-content-between">
                                            <h5 class="card-header-title">@lang('Filter')</h5>
                                            <button type="button"
                                                    class="btn btn-ghost-secondary btn-icon btn-sm ms-2"
                                                    id="filter_close_btn">
                                                <i class="bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <div class="card-body">
                                            <form id="filter_form">
                                                <div class="mb-4">
                                                    <span class="text-cap text-body">@lang('Product Name')</span>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <input type="text" class="form-control"
                                                                   id="product_name_filter_input"
                                                                   autocomplete="off">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm mb-4">
                                                        <small class="text-cap text-body">@lang('Product Status')</small>
                                                        <div class="tom-select-custom">
                                                            <select
                                                                class="js-select js-datatable-filter form-select form-select-sm"
                                                                id="filter_status"
                                                                data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "Any status",
                                                                  "searchInDropdown": false,
                                                                  "hideSearch": true,
                                                                  "dropdownWidth": "10rem"
                                                                }'>
                                                                <option value="all"
                                                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-secondary"></span>All Status</span>'>
                                                                    @lang('All Status')
                                                                </option>
                                                                <option value="1"
                                                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-success"></span>Published</span>'>
                                                                    @lang('Published')
                                                                </option>
                                                                <option value="0"
                                                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-danger"></span>Unpublished</span>'>
                                                                    @lang('Unpublished')
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-12 mb-4">
                                                        <span class="text-cap text-body">@lang('Date Range')</span>
                                                        <div class="input-group mb-3 custom">
                                                            <input type="text" id="filter_date_range"
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
                                                </div>

                                                <div class="row gx-2">
                                                    <div class="col">
                                                        <div class="d-grid">
                                                            <button type="button" id="clear_filter"
                                                                    class="btn btn-white">@lang('Clear Filters')</button>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="d-grid">
                                                            <button type="button" class="btn btn-primary"
                                                                    id="filter_button"><i
                                                                    class="bi-search"></i> @lang('Apply')
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>
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
                                       "pageLength": 10,
                                       "isResponsive": false,
                                       "isShowPaging": false,
                                       "pagination": "datatablePagination"
                                     }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="table-column-pe-0">
                                    <div class="form-check">
                                        <input class="form-check-input check-all tic-check" type="checkbox" name="check-all"
                                               id="datatableCheckAll">
                                        <label class="form-check-label" for="datatableCheckAll"></label>
                                    </div>
                                </th>
                                <th>@lang('Product')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Subcategory')</th>
                                <th>@lang('Price')</th>
                                <th>@lang('Availability')</th>
                                <th>@lang('status')</th>
                                <th> @foreach($languages as $language)
                                        <img class="avatar avatar-xss avatar-square me-2"
                                             src="{{ getFile($language->flag_driver, $language->flag) }}"
                                             alt="{{ $language->name }} Flag">
                                    @endforeach</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>

                            <tbody>

                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <div
                            class="row justify-content-center justify-content-sm-between align-items-sm-center">
                            <div class="col-sm mb-2 mb-sm-0">
                                <div
                                    class="d-flex justify-content-center justify-content-sm-start align-items-center">
                                    <span class="me-2">@lang('Showing:')</span>
                                    <div class="tom-select-custom">
                                        <select id="datatableEntries"
                                                class="js-select form-select form-select-borderless w-auto"
                                                autocomplete="off"
                                                data-hs-tom-select-options='{
                                                        "searchInDropdown": false,
                                                        "hideSearch": true
                                                      }'>
                                            <option value="5">5</option>
                                            <option value="10" selected>10</option>
                                            <option value="15">15</option>
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
        </div>
    </div>


    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" data-bs-backdrop="static"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="deleteModalLabel"><i
                            class="bi bi-check2-square"></i> @lang("Confirmation")</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" class="setRoute">
                    @csrf
                    @method("delete")
                    <div class="modal-body">
                        <p>@lang("Do you want to delete") `<span class="item-name text-danger"></span>` @lang("Product?") </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete multiple Modal -->
    <div class="modal fade" id="userDeleteMultipleModal" tabindex="-1" role="dialog" aria-labelledby="userDeleteMultipleModalLabel" data-bs-backdrop="static"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="userDeleteMultipleModalLabel"><i
                            class="fa-light fa-square-check"></i> @lang('Confirmation')</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    @csrf
                    <div class="modal-body">
                        @lang('Do you want to delete all selected Product?')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary delete-multiple">@lang('Confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End multiple Modal -->



@endsection




@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/appear.min.js') }}"></script>
    <script src="{{ asset("assets/admin/js/hs-counter.min.js") }}"></script>
@endpush

@push('script')
    <script>
        $(document).on('ready', function () {
            $(document).on('click', '.deleteBtn', function () {
                let route = $(this).data('route');
                let itemName = $(this).data('item-name');
                $('.item-name').text(itemName);
                $('.setRoute').attr('action', route);
            })

            // clear all data of local storage
            $(document).on('click', '#clearAllLocalStorage, #clearLocalStorage ', function () {
                localStorage.clear();
            });

            new HSCounter('.js-counter')
            HSCore.components.HSFlatpickr.init('.js-flatpickr')
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250,
            })
            HSCore.components.HSDatatables.init($('#datatable'), {
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route("admin.product.list") }}",
                },

                columns: [
                    {data: 'checkbox', name: 'checkbox'},
                    {data: 'product', name: 'product'},
                    {data: 'category', name: 'category'},
                    {data: 'subcategory', name: 'subcategory'},
                    {data: 'price', name: 'price'},
                    {data: 'availability', name: 'availability'},
                    {data: 'status', name: 'status'},
                    {data: 'language', name: 'language'},
                    {data: 'action', name: 'action'},
                ],

                select: {
                    style: 'multi',
                    selector: 'td:first-child input[type="checkbox"]',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '.datatableCounter',
                        counterInfo: '.datatableCounterInfo'
                    }
                },

                language: {
                    zeroRecords: `<div class="text-center p-4">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                    <p class="mb-0">No data to show</p>
                    </div>`,
                    processing: `<div><div></div><div></div><div></div><div></div></div>`
                },
            });

            document.getElementById("filter_button").addEventListener("click", function () {
                let filterProductName = $('#product_name_filter_input').val();
                let filterStatus = $('#filter_status').val();
                let filterDate = $('#filter_date_range').val();
                const datatable = HSCore.components.HSDatatables.getItem(0);
                datatable.ajax.url("{{ route('admin.product.list') }}" + "?filterProductName=" + filterProductName + "&filterStatus=" + filterStatus +
                    "&filterDate=" + filterDate).load();
            });
            $.fn.dataTable.ext.errMode = 'throw';


            $(document).on('click', '#datatableCheckAll', function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

            $(document).on('change', ".row-tic", function () {
                let length = $(".row-tic").length;
                let checkedLength = $(".row-tic:checked").length;
                if (length == checkedLength) {
                    $('#check-all').prop('checked', true);
                } else {
                    $('#check-all').prop('checked', false);
                }
            });

            $(document).on('click', '.delete-multiple', function (e) {
                e.preventDefault();
                let all_value = [];
                $(".row-tic:checked").each(function () {
                    all_value.push($(this).attr('data-id'));
                });
                let strIds = all_value;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('admin.product.destroy.multiple') }}",
                    data: {strIds: strIds},
                    datatType: 'json',
                    type: "post",
                    success: function (data) {
                        location.reload();
                    },
                });
            });

        });
    </script>
@endpush



