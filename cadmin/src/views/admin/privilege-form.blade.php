@extends('cactuar::admin.layout-master')

@section('content')

@php

$acts = [];
if ($type == 'detail') {
    foreach ($data->modules AS $mod) {
        array_push($acts, $mod->module.':'.$mod->act);
    }
}

$mod = [];
foreach ($module AS $k => $v) {
    if (!is_array(array_get($v, 'child'))) {
        if (is_array(array_get($v, 'permission'))) {
            $mod[$k] = $v + ['child' => [$k => $v]];
        } else
            continue;
    }

    if (is_array(array_get($v, 'child'))) {
        foreach (array_get($v, 'child') AS $kk => $vv) {
            if(is_array(array_get($vv, 'permission'))) {
                if (!array_key_exists($k, $mod)) {
                    $mod[$k] = $v;
                    $mod[$k]['child'] = [];
                }
                $mod[$k]['child'][$kk] = $vv;
            }
        }
    }
}

@endphp
<div class="col-md-12 ">
    @if ($type == 'detail') 
        <a href="{{ admin::url('privilege/create') }}" class="btn btn-app"><i class="fa fa-plus"></i> Create</a>
    @endif

    <a href="{{ admin::url('privilege') }}" class="btn btn-app"><i class="fa fa-list"></i> Back to list</a>
</div>

<form method="post" class="form-validate">
    <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
    <div class="col-md-6 col-xs-12 ">
        <div class="box box-primary">
            <!-- <div class="box-header">
                <h3 class="box-title"></h3>
            </div> -->
            <div class="box-body">
                <div class="form-group">
                    <label>Label</label>
                    <input type='text' name='label' value="{{ $data->label }}" class="form-control required">
                </div>
                
                <div class="form-group">
                    <div>
                        @foreach ($mod AS $k => $v)
                            <div class="privilege-list">
                                <div class='parent'>
                                    <h3>{!! array_get($v, 'label') !!} <span><a href=# class='check'></a>  <a href=# class='uncheck checked'></a></span>
                                    </h3>
                                    @foreach (array_get($v, 'child') AS $kk => $vv)
                                        <div class='module'>
                                            <h4>{!! array_get($vv, 'label') !!}</h4>
                                            <ul>
                                                @foreach (array_get($vv, 'permission') AS $p)
                                                    @if (in_array($p, ['order-up','order-down']))
                                                        @continue
                                                    @endif
                                                    @if (is_array(array_get($vv, 'public-permission')) && in_array($p,$vv['public-permission']))
                                                        @continue;
                                                    @endif
                                                    <li>
                                                        <label>
                                                            <input type="checkbox" class="minimal" name="act[]" value="{{ $kk }}:{{ $p }}"
                                                            @if (in_array($kk.':'.$p, $acts)) checked @endif
                                                               >
                                                            {{ ucwords(str_replace('-',' ',$p)) }}
                                                        </label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="col-md-12 col-xs-12">
        @if ($type == 'detail')
            <input type="submit" class="btn btn-primary" value="Update">
        @else
            <input type="submit" class="btn btn-primary" value="Save">
        @endif
    </div>
</form>

<script>
    // $('.privilege-list .parent input[type=checkbox]').each(function(){
    //     if (!$(this).is(':checked')){
    //         $(this).parents('.parent').find('a.check').addClass('checked');
    //     }else{
    //         $(this).parents('.parent').find('a.uncheck').removeClass('checked');
    //     }
    // });


    $('.privilege-list a.check').click(function() {
        var link = $(this);
        $(this).parents('.parent').find('input[type=checkbox]').each(function() {
            if (!$(this).is(':checked')){
                $(this).parent().trigger('click');
                link.parent().find('a').removeClass('checked');
                link.addClass('checked');
            }
            
        });
        return false;
    });
    
    $('.privilege-list a.uncheck').click(function() {
        var link = $(this);
        $(this).parents('.parent').find('input[type=checkbox]').each(function() {
            if ($(this).is(':checked')){
                $(this).parent().trigger('click');
                link.parent().find('a').removeClass('checked');
                link.addClass('checked');
            }
            
        });
        return false;
    });
</script>

@endsection