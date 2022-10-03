@extends('cactuar::admin.layout-guest')

@section('content')
	{!! Form::open(['url' => admin::url('guest/forgot'), 'class' => 'form-common-validate']) !!}
		@include('cactuar::admin.layout-message')
		<div>
			<div class="form-group has-feedback">
				<input type="text" class="form-control email required" placeholder="Username" name='username' required="">
				<span class="glyphicon glyphicon-user form-control-feedback"></span>
				<label class='error' for='username'></label>
			</div>
            
            @if(config('cadmin.password.login-captcha') == true)
                {!! recaptcha::drawV3() !!}
                <br>
            @endif
            
			<div class="form-group sidebottom">
				<div class="left">
					<a href="{{ url()->admin('guest/login') }}">Back to Login page</a>
				</div>
				<div class="right">
					<button type="submit" class="btn btn-primary btn-block btn-flat">SUBMIT</button>
				</div>
			</div>
		</div>
	{!! Form::close() !!}
@endsection