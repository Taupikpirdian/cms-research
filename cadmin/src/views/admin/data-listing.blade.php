<div class="col-md-12 col-xs-12">
    <div class="box box-primary">
        <div class="box-body table-responsive">
            @if ($download)
            <div class="clearfix">
                <a href="{{ url(implode('/',request()->segments())) }}?{!! http_build_query(request()->except(['_token'])) !!}&download=csv" class="btn btn-flat bg-blue"><i class="fa fa-download"></i> Download Data</a>
            </div>
            @endif
            
            @if (isset($tableTitle))
            <div class="box-header" style="float:left;">
                <h3 class="box-title">{!! $tableTitle !!}</h3>
            </div>
            @endif

            @if (!empty($filters) || !empty($searchs) || !empty($orders) || !empty($ranges))
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
                @if (!empty($orders))
                <div>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-sort"></i></span>
                        <select class="form-control" name='sort'>
                            <option value="">--default--</option>
                            @foreach ($orders AS $key => $val)
                                <option value="{{ $key }}-asc"  @if (request()->get('sort') == $key.'-asc') selected @endif >{{ $val }} asc</option>
                                <option value="{{ $key }}-desc" @if (request()->get('sort') == $key.'-desc') selected @endif >{{ $val }} desc</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if (!empty($filters))
                <div>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-filter"></i></span>
                        <select name='filter[]' multiple=multiple class="form-control multiselect">
                            @foreach ($filters as $k => $v) 
                            <optgroup label="{{ array_get($v, 'label') }}">  
                                @foreach (array_get($v, 'options') as $kk => $vv)
                                <option value="{{ $k }}-{{ $kk }}" @if (is_array(request()->get('filter')) && in_array($k.'-'.$kk, request()->get('filter'))) selected @endif>{{ $vv }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if (!empty($ranges))
                <div>
                    <div class="input-group pull-left">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" class="form-control date-range-picker" name='range' value="{{ request()->get('range') }}" style="font-size:12px;width:150px;" autocomplete=off>
                    </div>
                </div>
                @endif
                @if (!empty($searchs))
                <div>
                    <div class="input-group pull-left">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control" name='search' value="{{ request()->validated('search','string|required') }}">
                    </div>
                </div>
                @endif
                <div>
                    <input type='submit' class='btn btn-flat bg-purple' value='Filter'>
                </div>
            </form>
            @endif
            
            <table class="table table-hover">
                <tr>
                @foreach($heads as $k=>$v)
                    <th origin="{{ $k }}">{!! $v !!}</th>
                @endforeach
                </tr>
                
                @forelse($data as $item)
                <tr>
                    @foreach($item as $k=>$v)
                    <td origin="{{ $k }}">{!! $v !!}</td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ count($heads) }}">No data found</td></tr>
                @endforelse
            </table>
            <br>
            @include('cactuar::admin.paginate', ['listing' => $listings])
        </div>
    </div>
</div>