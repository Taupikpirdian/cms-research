<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Admin Login</title>
	
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('fa/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('bootstrap/css/ionicons.min.css') }}">
	<link rel="stylesheet" href="{{ asset('adminLTE-2/css/AdminLTE.min.css') }}">
	<link rel="stylesheet" href="{{ asset('root/c.css') }}">
	<meta name="robots" content="noindex">
</head>
<body>
	<style>
		body { background-color:#d2d6de; }
	</style>
	
	<div class="login-box">
        @if ($website->logo)
		<div class="login-logo">
			<img src="{{ media::url($website->logo) }}">
		</div>
		@endif
		
		<div class="login-box-body">
            <p class="login-box-msg">Sign in to start your session</p>
		      	
			{!! Form::open(['url' => admin::url('guest/login'), 'class' => 'form-common-validate']) !!}
				
				@include('cactuar::admin.layout-message')
				
				<div class="form-group has-feedback">
					<input type="text" class="form-control required" placeholder="Username" name='username'>
					<span class="glyphicon glyphicon-user form-control-feedback"></span>
					<label class='error' for='username'></label>
				</div>
				<div class="form-group has-feedback">
					<input type="password" class="form-control required" placeholder="Password" name='password'>
					<span class="glyphicon glyphicon-lock form-control-feedback"></span>
					<label class='error' for='password'></label>
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
				</div>
				
			{!! Form::close() !!}

			{{--<p>
				<a href="#">forgot password?</a><br>
			</p>--}}
		</div>
	</div>
	
	<script src="{{ asset('adminLTE-2/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
	<script src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('adminLTE-2/plugins/iCheck/icheck.min.js') }}"></script>

	<script src="{{ asset('root/jquery.validate.min.js') }}"></script>
	<script src="{{ asset('adminLTE-2/plugins/fastclick/fastclick.js') }}"></script>
	<script src="{{ asset('adminLTE-2/dist/js/app.min.js') }}"></script>
</body>
</html>