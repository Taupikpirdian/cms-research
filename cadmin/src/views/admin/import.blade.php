@extends('cactuar::admin.layout-master')

@section('content')

@if (is_array($mainAction)) 
    <div class="col-md-12">
        @foreach ($mainAction AS $v)
            <a href="{{ array_get($v, 'url') }}" class="btn btn-app">
                <i class="fa fa-{{ array_get($v, 'fa') }}"></i>
                {!! array_get($v, 'label') !!}
            </a>
        @endforeach
    </div>
@endif

<form method="post" class="form-validate" style='max-width:1200px;' enctype='multipart/form-data'>
    <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
    <div class="col-md-12 col-xs-12 ">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Import Data</h3>
            </div>
		    <div class="box-body">
                
                <div class="form-group clearfix" origin="'.$key.'">
					<label class="col-md-2 col-xs-12 control-label">Separator/Delimiter</label>
					<div class="col-md-10 col-xs-12">
                        {!! Form::select('separator', [';' => 'Semicolon ( ; )', ',' => 'Comma ( , )'], null, ['class' => 'form-control required', 'style' => 'max-width:200px;']) !!}
                    </div>
                </div>
                
                <div class="form-group clearfix" origin="'.$key.'">
					<label class="col-md-2 col-xs-12 control-label">CSV File</label>
					<div class="col-md-10 col-xs-12">
                        {!! Form::file('csv', ['class' => 'required']) !!}
                    </div>
                </div>
                
                <ul class="info">
                    <li>Max file size 2MB</li>
                    <li>Some Installed Excel program will generate CSV with semicolon ( ; ) separator, instead of comma ( , ) separator.<br>Please check your separator type before upload your CSV File.</li>
                </ul>
			</div>
		</div>
	</div>
	
	<div class="col-md-12 col-xs-12">
        <input type="submit" class="btn btn-primary" value="SAVE" style="padding:15px 60px;">
    </div>
    
    {!! $append !!}
</form>

<style>
    ul.info {
        font-style:italic;    
        border-top:thin solid #aaa;
        margin-top:30px;
        padding-top:10px;
    }
    
    ul.info li {
        list-style: none;
        padding-left:1em;
        text-indent:-1em;
    }
    
    ul.info li:before {
        content:"*";
        padding-right:5px;
    }
</style>

@endsection