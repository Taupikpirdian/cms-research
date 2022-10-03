<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@if (isset($title)) {{ strip_tags($title) }} @else {{ strip_tags($baseTitle) }} @endif - Admin</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('fa/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('ion/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/css/skins/_all-skins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/iCheck/flat/blue.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/morris/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/jvectormap/jquery-jvectormap-1.2.2.cs') }}s">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/datepicker/datepicker3.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/daterangepicker/daterangepicker-bs3.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/iCheck/all.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/select2/select2.min.css') }}">
	
    <link rel="stylesheet" href="{{ asset('adminLTE-2/plugins/colorpicker/bootstrap-colorpicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE-2/css/AdminLTE.min.css') }}">
	
	<link rel="stylesheet" href="{{ asset('root/adminC.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-wcms/css/style.css') }}">
    
    <script src="{{ asset('adminLTE-2/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
    <meta name="robots" content="noindex">
    
    @php
    $favicon = conf('site-setting')->favicon;
    
    if (media::source($favicon)) {
        if (strtolower(pathinfo($favicon, PATHINFO_EXTENSION) == 'png')) {
            echo '<link rel="icon" type="image/png" href="'.media::thumb($favicon, 'favicon').'" sizes="32x32">'.PHP_EOL;
        }

        if (strtolower(pathinfo($favicon, PATHINFO_EXTENSION) == 'ico')) {
            echo '<link rel="icon" href="'.media::url($favicon).'">'.PHP_EOL;
        }
    }
    
    @endphp
    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    
    @if (!Request::has('opener'))
        <header class="main-header">
            <!-- <a href="{{ url(ADMIN) }}" class="logo" @if ($website->logo) style="background-image:url('{{ media::url($website->logo) }}');" @endif>&nbsp;</a> -->
            <a href="{{ url(ADMIN) }}" class="logo">
                <span class="logo-lg"><img src="{{ media::url($website->logo) }}" alt=""></span>
                <span class="logo-mini"><img src="{{ media::url($website->logo) }}" alt=""></span>
            </a>
            @include('cactuar::admin.layout-top-menu')
        </header>

        @include('cactuar::admin.layout-nav')
    @endif
    
    <div class="content-wrapper">
        <section class="content-header">
            <h1 style="color:#3c8dbc;">{!! $title ?? $baseTitle !!}</h1>
        </section>
        
        <div class="col-md-12" style="margin-top:10px;">
            @include('cactuar::admin.layout-message')
        </div>
        
        <section class="content">
            <div class="row">
                @yield('content')
            </div>
        </section>
    </div>
    <footer class="main-footer">
        {{--@copyright--}}
    </footer>
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<script>
    var $url = "{{ admin::url() }}";
    var $asset = "{{ asset('') }}";
    var $mediaPath = "{{ asset(Config::get('cadmin.media.sourcepath')) }}";
    var $token = "{{ csrf_token() }}";
    var $locale = "{{ App::getLocale() }}";
    var $lang = "{{ Config::get('cadmin.lang.default') }}";
	
	//remove auto-build by ckeditor, add wrap textarea
	$('.ckeditor').removeClass('ckeditor').addClass('my-ckeditor').wrap('<div class="ckeditor-container" style="min-height:320px;">');
</script>
<script src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/knob/jquery.knob.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/moment/moment.js') }}"></script>    
<script src="{{ asset('adminLTE-2/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/slimScroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/ckeditor/adapters/jquery.js') }}"></script>
<script src="{{ asset('adminLTE-2/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>
<script src="{{ asset('adminLTE-2/js/app.min.js') }}"></script>
<script src="{{ asset('adminLTE-2/js/demo.js') }}"></script>

<script src="{{ asset('root/jquery.validate.min.js')}}"></script>
<script src="{{ asset('root/additional-methods.min.js')}}"></script>
<script src="{{ asset('root/autoNumeric-1.6.2.js') }}"></script>    
<script src="{{ asset('adminLTE-2/plugins/iCheck/icheck.min.js') }}"></script>   
    
<!-- WYSIHTML -->
<script src="{{ asset('wysihtml/wysihtml.js') }}"></script>
<script src="{{ asset('wysihtml/wysihtml.all-commands.js') }}"></script>
<script src="{{ asset('wysihtml/wysihtml.toolbar.js') }}"></script>
<script src="{{ asset('wysihtml/parser_rules/advanced_unwrap.js') }}"></script>
  
<!-- SWAPIES -->
<script src="{{ asset('root/jquery-swapsies.js') }}"></script>
  
<script src="{{ asset('root/c.js')}}"></script>
<script src="{{ asset('root/adminC.js')}}"></script>
    
    </body>
</html>
