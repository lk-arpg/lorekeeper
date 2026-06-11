<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
    header('Permissions-Policy: interest-cohort=()');
    ?>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (config('lorekeeper.extensions.use_recaptcha'))
        <!-- ReCaptcha v3 -->
        {!! RecaptchaV3::initJs() !!}
    @endif

    <title>{{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}{!! View::hasSection('title') ? ' - ' . trim(View::getSection('title')) : '' !!}</title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}{!! View::hasSection('title') ? ' - ' . trim(View::getSection('title')) : '' !!}">
    <meta name="description" content="{!! View::hasSection('meta-desc') ? trim(strip_tags(View::getSection('meta-desc'))) : config('lorekeeper.settings.site_desc', 'A Lorekeeper ARPG') !!}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url', 'http://localhost') }}">
    <meta property="og:image" content="{{ View::hasSection('meta-img') ? View::getSection('meta-img') : asset('images/meta-image.png') }}">
    <meta property="og:title" content="{{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}{!! View::hasSection('title') ? ' - ' . trim(View::getSection('title')) : '' !!}">
    <meta property="og:description" content="{!! View::hasSection('meta-desc') ? trim(strip_tags(View::getSection('meta-desc'))) : config('lorekeeper.settings.site_desc', 'A Lorekeeper ARPG') !!}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ config('app.url', 'http://localhost') }}">
    <meta property="twitter:image" content="{{ View::hasSection('meta-img') ? View::getSection('meta-img') : asset('images/meta-image.png') }}">
    <meta property="twitter:title" content="{{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}{!! View::hasSection('title') ? ' - ' . trim(View::getSection('title')) : '' !!}">
    <meta property="twitter:description" content="{!! View::hasSection('meta-desc') ? trim(strip_tags(View::getSection('meta-desc'))) : config('lorekeeper.settings.site_desc', 'A Lorekeeper ARPG') !!}">

    <!-- No AI scraping directives -->
    <meta name="robots" content="noai">
    <meta name="robots" content="noimageai">

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    <script defer src="{{ mix('js/app-secondary.js') }}"></script>
    <script defer src="{{ asset('js/site.js') }}"></script>
    <script src="{{ asset('js/tinymce.min.js') }}"></script>
    <script src="{{ asset('js/jquery.tinymce.min.js') }}"></script>
    <script defer src="{{ asset('js/bootstrap-colorpicker.min.js') }}"></script>
    <script src="{{ asset('js/bs-custom-file-input.min.js') }}"></script>
    <script defer src="{{ asset('js/jquery-ui-timepicker-addon.js') }}"></script>
    <script defer src="{{ asset('js/croppie.min.js') }}"></script>
    <script src="{{ asset('js/jquery.ui.touch-punch.min.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/lorekeeper.css?v=' . filemtime(public_path('css/lorekeeper.css'))) }}" rel="stylesheet">

    {{-- Font Awesome --}}
    <link defer href="{{ faVersion() }}" rel="stylesheet">

    {{-- jQuery UI --}}
    <link defer href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">

    {{-- Bootstrap Toggle --}}
    <link defer href="{{ asset('css/bootstrap4-toggle.min.css') }}" rel="stylesheet">

    <link defer href="{{ asset('css/lightbox.min.css') }}" rel="stylesheet">
    <link defer href="{{ asset('css/bootstrap-colorpicker.min.css') }}" rel="stylesheet">
    <link defer href="{{ asset('css/jquery-ui-timepicker-addon.css') }}" rel="stylesheet">
    <link defer href="{{ asset('css/croppie.css') }}" rel="stylesheet">
    <link defer href="{{ asset('css/selectize.bootstrap4.css') }}" rel="stylesheet">

    @if (file_exists(public_path() . '/css/custom.css'))
        <link href="{{ asset('css/custom.css') . '?v=' . filemtime(public_path('css/custom.css')) }}" rel="stylesheet">
    @endif

    @include('feed::links')
    @if (Auth::check())
        @include('layouts.user_style_settings')
    @endif


    @yield('head')
</head>

<body>
    <div id="app">
        <div class="site-header-image" id="header" style="background-image: url('{{ asset('images/header.png') }}');"></div>
        @include('layouts._nav')
        @if (View::hasSection('sidebar'))
            <div class="site-mobile-header bg-secondary"><a href="#" class="btn btn-sm btn-outline-light" id="mobileMenuButton">Menu <i class="fas fa-caret-right ml-1"></i></a></div>
        @endif

        <main class="container-fluid">
            <div class="row">

                <div class="sidebar col-lg-2" id="sidebar">
                    @yield('sidebar')
                </div>
                <div class="main-content col-lg-8 p-4">
                    <div>
                        @if (Settings::get('is_maintenance_mode'))
                            <div class="alert alert-secondary">
                                The site is currently in maintenance mode!
                                @if (!Auth::check() || !Auth::user()->hasPower('maintenance_access'))
                                    You can browse public content, but cannot make any submissions.
                                @endif
                            </div>
                        @endif
                        @if (Auth::check() && !config('lorekeeper.extensions.navbar_news_notif'))
                            @if (Auth::user()->is_news_unread)
                                <div class="alert alert-info"><a href="{{ url('news') }}">There is a new news post!</a></div>
                            @endif
                            @if (Auth::user()->is_sales_unread)
                                <div class="alert alert-info"><a href="{{ url('sales') }}">There is a new sales post!</a></div>
                            @endif
                        @endif
                        @include('flash::message')
                        @yield('content')
                    </div>

                    <div class="site-footer mt-4" id="footer">
                        @include('layouts._footer')
                    </div>
                </div>
            </div>

        </main>


        <div class="modal fade" id="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0"></span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>

        @yield('scripts')
        @include('layouts._pagination_js')
        <script>
            $(document).on('focusin', function(e) {
                if ($(e.target).closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
                    e.stopImmediatePropagation();
                }
            });

            $(document).ready(function() {
                $('[data-toggle="tooltip"]').tooltip({
                    html: true
                });
                $('.cp').colorpicker();

                bsCustomFileInput.init();
                var $mobileMenuButton = $('#mobileMenuButton');
                var $sidebar = $('#sidebar');
                $('#mobileMenuButton').on('click', function(e) {
                    e.preventDefault();
                    $sidebar.toggleClass('active');
                });

                $('.inventory-log-stack').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('items') }}/" + $(this).data('id') + "?read_only=1", $(this).data('name'));
                });

                $('.spoiler-text').hide();
                $('.spoiler-toggle').click(function() {
                    $(this).next().toggle();
                });
            });
        </script>
    </div>
</body>

</html>
