@extends(template().'layouts.user')
@section('title', 'badges')

@section('content')

    <div class="pagetitle">
        <h3 class="mb-1">@lang('Badges')</h3>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('user.dashboard')}}">@lang('Home')</a></li>
                <li class="breadcrumb-item active">@lang('Badges')</li>
            </ol>
        </nav>
    </div>
    <div class="mt-50">
        @if($allBadges)
            <div class="badge-box-wrapper">
                <div class="row g-4 mb-4">
                    @foreach($allBadges as $key => $badge)
                        <div class="col-xl-4 col-lg-4 col-md-6 box">
                            <div class="badge-box">
                                @if(auth()->user()->rank && auth()->user()->rank->id == $badge->id)
                                    <i class="fa-regular fa-square-check" data-bs-toggle="tooltip" data-bs-placement="top" title="Your Current Badge"></i>
                                @endif
                                <img src="{{ getFile($badge->driver,$badge->rank_icon) }}" alt="" />
                                <h3>@lang(@$badge->rank_lavel)</h3>
                                <p>@lang($badge->description)</p>
                                <div class="text-start">
                                    <h5>@lang('Minimum Invest'): <span>{{ currencyPosition($badge->min_invest) }}</span></h5>
                                    <h5>@lang('Minimum Deposit'): <span>{{ currencyPosition($badge->min_deposit) }}</span></h5>
                                    <h5>@lang('Minimum Earning'): <span>{{ currencyPosition($badge->min_earning) }}</span></h5>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center p-4">
                <img class=" mb-3 w-25" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                <p class="mb-0">@lang('No data to show')</p>
            </div>
        @endif
    </div>
@endsection
