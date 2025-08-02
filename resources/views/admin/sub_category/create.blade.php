
@extends('admin.layouts.app')
@section('page_title', __('Create Sub Categories'))
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
                                <a class="breadcrumb-link" href="{{ route('admin.product.subcategories') }}">
                                    @lang('sub-Categories')
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Create')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Sub Category Form')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-8">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card pb-3">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="card-title m-0">@lang('Add Sub Category')</h4>
                        </div>
                        <div class="card-body mt-2">
                            <form action="{{ route('admin.product.subcategories.store') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="row mb-4 d-flex align-items-center">
                                    <div class="col-md-12 mb-3">
                                        <label for="NameLabel" class="form-label">@lang("Category")</label>
                                        <div class="tom-select-custom">
                                            <select class="js-select form-select @error('category_id') is-invalid @enderror" autocomplete="off" name="category_id">
                                                {{--                                                <option value="">@lang('Select a category')</option>--}}
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="nameLabel" class="form-label">@lang('Sub Category Name')</label>
                                        <input type="text" class="form-control  @error('name') is-invalid @enderror"
                                               name="name" id="nameLabel" placeholder="Sub Category Name" aria-label="Name"
                                               autocomplete="off"
                                               value="{{ old('name') }}">
                                        @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="NameLabel" class="form-label">@lang("Status")</label>
                                        <div class="tom-select-custom">
                                            <select class="js-select form-select" autocomplete="off" name="status">
                                                <option value="1" >@lang('Active')</option>
                                                <option value="0">@lang('In Active')</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="cImage">@lang(stringToTitle('Subcategory image'))</label>
                                        <label class="form-check form-check-dashed" for="logoUploader" id="content_img">
                                            <img id="contentImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ asset("assets/admin/img/oc-browse-file.svg") }}"
                                                 alt="Image Description" data-hs-theme-appearance="default">
                                            <img id="contentImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ asset("assets/admin/img/oc-browse-file-light.svg") }}"
                                                 alt="Image Description" data-hs-theme-appearance="dark">
                                            <span class="d-block">@lang("Browse your file here")</span>
                                            <input type="hidden" name="test" value="0">
                                            <input type="file" name="subcategory_image" class="js-file-attach form-check-input @error('subcategory_image') is-invalid @enderror"
                                                   id="logoUploader" data-hs-file-attach-options='{
                                                                      "textTarget": "#contentImg",
                                                                      "mode": "image",
                                                                      "targetAttr": "src",
                                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                                   }'
                                            />
                                            @error('subcategory_image')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </label>
                                    </div>


                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit"
                                            class="btn btn-primary submit_btn">@lang('Save changes')</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush


@push('script')
    <script>
        $(document).ready(() => new HSFileAttach('.js-file-attach'));
        HSCore.components.HSTomSelect.init('.js-select', {
            maxOptions: 250
        })


    </script>
@endpush




