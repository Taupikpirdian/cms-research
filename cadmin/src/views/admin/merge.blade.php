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
                <div class="clear">
                @foreach (array_get($data,'subMenu') as $v) 
                    <a href="{{ array_get($v, 'url') }}" class="btn btn-flat bg-blue"><i class="fa fa-{{ array_get($v, 'fa') }}"></i> {!! array_get($v, 'label') !!}</a>
                @endforeach
                </div>
            </div>
			<div class="box-body">
                <div style="text-align:right;margin-bottom:30px;">
                    <div>
                        Last Update : 
                        @if ($data['log'])
                            {{ helper::date2string($data['log']->updated_at,'d M Y H:i') }}<br>
                            Draft by : <b>{{ $data['log']->draft_by }}</b><br>
                            @if ($data['log']->status == 1)
                            Approve by : <b>{{ $data['log']->approve_by }}</b>
                            @endif
                        @else
                            - none -
                        @endif
                    </div>
                    <div style="margin-top:10px;">
                        @if ($data['deleteUrl'])
                            <a href="{{ $data['deleteUrl'] }}" class="btn btn-flat bg-red" onClick="return confirm('Are you sure delete draft?');"><i class="fa fa-trash"></i> Delete Draft</a>
                        @endif
                        @if ($data['previewUrl'])
                            <a href="{{ $data['previewUrl'] }}" class="btn btn-flat bg-aqua" target="_blank"><i class="fa fa-eye"></i> Preview Draft</a>
                        @endif
                    </div>
                </div>
				{!! array_get($data, 'form') !!}
			</div>
		</div>
	</div>
	
	<div class="col-md-12 col-xs-12">
        <input type="submit" class="btn btn-primary bg-green" value="SAVE & MERGE DRAFT" style="padding:15px 60px;">    
    </div>
</form>

{!! array_get($data, 'append') !!}
@include('cactuar::admin.component.copy-translation-js')
@endsection