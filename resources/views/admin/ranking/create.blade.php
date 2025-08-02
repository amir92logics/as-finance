@extends('admin.layouts.app')
@section('page_title',__('Create Rank'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Dashboard')</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Create Rank')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Create Rank')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-content-md-end">
                        <h4 class="card-header-title">Ranking Information</h4>
                    </div>
                    <form action="{{route('admin.rankStore')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="exampleFormControlInput1">Ranking Name</label>
                                        <input type="text" name="rank_name" value="{{old('rank_name')}}"
                                               id="exampleFormControlInput1" class="form-control"
                                               placeholder="e.g: hyip member">
                                    </div>
                                    @error("rank_name")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="exampleFormControlInput1">Ranking Level</label>
                                        <input type="text" name="rank_lavel" value="{{old('rank_lavel')}}"
                                               id="exampleFormControlInput1" class="form-control"
                                               placeholder="e.g: level 1">
                                    </div>
                                    @error("rank_lavel")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="exampleFormControlInput1">Minimum Invest</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="min_invest" value="{{old('min_invest')}}"
                                               id="exampleFormControlInput1" class="form-control" placeholder="e.g: 50"
                                               step="0.01">
                                        <span class="input-group-text">{{basicControl()->currency_symbol}}</span>
                                    </div>
                                    @error("min_invest")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 min_deposit" >
                                    <label class="form-label" for="min_deposit">Minimum Deposit</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="min_deposit" value="{{old('min_deposit')}}"
                                               id="min_deposit" class="form-control" placeholder="e.g: 50"
                                               step="0.01">
                                        <span class="input-group-text">{{basicControl()->currency_symbol}}</span>
                                    </div>
                                    @error("min_deposit")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6 min_earning">
                                    <label class="form-label" for="min_earning">Minimum Earning</label>
                                    <div class="input-group mb-3">
                                        <input type="number" name="min_earning" value="{{old('min_earning')}}"
                                               id="min_earning" class="form-control" placeholder="e.g: 50"
                                               step="0.01">
                                        <span class="input-group-text">{{basicControl()->currency_symbol}}</span>
                                    </div>
                                    @error("min_earning")
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>


                                <div class="col-md-12 mb-4">
                                    <label class="form-label" for="exampleFormControlInput1">Description</label>
                                    <textarea class="summernote" name="description"> {{old('description')}}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="exampleFormControlInput1">Ranking Icon</label>
                                    <label class="form-check form-check-dashed"
                                           for="logoUploader" id="content_img">
                                        <img id="contentImg"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{asset('assets/admin/img/oc-browse-file-light.svg')}}"
                                             alt="Image Description"
                                             data-hs-theme-appearance="default">
                                        <img id="contentImg"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{asset('assets/admin/img/oc-browse-file.svg')}}"
                                             alt="Image Description"
                                             data-hs-theme-appearance="dark">
                                        <span
                                            class="d-block">@lang("Browse your file here")</span>
                                        <input type="file" name="rank_icon"
                                               class="js-file-attach form-check-input"
                                               id="logoUploader"
                                               data-hs-file-attach-options='{
                                                                      "textTarget": "#contentImg",
                                                                      "mode": "image",
                                                                      "targetAttr": "src",
                                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                                   }'>
                                    </label>
                                    @error("rank_icon")
                                    <span class="invalid-feedback d-block" role="alert">
                                            {{ $message }}
                                            </span>
                                    @enderror
                                </div>

                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <div>
                                <button class="btn btn-primary me-2" name="status" value="1">Save & Publish</button>
                                <button class="btn btn-info" name="status" value="0">Save & Draft</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/summernote-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush
@push('js-lib')
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function () {
            new HSFileAttach('.js-file-attach')
            HSCore.components.HSFlatpickr.init('.js-flatpickr')
        })();
        $(document).ready(function () {
            $('.summernote').summernote({
                height: 200,
                callbacks: {
                    onBlurCodeview: function () {
                        let codeviewHtml = $(this).siblings('div.note-editor').find('.note-codable').val();
                        $(this).val(codeviewHtml);
                    }
                }
            });
        });
    </script>
@endpush

