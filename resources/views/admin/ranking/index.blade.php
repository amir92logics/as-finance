@extends('admin.layouts.app')
@section('page_title',__('Rank List'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Dashboard')</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Rank List')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Rank List')</h1>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-content-md-end">

                <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                    <a href="{{route('admin.rankCreate')}}"  class="btn btn-primary"><i class="fa-duotone fa-solid fa-plus me-1"></i>@lang('Add New')</a>
                </div>
            </div>

            <div class=" table-responsive datatable-custom  ">
                <table id="datatable"
                       class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                       "columnDefs": [{
                          "targets": [0, 8],
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

                        <th scope="col">@lang('Rank Name')</th>
                        <th scope="col">@lang('Rank Level')</th>
                        <th scope="col">@lang('Rank Icon')</th>
                        <th scope="col">@lang('Min Invest')</th>
                        <th scope="col">@lang('Min Deposit')</th>
                        <th scope="col">@lang('Min Earning')</th>
                        <th scope="col">@lang('Details')</th>
                        <th scope="col">@lang('status')</th>
                        <th scope="col">@lang('Action')</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($rankings as $item)
                        <tr data-id="{{ $item->id }}">
                            <td data-label="@lang('Rank Name')">
                                @lang($item->rank_name)
                            </td>
                            <td data-label="@lang('Rank Level')">
                                <p class="font-weight-bold">{{$item->rank_lavel}}</p>
                            </td>

                            <td data-label="@lang('Rank icon')">
                                <img src="{{ getFile($item->driver,$item->rank_icon)}}"
                                     alt="@lang('not found')" width="60">
                            </td>

                            <td data-label="@lang('Minimum Earning')">
                                <p class="font-weight-bold">{{$item->min_invest}} {{ config('basic.currency') }}</p>
                            </td>

                            <td data-label="@lang('Minimum Earning')">
                                <p class="font-weight-bold">{{$item->min_deposit}} {{ config('basic.currency') }}</p>
                            </td>

                            <td data-label="@lang('Minimum Earning')">
                                <p class="font-weight-bold">{{$item->min_earning}} {{ config('basic.currency') }}</p>
                            </td>

                            <td data-label="@lang('Bonus')">
                                <p class="font-weight-bold">@lang($item->description)</p>
                            </td>

                            <td data-label="@lang('Status')">

                                @php
                                    $bg =   $item->status == 1 ? 'success' : 'danger';
                                @endphp

                                <span class="badge bg-soft-{{$bg}} text-{{$bg}}">
                                        <span class="legend-indicator bg-{{$bg}}"></span>
                                              @if($item->status == 1)
                                        @lang('Active')
                                    @else
                                        @lang('Deactive')
                                    @endif
                                      </span>

                            </td>

                            <td data-label="@lang('Action')">
                                <div class="btn-group" role="group">
                                    <a class="btn btn-white btn-sm" href="{{ route('admin.rankEdit',$item->id) }}">
                                        <i class="bi-pencil-fill me-1"></i> @lang('Edit')
                                    </a>

                                    <!-- Button Group -->
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="productsEditDropdown1" data-bs-toggle="dropdown" aria-expanded="false"></button>

                                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="productsEditDropdown1" style="">
                                            <a class="dropdown-item deleteBtn" href="javascript:void(0)"
                                               data-route="{{ route('admin.rankDelete',$item->id) }}"
                                               data-bs-toggle="modal"
                                               data-bs-target="#deleteModal"
                                            >
                                                <i class="bi-trash dropdown-item-icon"></i> @lang('Delete')
                                            </a>
                                        </div>
                                    </div>
                                    <!-- End Button Group -->
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center">
                                <div class="text-center p-4">
                                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                                    <p class="mb-0">@lang('No data to show')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
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
                                    <option value="20" selected>20</option>
                                    <option value="30">30</option>
                                    <option value="40">40</option>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
         data-bs-backdrop="static"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="deleteModalLabel"><i
                            class="bi bi-check2-square"></i> @lang("Confirmation")</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" id="setRoute">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>@lang("Do you want to delete this Item")</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Delete Modal -->

@endsection

@push('script')
    <script>

        $(document).on('click','.deleteBtn',function (){
            $('#setRoute').attr('action',$(this).data('route'))
        })


    </script>
@endpush




