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

<div class="col-md-12 ">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{!! array_get($data, 'label') !!}</h3>
        </div>
        <div class="box-body"> 
            
            @foreach (array_get($data,'subMenu') as $v) 
            <a href="{{ array_get($v, 'url') }}" class="btn btn-flat bg-blue"><i class="fa fa-{{ array_get($v, 'fa') }}"></i> {!! array_get($v, 'label') !!}</a>
            @endforeach
            
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

                    @if (is_array(array_get($data, 'order')) && array_get($data, 'order') != [])
                        <div>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-sort"></i></span>
                                <select class="form-control" name='sort'>
                                    <option>--default--</option>
                                    @foreach (array_get($data, 'order') AS $key => $val)
                                        <option value="{{ $key }}-asc" @if (Request::query('sort') == $key && Request::query('sortType') == 'asc') selected @endif >{{ $val }} asc</option>
                                        <option value="{{ $key }}-desc"@if (Request::query('sort') == $key && Request::query('sortType') == 'desc') selected @endif >{{ $val }} desc</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    
                    @if (is_array(array_get($data, 'filter')) && array_get($data, 'filter') != [])
                    <div>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-filter"></i></span>
                            <select name='filter[]' multiple=multiple class="form-control multiselect">
                                @foreach (array_get($data, 'filter') as $k => $v) 
                                <optgroup label="{{ array_get($v, 'label') }}">  
                                    @foreach (array_get($v, 'options') as $kk => $vv)
                                    <option value="{{ $k }}-{{ $kk }}" @if (is_array(Request::query('filter')) && in_array($k.'-'.$kk, Request::query('filter'))) selected @endif>{{ $vv }}</option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    @if (array_get($data, 'search') == true)
                        <div>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" name='search' value="{{ Request::input('search') }}">
                            </div>
                        </div>
                    @endif

                    @if (array_get($data, 'range') == true)
                        <div>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" class="form-control date-range-picker" name='range' value="{{ Request::input('range') }}" style="font-size:12px;width:150px;">
                            </div>
                        </div>
                    @endif
                    
                    @if (array_get($data, 'search') == true 
                        || array_get($data, 'range') == true
                        || (is_array(array_get($data, 'filter')) && array_get($data, 'filter') != [])
                        || (is_array(array_get($data, 'order')) && array_get($data, 'order') != [])
                        )
                    <div>
                        <input type='submit' class='btn btn-flat bg-purple btn-midh' value='Filter'>
                    </div>
                    @endif
                </form>
                <table class="table table-hover">
                    <tr>
                        @if (is_array(array_get($data, 'bulkAction')) && array_get($data, 'bulkAction') != [] && !empty(array_get($data, 'data')))
                        <th style="width:20px;">
                            {!! Form::checkbox('', null, false, ['class' => 'minimal check-all', 'target' => '.bulk-check']) !!}
                            
                        </th>
                        @endif
                        @foreach (array_get($data, 'head') AS $k => $v)
                            <th key-name='{{ $k }}'>{!! $v !!}</th>
                        @endforeach
                    </tr>

                    @forelse (array_get($data, 'data') AS $row)
                        <tr>
                            @if (is_array(array_get($data, 'bulkAction')) && array_get($data, 'bulkAction') != [] && !empty(array_get($data, 'data')))
                            <td>
                                {!! Form::checkbox('checkAll', array_get($row, 'uniqid'), false, ['class' => 'bulk-check minimal']) !!}
                            </td>
                            @endif
                            @foreach ($row AS $k => $v)
                                @if ($k != 'uniqid')
                                    <td key-name='{{ $k }}'>{!! $v !!}</td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count(array_get($data, 'head')) }}" style="background-color:#aff;"><em>---- Empty data</em></td>
                        </tr>
                    @endforelse
                </table>
                
                @include('cactuar::admin.paginate', ['listing' => array_get($data, 'listing')])
                
                @if (is_array(array_get($data, 'bulkAction')) && array_get($data, 'bulkAction') != [] && !empty(array_get($data, 'data')))
                <div class="col-md-12">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-check-square-o"></i></span>
                        <select name='bulk-action' class='form-control' style='max-width:200px;'>
                            <option value=''>-- with selected</option>
                            @foreach (array_get($data, 'bulkAction') as $k => $v)
                                <option value="{!! array_get($v, 'target') !!}" prompt="{{ array_get($v, 'prompt') }}">{{ array_get($v, 'label') }}</option>
                            @endforeach
                        </select>
                        {{--{!! Form::select('bulk-action', ['' => '-- with selected'] + array_get($data, 'bulkAction'), null, ['class' => 'form-control', 'style' => 'max-width:200px;']) !!}--}}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<link rel='stylesheet' href='{{ asset('bootstrap/multiple-select/bootstrap-multiselect.css')}}'>
<script src='{{ asset('bootstrap/multiple-select/bootstrap-multiselect.js') }}'></script>
<script>
    $('.multiselect').multiselect({
        maxHeight: 200,
		disableIfEmpty: true,
        buttonText: function(options, select) {
            if (options.length < 1)
                return 'no item selected';
            
            if (options.length > 1)
                return options.length + ' items selected';
            
            var label = '';
            options.each(function () {
                label = $(this).text();
                
                var optGroup = $(this).parents('optgroup');
                if (optGroup.length > 0)
                    label += ' (' + optGroup.attr('label') + ')';
                
                return;
            });
            
            return label;
        }
    });
    
    $('select[name=bulk-action]').change(function() {
        if ($('input[type=checkbox].bulk-check:checked').length < 1) {
            $(this).val('');
            alert('Please select at least one item');
            return false;
        }
        
        var prompt = $(this).find('option:selected').attr('prompt');
        
        if (prompt && !confirm(prompt)) {
            $(this).val('');
            return false;
        }
        
        var selected = [];
        $('input[type=checkbox].bulk-check:checked').each(function() {
            selected.push('unique[]=' + $(this).val()); 
        });
        
        var url = $(this).val();
        if (url.indexOf('?') < 0) 
            url += '?';
        
        url += selected.join('&');
        window.location.href = url;
    });
</script>

{!! array_get($data, 'append') !!}

@endsection