@extends('cactuar::admin.layout-master')

@section('content')

<div class="col-md-12 ">
    <div class="box box-primary">
		<div class="box-header with-border">
			<h3 class="box-title">{{ config('cadmin.menu.'.$module.'.label') }} Listing</h3>
		</div>
        <div class="box-body"> 
			@if(Auth::user()->allow($module,'create'))
				<a href="{{ url()->admin($module.'/create') }}" class="btn btn-flat bg-blue"><i class="fa fa-plus"></i> Create</a>
			@endif
            <div class="table-responsive"> 
    			@php 
    			$query = Request::query();
    			foreach (['sort', 'filter', 'search', 'range', 'page'] as $empty) {
    				if (array_key_exists($empty, $query))
    					unset($query[$empty]);
    			}
    			@endphp
                <form method="post" action="{{ admin::url('admin/filter?'.http_build_query($query)) }}" class="pull-right admin-filter ">
                    <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
                    <input type="hidden" name="segments" value="{{ json_encode(Request::segments()) }}">

                    <div>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control" name='search' value="{{ Request::input('search') }}">
                        </div>
                    </div>
                    <div>
                        <input type='submit' class='btn btn-flat bg-purple btn-midh' value='Filter'>
                    </div>
                </form>
                <table class="table table-hover">
                    <tr>
                        <th key-name="label">Label</th>
                        <th key-name="type">Type</th>
                        <th key-name="action">Sort ID</th>
                        <th key-name="time-created_at">Created</th>
                        <th key-name="time-updated_at">Updated</th>
                        <th key-name="action">&nbsp;</th>
                    </tr>
                    
                    @if(count($data) == 0)
                        <tr>
                            <td colspan="6" style="background-color:#aff;"><em>---- Empty data</em></td>
                        </tr>
                    @endif

                    @foreach($data as $v)
                    @php
                    $color = 255 - ($v['deep'] * (55/$maxDeep));
                    @endphp
                    
                    <tr data-parent="{{ $v['item']->parent_id }}" style="@if ($v['item']->parent_id) display:none; @endif background-color:rgb({{ $color }},{{ $color }},{{ $color }});">
                        <td class="data-label" style="padding-left:{{ ($v['deep'] * 30) + 8 }}px;"> 
                            @if(count($v['childs']) > 0) <a href="" data-id="{{ $v['item']->id }}" data-show="hide">{{ $v['label'] }}&nbsp;&nbsp;<i class="fa fa-chevron-left"></i></a> @else {{ $v['label'] }} @endif</td>
                        <td>{{ $v['type'] }}</td>
                        <td style="white-space:nowrap;">{{ $v['item']->sort_id }}
                            @if ($v['item']->sort_id < $v['item']->maxSort)
                            &nbsp;&nbsp;
                            <a 
                                href="{{ url()->admin('menu/order-up?unique='.$v['item']->id) }}"
                                class="fa fa-chevron-down need-confirm"
                                data-confirm="Are you sure to reorder this item?"
                                ></a>
                            @endif
                            
                            @if($v['item']->sort_id > 1)
                            &nbsp;&nbsp;
                                <a 
                                href="{{ url()->admin('menu/order-down?unique='.$v['item']->id) }}" 
                                class="fa fa-chevron-up need-confirm"
                                data-confirm="Are you sure to reorder this item?"
                                ></a>
                            @endif
                        </td>
                        <td>{{ $v['item']->created_at->format('d F Y H:i') }}</td>
                        <td>{{ $v['item']->updated_at->format('d F Y H:i') }}</td>
                        <td key-name="action">
                            @if($v['buttons'])
                            {!! $v['buttons'] !!}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table td.data-label a { color:#000; }
    .table td.data-label a i { color:#00558f; }
    .table td.data-label a i { transition:transform linear .1s; }
    .table td.data-label a[data-show=show] i { transform:rotate(-90deg); }
</style>
<script>
    $('.table td.data-label a').click(function() {
        var id = $(this).attr('data-id');
        var show = $(this).attr('data-show');
        
        if (show == 'hide') {
            $('.table tr[data-parent=' + id + ']').show();
            $(this).attr('data-show','show');
        } else {
            $('.table tr[data-parent=' + id + '] td.data-label a[data-show=show]').trigger('click');
            $('.table tr[data-parent=' + id + ']').hide();
            $(this).attr('data-show','hide');
        }
        
        return false;
    });

    $('.btn-delete').click(function () {
        $(this).parent('form').submit();
    })
</script>

@endsection