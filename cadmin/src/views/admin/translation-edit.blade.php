@extends('cactuar::admin.layout-master')

@section('content')

<a href="{{ admin::url(admin::module()) }}" class="btn btn-app">
    <i class="fa fa-list"></i>
    Index
</a>

<div class="col-md-12 ">
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title">Edit Translation</h3>
        </div>
        
        <div class="box-body">
            <div class="clearfix"></div>
            
            <form method="post" class="form-validate" style='max-width:1200px;'>
                <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
                <div class="form-group clearfix">
					<label class="col-md-2 col-xs-12 control-label">Code</label>
					<div class="col-md-10 col-xs-12">
                        <input type="text" class="form-control" name="code" value="{{ $param['code'] }}" readonly>
                    </div>
                </div>
                
                @foreach (lang::codes() as $v) 
				<div class="form-group clearfix">
					<label class="col-md-2 col-xs-12 control-label">Translation [ {{ strtoupper($v) }} ]</label>
					<div class="col-md-10 col-xs-12">
                        <textarea class="form-control 
                            @if(substr($param['code'],-5) == '_html')
                                ckeditor
                            @endif" name="translation[{{$v}}]"
                            
                        >{{ lang::translate($param['code'], [], $v) }}</textarea>
                    </div>
                </div>
				@endforeach
				
				<div class="form-group clearfix">
					<em class="form-info">
					@if (!empty($param['keywords']))
						Available keywords :<br>
						<ul>
						@foreach ($param['keywords'] as $v)
							<li>{{ $v }}</li>
						@endforeach
						</ul>
					@endif
					</em>
				</div>
                
                <div class="col-md-12 col-xs-12">
                    <input type="submit" class="btn btn-primary" value="UPDATE" style="padding:15px 60px;">
                </div>
            </form>
        </div>
    </div>
</div>

@endsection