<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
	<link rel='stylesheet' type='text/css' href="{{ asset('fa/css/font-awesome.css') }}">
</head>

<body>
    <style>
        .page-box { text-align:center; font-family:'Roboto', sans-serif; color:#555; }
        .page-content { display:inline-block; margin:80px 0px; padding:20px; border:thin solid #aaa;}
        a.btn { background:#3c8dbc; color:#fff; text-decoration:none; padding:10px 20px; }
    </style>
	
    <div class='middle-content'>
		<div class='page-box'>
			<div class='page-content' >
				@yield('content')
			</div>
		</div>
	</div>
</body>

</html>