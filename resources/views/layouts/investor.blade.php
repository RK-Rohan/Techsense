<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ Session::get('business.name') }}</title>
    @include('layouts.partials.meta-icons')

    <link rel="stylesheet" href="{{ asset('css/vendor.css?v='.$asset_v) }}">
    <link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">
    @yield('css')
</head>
<body class="hold-transition skin-blue-light layout-top-nav">
<div class="wrapper">

    <header class="main-header">
        <nav class="navbar navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <span class="navbar-brand" style="font-weight:600;">
                        @if(!empty(Session::get('business.logo')))
                            <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}"
                                 alt="Logo" style="height:30px; display:inline-block; vertical-align:middle;">
                        @else
                            {{ Session::get('business.name') }}
                        @endif
                    </span>
                </div>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-user-tie"></i>
                                <span class="hidden-xs">{{ auth()->user()->investor->name ?? auth()->user()->username }}</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-footer">
                                    <div class="pull-right">
                                        <a href="{{ url('/logout') }}" class="btn btn-default btn-flat">
                                            <i class="fa fa-sign-out-alt"></i> @lang('lang_v1.sign_out')
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="content-wrapper" style="margin-left:0;">
        <input type="hidden" id="__code" value="{{ session('currency')['code'] }}">
        <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] }}">
        <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] }}">
        <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] }}">
        <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">

        @yield('content')
    </div>

    <footer class="main-footer" style="margin-left:0;">
        <div class="container text-center text-muted">
            {{ Session::get('business.name') }}
        </div>
    </footer>
</div>

<script src="{{ asset('js/vendor.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/functions.js?v=' . $asset_v) }}"></script>
@yield('javascript')
</body>
</html>
