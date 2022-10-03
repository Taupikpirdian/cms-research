@php
$active = admin::module();
foreach(config('cadmin.menu') as $k=>$v) {
    if (array_get($v, 'merge') && $active == $k)
        $active = $v['merge'];
}
@endphp

<aside class="main-sidebar">
    <section class="sidebar">
        
        <ul class="sidebar-menu">
            @foreach ($menu AS $k => $v)
                @if(!is_array(array_get($v, 'child'))) 
                    @if(!array_get($v, 'merge'))
                    <li 
                        @if ($active == $k)
                            class="active"
                        @endif
                        >
                        <a href="{{ admin::url(''.$k . ((array_get($v, 'default-action')) ? '/'.array_get($v, 'default-action') : '')) }}">
                            <i class="fa @if (array_get($v, 'fa')) fa-{!! array_get($v, 'fa') !!} @else fa-list @endif"></i>
                            <span>{!! array_get($v, 'label') !!}</span>
                            @if (array_get($v, 'counter-label'))
                            &nbsp;<small class="label bg-yellow">{{ array_get($v, 'counter-label') }}</small>
                            @endif
                        </a>
                    </li>
                    @endif
                @else
                    <li 
                        @if (in_array($active, array_keys(array_get($v, 'child'))))
                            class="treeview active"
                        @else
                            class="treeview"
                        @endif
                        >
                        <a href="#">
                            <i class="fa @if (array_get($v, 'fa')) fa-{!! array_get($v, 'fa') !!} @else fa-list @endif"></i>
                            <span>{!! array_get($v, 'label') !!}</span>
                            @if (array_get($v, 'counter-label'))
                            &nbsp;<small class="label bg-yellow">{{ array_get($v, 'counter-label') }}</small>
                            @endif
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu" style="padding-bottom:20px;">
                            @foreach (array_get($v, 'child') AS $kk => $vv)
                                @if(!array_get($vv,'merge'))
                                <li @if ($active == $kk) class="active" @endif>
                                    <a href="{{ admin::url(''. $kk . ((array_get($vv, 'default-action')) ? '/'.array_get($vv, 'default-action') : ''))}}">
                                        <i class="fa @if (array_get($vv, 'fa')) fa-{!! array_get($vv, 'fa') !!} @else fa-list @endif"></i>
                                        <span>{!! array_get($vv, 'label') !!}</span>
                                        @if (array_get($vv, 'counter-label'))
                                        &nbsp;<small class="label bg-yellow">{{ array_get($vv, 'counter-label') }}</small>
                                        @endif
                                    </a>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endforeach
            
            @if (config('cadmin.cadmin.documentation-file') && file_exists(config('cadmin.cadmin.documentation-file')))
                <li><a href="{{ url(ADMIN.'/documentation') }}" target="_blank"><i class="fa fa-file-pdf-o"></i> Documentation</a></li>
            @endif 
            
            @if (config('cadmin.cadmin.go-to-web'))
                <li><a href="{{ str_replace('[site]',url(''), config('cadmin.cadmin.go-to-web')) }}" target="_blank"><i class="fa fa-location-arrow"></i> Go to web</a></li>
            @endif

            <li><a href="{{ url()->admin('logout') }}"><i class="fa fa-power-off"></i> Logout</a></li>
        </ul>

        @if (!empty($menuRoot))
            
        @endif
    </section>
</aside>