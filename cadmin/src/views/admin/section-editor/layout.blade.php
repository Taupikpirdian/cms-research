<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Section Editor</title>
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
    
    <script src="{{ asset('adminLTE-2/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
    <meta name="robots" content="noindex">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition ">
<div class="wrapper">

@yield('content')

</div>
</body>
</html>
