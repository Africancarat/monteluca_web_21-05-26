@extends('master.front')
@section('title')
    {{__('Dashboard')}}
@endsection
@section('content')

<!-- Page Title-->
<div class="page-title">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <ul class="breadcrumbs">
                    <li><a href="{{__('front.index')}}">{{__('Home')}}</a> </li>
                    <li class="separator"></li>
                    <li>{{__('Welcome Back')}} </li>
                  </ul>
            </div>
        </div>
    </div>
  </div>

  <!-- Page Content-->
  <div class="container  padding-bottom-3x mb-1">
  <div class="row">
         @include('includes.user_sitebar')
          <div class="col-lg-8">
            <div class="padding-top-2x mt-2 hidden-lg-up"></div>
                <div class="row u-d-d">
                    @if (!empty($assignedReferralCodes) && $assignedReferralCodes->isNotEmpty())
                        <div class="col-md-12 mb-4">
                            <div class="card round">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <p class="text-muted mb-1">{{ __('Referral Code and Wallet Balance') }}</p>
                                            <h4 class="mb-0"><b>{{ PriceHelper::setCurrencyPrice(Auth::user()->referral_balance ?? 0) }}</b></h4>
                                        </div>
                                        <i class="icon-credit-card"
   style="
   font-size:32px;
   display:flex;
   align-items:center;
   justify-content:center;
   width:60px;
   height:60px;
   border-radius:50%;
   background:#000;
   color:#fff;
"></i>
                                    </div>

                                    <div class="row">
                                        @foreach ($assignedReferralCodes as $referralCode)
                                            <div class="col-md-6 mb-3">
                                                <div class="border rounded p-3 h-100">
                                                    <span class="text-muted small d-block">{{ __('Referral Code') }}</span>
                                                    <h5 class="mb-2"><b>{{ $referralCode->referral_code }}</b></h5>
                                                    <p class="small mb-1">{{ __('Discount') }}: {{ $referralCode->discount_percent }}%</p>
                                                    <p class="small mb-1">{{ __('Cashback') }}: {{ $referralCode->cashback_percent }}%</p>
                                                    <p class="small mb-0">{{ __('Total Earned') }}: {{ PriceHelper::setCurrencyPrice($referralCode->total_earned_cashback ?? 0) }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6 mb-4">
                        <div class="card round">
                            <div class="card-body text-center">
                                <i class="icon-shopping-bag"></i>
                                <p class="mt-3">{{__('All Order')}}</p>
                                <h4><b>{{$allorders}}</b></h4>
                            </div>
                        </div>
                    </div>
                        <div class="col-md-6 mb-4">
                            <div class="card round">
                                <div class="card-body text-center">
                                    <i class="icon-shopping-bag"></i>
                                    <p class="mt-3">{{__('Pending Order')}}</p>
                                    <h4><b>{{$pending}}</b></h4>
                                </div>
                            </div>
                        </div>
                    <div class="col-md-6 mb-4">
                        <div class="card round">
                            <div class="card-body text-center">
                                <i class="icon-shopping-bag"></i>
                                <p class="mt-3">{{__('Completed Order')}}</p>
                                <h4><b>{{$delivered}}</b></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card round">
                            <div class="card-body text-center">
                                <i class="icon-shopping-bag"></i>
                                <p class="mt-3">{{__('Processing Order')}}</p>
                                <h4><b>{{$progress}}</b></h4>
                            </div>
                        </div>
                    </div>
                        <div class="col-md-6 mb-4">
                            <div class="card round">
                                <div class="card-body text-center">
                                    <i class="icon-shopping-bag"></i>
                                    <p class="mt-3">{{__('Manufacturing Order')}}</p>
                                    <h4><b>{{$Manufacturing}}</b></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card round">
                                <div class="card-body text-center">
                                    <i class="icon-shopping-bag"></i>
                                    <p class="mt-3">{{__('shipped Order')}}</p>
                                    <h4><b>{{$shipped}}</b></h4>
                                </div>
                            </div>
                        </div>
                    <div class="col-md-6 mb-4">
                        <div class="card round">
                            <div class="card-body text-center">
                                <i class="icon-shopping-bag"></i>
                                <p class="mt-3">{{__('Canceled Order')}}</p>
                                <h4><b>{{$canceled}}</b></h4>
                            </div>
                        </div>
                    </div>
                        <div class="col-md-6 mb-4">
                            <div class="card round">
                                <div class="card-body text-center">
                                    <i class="icon-shopping-bag"></i>
                                    <p class="mt-3">{{__('Refunded Order')}}</p>
                                    <h4><b>{{$Refunded}}</b></h4>
                                </div>
                            </div>
                        </div>
                </div>
          </div>
        </div>
  </div>
@endsection
