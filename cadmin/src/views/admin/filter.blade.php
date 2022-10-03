<style>
    .admin-filter > div {
        display:table-cell;
        padding-left:10px;
    }
    
    .admin-filter input[type=text], .admin-filter select {
        width:120px;
    }
    
    @media screen and (max-width:400px) {
        .admin-filter > div {
            display:block;
            margin-bottom:10px;
        }
        
        .admin-filter {
            margin-top:10px;
        }
    }
</style>

<form method="post" action="{{ admin::url('admin/filter?'.http_build_query(Request::query())) }}" class="pull-right admin-filter">
    <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
    <input type="hidden" name="segments" value="{{ json_encode(Request::segments()) }}">
    
    @if (isset($sort))
        <div>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sort"></i></span>
                <select class="form-control" name='sort'>
                    <option>--default--</option>
                    @foreach ($sort AS $key => $val)
                        <option value="{{ $key }}-asc" @if (Request::query('sort') == $key && Request::query('sortType') == 'asc') selected @endif >{{ $val }} asc</option>
                        <option value="{{ $key }}-desc"@if (Request::query('sort') == $key && Request::query('sortType') == 'desc') selected @endif >{{ $val }} desc</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
    
    <div>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-search"></i></span>
            <input type="text" class="form-control" name='search' value="{{ Request::input('search') }}">
        </div>
    </div>
</form>

<script>
    $('select[name=sort]').change(function() {
        $('.admin-filter').submit();
    })
</script>