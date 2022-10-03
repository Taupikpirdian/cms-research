@extends('cactuar::admin.layout-guest')

@section('content')

{!! Form::open(['url' => admin::url('guest/login'), 'class' => 'form-common-validate']) !!}
	@include('cactuar::admin.layout-message')
	<div>
		<div class="form-group has-feedback">
			<input type="text" class="form-control required" placeholder="Username" name='username' required="">
			<span class="glyphicon glyphicon-user form-control-feedback"></span>
			<label class='error' for='username'></label>
		</div>
		<div class="form-group has-feedback">
			<input type="password" class="form-control required" placeholder="Password" name='password' required="">
			<span class="glyphicon glyphicon-lock form-control-feedback"></span>
			<label class='error' for='password'></label>
		</div>
        
        @if(config('cadmin.password.login-captcha') == true)
            {!! recaptcha::drawV3() !!}
            <br>
        @endif
        
		<div class="form-group sidebottom">
			<div class="left">
					<a href="{{ url()->admin('guest/forgot') }}">Forgot Password?</a>
			</div>
			<div class="right">
				<button type="submit" class="btn btn-primary btn-block btn-flat btn-login"></button>
			</div>
		</div>
	</div>
	@if (Session::get('redirectParam'))
		<input type="hidden" name="redirect" value="{{ Session::get('redirectParam') }}">
	@endif
{!! Form::close() !!}

@endsection