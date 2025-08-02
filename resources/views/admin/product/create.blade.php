@extends('admin.layouts.app')
@section('page_title',__('Product Create'))
@section('content')
    <style>
        .image-uploader{
            border: none;
        }
    </style>

    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Product Setting")</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Products")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Product Create")</h1>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.product.store') }}" method="post" enctype="multipart/form-data">

            <input type="hidden" name="language_id" value="{{$language->id}}">
            <div class="row">
                @csrf
                <div class="col-lg-8">
                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-header-title">@lang('Product information')</h4>
                            <a href="{{ route('admin.products') }}" class="btn btn-primary" id="clearLocalStorageData">@lang("Product List")</a>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12 mb-4">
                                    <label for="productNameLabel" class="form-label">@lang('Title')
                                        <i class="bi-question-circle text-body ms-1"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Products are the goods or services you sell."></i></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" id="title" placeholder="Product Title"
                                           aria-label="Shirt, t-shirts, etc." value="{{ old('title') }}">
                                    @error('title')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="input-box ">
                                        <label for="">@lang('Price')</label>
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control @error('price') is-invalid @enderror"
                                                   name="price" placeholder="0.00"
                                                   value="{{ old('price') }}"
                                                   id="price" autocomplete="off" onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"/>
                                            <span class="input-group-text" id="basic-addon2">{{basicControl()->currency_symbol}}</span>
                                        </div>
                                        @error('price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-box">
                                        <label for="">@lang('Quantity')</label>
                                        <div class="input-group mb-3">
                                            <input type="number" name="quantity" class="form-control" placeholder="0" aria-label="Recipient's username" aria-describedby="basic-addon2" step="0.01">
                                            <div class="tom-select-custom">
                                                <select class="form-select js-select" name="quantity_type">
                                                    <option value="gm">@lang('gm')</option>
                                                    <option value="kg">@lang('kg')</option>
                                                    <option value="pcs">@lang('pcs')</option>
                                                    <option value="liter">@lang('liter')</option>
                                                </select>
                                                @error('quantity_type')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- Body -->
                    </div>

                    <div class="card mb-5">
                        <div class="card-header">
                            <h5 class="card-header-title">@lang('Short Description')</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <textarea class="summernote" name="short_description"> {{old('short_description')}}</textarea>
                                    @error('short_description')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card  mb-5">
                        <div class="card-header">
                            <h5 class="card-header-title">@lang('Description')</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <textarea class="summernote" name="description"> {{old('description')}}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-5 ">
                        <!-- Header -->
                        <div class="card-header card-header-content-between">
                            <h4 class="card-header-title">@lang('Thumbnail')</h4>
                        </div>
                        <!-- End Header -->

                        <!-- Body -->
                        <div class="card-body">
                            <div class="col-12">
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
                                    <input type="file" name="thumbnail"
                                           class="js-file-attach form-check-input"
                                           id="logoUploader"
                                           data-hs-file-attach-options='{
                                                                      "textTarget": "#contentImg",
                                                                      "mode": "image",
                                                                      "targetAttr": "src",
                                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                                   }'>
                                </label>
                                @error("thumbnail")
                                <span class="invalid-feedback d-block" role="alert">
                                            {{ $message }}
                                            </span>
                                @enderror
                            </div>

                            @error("thumbnail")
                            <span class="invalid-feedback d-block" role="alert">
                                            {{ $message }}
                                            </span>
                            @enderror
                        </div>

                        <!-- Body -->
                    </div>



                </div>

                <div class="col-lg-4">
                    <!-- Card -->
                    <div class="card mb-3 mb-lg-5">
                        <!-- Header -->
                        <div class="card-header">
                            <h4 class="card-header-title">@lang('Availability')</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="tom-select-custom">
                                    <select class="form-select js-select" name="status">
                                        <option value="Available">@lang('Available')</option>
                                        <option value="Stock Out">@lang('Stock Out')</option>
                                    </select>
                                    @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-header-title">@lang('Organization')</h4>
                        </div>

                        <div class="card-body">


                            <div class="mb-4">
                                <label for="categoryLabel" class="form-label">@lang('Category')</label>
                                <div class="tom-select-custom">
                                    <select class="js-select form-select @error('category_id') is-invalid @enderror"
                                            id="categorySelect" name="category_id">
                                        <option value="">@lang('Select a category')</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">@lang($category->name)</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="subcategorySelect" class="form-label">@lang('Subcategory')</label>
                                <div class="tom-select-custom">
                                    <select class="form-select subCategorySelect" id="subcategorySelect" name="subcategory_id">
                                        <option value="">@lang('Select a subcategory')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-5">
                        <div class="card-header bg-white">
                            <h4 class="card-header-title">@lang('Publish')</h4>
                        </div>
                        <div class="card-body">
                            <div>
                                <button class="btn btn-primary mb-3" type="submit" name="is_published" value="1">@lang('Save & Publish')</button>
                                <button class="btn btn-info ms-3 mb-3" type="submit" name="is_published" value="0">@lang('Save & Draft')</button>
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
    <link rel="stylesheet" href="{{ asset('assets/admin/css/summernote-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/image-uploader.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush
@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hs-add-field.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/image-uploader.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush


@push('script')
    <script>

        (function () {
            new HSFileAttach('.js-file-attach')
            HSCore.components.HSFlatpickr.init('.js-flatpickr')
        })();
        $(document).ready(function (){
            $('.input-images-1').imageUploader();
            $('.summernote').summernote({
                height: 200,
                callbacks: {
                    onBlurCodeview: function () {
                        let codeviewHtml = $(this).siblings('div.note-editor').find('.note-codable').val();
                        $(this).val(codeviewHtml);
                    }
                }
            });


            //get getSubcategory
            $(document).on('change','#categorySelect', function () {
                $('select[name="subcategory_id"]').html('');
                var category_id = $('#categorySelect').find(":selected").val();
                $.ajax({
                    url: "{{ route('admin.product.getSubcategory') }}",
                    type: "get",
                    data: {
                        category_id: category_id,
                    },
                    success: function (res) {
                        var response = res.data;
                        $.each(response, function (key, value) {
                            $('select[name="subcategory_id"]').append('<option value=" ' + value.id + '">' + value.name + '</option>');
                        })
                    },
                });
            })
        })
    </script>
@endpush

@push('css')
    <style>

        .image-uploader {
            height: 15rem;
            border: .125rem dashed rgba(231,234,243,.7);
            border-radius: 10px;
            position: relative;
            overflow: auto;
        }

        .input-images-1{
            padding-top: .5rem !important;
        }

    </style>
@endpush




