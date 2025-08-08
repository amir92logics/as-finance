<!-- Sidebar section start -->
<aside id="sidebar" class="sidebar">
    @php
       $rank = auth()->user()->rank;
    $user   = auth()->user();
    @endphp

    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link {{menuActive(['user.dashboard'])}}" href="{{route('user.dashboard')}}">
                <i class="fa-regular fa-grid"></i>
                <span>@lang('Dashboard')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed {{isMenuActive(['user.plans'])}}" href="{{route('user.plans')}}">
                <i class="fa-brands fa-codepen"></i>
                <span>@lang('Investment Plan')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed {{isMenuActive(['user.invests'])}}" href="{{route('user.invests')}}">
                <i class="fa-brands fa-slack"></i>
                <span>@lang('Invests')</span>
            </a>
        </li> 
        <li class="nav-item">
            <a class="nav-link collapsed {{isMenuActive(['user.plan.investment'])}}"
               href="{{route('user.plan.investment')}}">
                <i class="fal fa-cube"></i>
                <span>@lang('Plan invest history')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed {{isMenuActive(['user.project.investment'])}}"
               href="{{route('user.project.investment')}}">
                <i class="fal fa-tractor"></i>
                <span>@lang('Invest history')</span>
            </a>
        </li>

       @if(basicControl()->ecommerce)
            <li class="nav-item">
                <a class="nav-link collapsed {{isMenuActive(['user.orders'])}}" href="{{route('user.orders')}}">
                    <i class="fal fa-shopping-basket" aria-hidden="true"></i>
                    <span>@lang('Orders')</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed {{isMenuActive(['user.wishlist'])}}" href="{{route('user.wishlist')}}">
                    <i class="fal fa-heart" aria-hidden="true"></i>
                    <span>@lang('Wishlist')</span>
                </a>
            </li>
       @endif

        <li class="nav-item">
            <a class="nav-link collapsed {{ isMenuActive('user.add.fund') }}"
               href="{{route('user.add.fund')}}">
                <i class="fal fa-wallet"></i>
                <span>@lang('Deposit')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed {{ isMenuActive("user.fund.index") }}"
               href="{{ route('user.fund.index') }}">
                <i class="fa-light fa-tasks-alt"></i>
                <span>@lang('Deposit History')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed {{ isMenuActive('user.payout') }}"
               href="{{ route('user.payout') }}">
                <i class="fal fa-hand-holding-dollar"></i>
                <span>@lang('Payout')</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ isMenuActive('user.payout.index') }}"
               href="{{ route("user.payout.index") }}">
                <i class="fal fa-table-list"></i>
                <span>@lang('Payout History')</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ isMenuActive('user.referral') }}"
               href="{{route('user.referral')}}">
                <i class="fa-regular fal fa-line-chart"></i>

                <span>@lang('Referral')</span>
            </a>
        </li>
        <li class="nav-item ">
            <a class="nav-link {{ isMenuActive('user.referral.bonus') }}"
               href="{{route('user.referral.bonus')}}">
                <i class="fa-sharp fa-thin fa-gift"></i>
                <span>@lang("Referral Bonus")</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed {{ isMenuActive('user.transaction.list') }}"
               href="{{route('user.transaction.list')}}">
                <i class="fal fa-chart-line"></i><span>@lang("Transaction")</span></a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed {{ isMenuActive('user.badges') }}"
               href="{{route('user.badges')}}">
                <i class="fal fa-badge" aria-hidden="true"></i><span>@lang("Badges")</span></a>
        </li>

        <li class="nav-item ">
            <a class="nav-link {{ isMenuActive(['user.ticket.list','user.ticket.view','user.ticket.create']) }}"
               href="{{route('user.ticket.list')}}">
                <i class="fa-regular fal fa-user-headset"></i>
                <span>@lang("Support Ticket")</span>
            </a>
        </li>

        <li class="nav-item ">
            <a class="nav-link {{ isMenuActive('user.notification.permission') }}"
               href="{{route('user.notification.permission')}}">
                <i class="fa-regular fa-bell"></i>
                <span>@lang("Notification Permission")</span>
            </a>
        </li>
    </ul>


</aside>
<!-- Sidebar section end -->
