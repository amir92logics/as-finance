<!-- header -->
<header class="main-header header-style-two">

    <!-- header top -->
    <div class="header-top">
        <div class="container">
            <div class="header-top-container">
                <div class="header-top-social-container">
                    <div class="header-top-info">
                        <i class="fa-light fa-phone"></i>
                        <a href="tel:{!! $top_section['single']['telephone']??'' !!}">{!! $top_section['single']['telephone']??'' !!}</a>
                    </div>
                    <div class="header-top-social-border"></div>
                    <div class="header-top-info">
                        <i class="fa-light fa-envelope"></i>
                        <a href="mailto:{{$top_section['single']['email']}}">{{$top_section['single']['email']}}</a>
                    </div>
                </div>
                <div class="header-right-btn">
                    <div class="sign-up">
                        @if(auth()->user())
                            <div class="button-1">
                                <a href="{{route('user.dashboard')}}" class="btn-1">@lang('Dashboard') <span></span></a>
                            </div>
                        @else
                            <div class="button-1">
                                <a href="{{route('login')}}" class="btn-1">@lang('Log In') <span></span></a>
                            </div>
                            <div class="button-2">
                                <a href="{{route('register')}}" class="btn-1">@lang('Sign Up') <span></span></a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header top -->

    <!-- Header Lower -->
    <div class="header-lower">
        <div class="container">
            <div class="inner-container d-flex align-items-center justify-content-between">

                <div class="left-column">
                    <div class="logo-box">
                        <div class="logo"><a href="{{route('page')}}"><img
                                    src="{{getFile(basicControl()->logo_driver,basicControl()->logo)}}" alt="logo"></a>
                        </div>
                    </div>
                </div>

                <div class="right-column d-flex align-items-center">
                    <div class="nav-outer">
                        <div class="mobile-nav-toggler"><i class="fa-regular fa-bars-staggered"></i></div>
                        <nav class="main-menu navbar-expand-md navbar-light">
                            <div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">
                                <ul class="navigation">
                                    {!! renderHeaderMenu(getHeaderMenuData()) !!}
                                    @if(auth()->user())
                                        <li class="outer-menu"><a
                                                href="{{route('user.dashboard')}}">@lang('Dashboard')</a></li>
                                    @else
                                        <li class="outer-menu"><a href="{{route('login')}}">@lang('Login')</a></li>
                                        <li class="outer-menu"><a href="{{route('register')}}">@lang('Registration')</a>
                                        </li>
                                    @endif

                                </ul>
                            </div>
                        </nav>
                    </div>

                    @if(basicControl()->ecommerce)
                        <div class="header-right-search">
                            <div class="header-right-search-container">
                                <div class="search">
                                    <a href="javascript:void(0)" class="search-icon search-btn"><i
                                            class="fa-light fa-magnifying-glass"></i></a>
                                </div>
                                <div class="cart">
                                    <a href="{{route('cart')}}" class="cart-icon"><i
                                            class="fa-light fa-cart-shopping"></i></a>
                                    <div class="cart-badge cartItems">{{count(session('cart')??[])}} </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Header Lower -->


    <!-- sticky header -->
    <div class="sticky-header">
        <div class="header-upper">
            <div class="container">
                <div class="inner-container d-flex align-items-center justify-content-between">
                    <div class="left-column d-flex align-items-center">
                        <div class="logo-box">
                            <div class="logo"><a href="{{route('page')}}"><img
                                        src="{{getFile(basicControl()->logo_driver,basicControl()->logo)}}" alt="logo"></a>
                            </div>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="nav-outer">
                            <div class="mobile-nav-toggler"><i class="fa-regular fa-bars-staggered"></i></div>
                            <nav class="main-menu navbar-expand-md navbar-light"></nav>
                        </div>
                        @if(basicControl()->ecommerce)
                            <div class="header-right-search-container">
                                <div class="search">
                                    <a href="javascript:void(0)" class="search-icon search-btn"><i
                                            class="fa-light fa-magnifying-glass"></i></a>
                                </div>
                                <div class="cart">
                                    <a href="{{route('cart')}}" class="cart-icon"><i
                                            class="fa-light fa-cart-shopping"></i></a>
                                    <div class="cart-badge cartItems">{{count(session('cart')??[])}}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- sticky header -->


    <!-- mobile menu -->
    <div class="mobile-menu">
        <div class="menu-backdrop"></div>
        <div class="close-btn"><span class="fal fa-times"></span></div>

        <nav class="menu-box">
            <div class="nav-logo"><a href="{{route('page')}}"><img
                        src="{{getFile(basicControl()->logo_driver,basicControl()->logo)}}" alt="logo"></a></div>
            <div class="menu-outer">
                <!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header--></div>
            <!--Social Links-->
            <div class="social-links">
                <ul class="clearfix">
                    @foreach(collect($footer_section['multiple'])->toArray() as $item)
                        <li><a href="{{$item['media']->link}}"><span class="{{$item['media']->icon}}"></span></a></li>
                    @endforeach
                </ul>
            </div>
        </nav>
    </div>
    <!-- mobile menu -->
</header>
<!-- Header eand -->
