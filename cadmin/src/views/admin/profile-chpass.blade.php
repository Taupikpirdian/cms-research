@extends('cactuar::admin.layout-master')

@section('content')

<div class="col-md-12">
	<a href="{{ admin::url('profile') }}" class="btn btn-app"><i class="fa fa-user"></i> My Profile</a>
	<a href="{{ admin::url('profile/chpass') }}" class="btn btn-app"><i class="fa fa-lock"></i> Change Password</a>
</div>


<div class="col-md-6 col-xs-12">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Change my password</h3>
        </div>
		<div class="box-body">
			<form method="post" class="form-validate">
				<input type="hidden" name="_token" value="{!! csrf_token(); !!}">
				<div class="">
					<div class="form-group">
						<label>Login Name</label>
						<input type='text' name='' value="{{ Auth::user()->name }}" class="form-control" readonly>
					</div>
					<div class="form-group">
						<label>Privilege</label>
						<input type='text' name='' value="@if (Auth::user()->isRoot) root @else {{ Auth::user()->privilege->label }} @endif" class="form-control" readonly>
					</div>
					<div class="form-group">
						<label>Old Password</label>
						<input type='password' name='old_pass' value="" class="form-control required">
					</div>
					<div class="form-group">
						<label>New Password</label>
						<input type='password' id='password' name='password' value="" class="form-control required">
                        <div>{!! implode('<br>',$passwordInfo) !!}</div>
					</div>
					<div class="form-group">
						<label>Confirm New Password</label>
						<input type='password' name='confirm_password' value="" class="form-control required equalTo" equalTo="#password">
					</div>
				</div>
				
				<div class="clearfix"></div>
				
				<div class="box-footer">
					<input type="submit" class="btn btn-primary" value="Update">
				</div>
			</form>
		</div>
	</div>
</div>

@endsection