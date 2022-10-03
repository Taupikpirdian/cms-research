@extends('cactuar::admin.layout-master')

@section('content')

@if (is_array(array_get($data, 'menu'))) 
    <div class="col-md-12">
        @foreach (array_get($data, 'menu') AS $v)
            <a href="{{ array_get($v, 'url') }}" class="btn btn-app">
                <i class="fa fa-{{ array_get($v, 'fa') }}"></i>
                {!! array_get($v, 'label') !!}
            </a>
        @endforeach
    </div>
@endif

<form method="post" class="form-validate" style='max-width:1200px;'>
    <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
    <div class="col-md-12 col-xs-12 ">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{!! array_get($data, 'label') !!}</h3>
            </div>
			<div class="box-body">
				{!! array_get($data, 'form') !!}
			</div>
		</div>
	</div>
	
	<div class="col-md-12 col-xs-12">
        <input type="submit" class="btn btn-primary" value="UPDATE" style="padding:15px 60px;">
    </div>
</form>

{!! array_get($data, 'append') !!}

@include('cactuar::admin.component.copy-translation-js')
@endsection