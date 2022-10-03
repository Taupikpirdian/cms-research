@extends('cactuar::admin.layout-master')

@section('content')

<div class="col-md-12 ">
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title">Translation Listing</h3>
        </div>
        <div class="box-body table-responsive">
            
            <div class="pull-right admin-filter">
                <div>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control" name='search' value="">
                    </div>
                </div>
            </div>
            
            <script>
                $('input[name=search]').keyup(function() {
                    if (!$(this).val()) {
                        $('.table').find('tr').show();
                        $('tr.subtitle').show();
                        $('.cat').hide();
                        return;
                    }
                    
                    var keyword = $(this).val().toLowerCase();
                    
                    $('.table tr').each(function() {
                        if ($(this).find('th').length >= 1)
                            return;
                        var found = false;
                        $(this).find('td').each(function() {
                            if($(this).html().toLowerCase().indexOf(keyword) >= 0)
                                found = true;
                        });
                        
                        if (found == false)
                            $(this).hide();
                        else
                            $(this).show();
                    });
                    
                    $('tr.subtitle').hide();
                    $('.cat').show();
                });
            </script>
            <style>.cat { display:none; }</style>
            
            <table class="table table-hover">
                <tr>
                    <th>Code</th>
                    @foreach (lang::codes() as $v)
                    <th>Translation</th>
                    @endforeach
                    <th width="100px">&nbsp;</th>
                </tr>
                <?php $onCat = ''; ?>
                @foreach ($translateKeys as $baseCode) 
                
                @php
                    $ex = explode('.',$baseCode);
                    $code = array_pop($ex);
                    $cat = implode(' / ', $ex);
                @endphp
                
                @if($cat != $onCat)
                    @php($onCat = $cat)
                    <tr class="subtitle"><td colspan="{{ count(lang::codes()) + 3 }}" style="background:#3c8dbc;font-weight:bold;color:#fff;text-align:center;">@if($cat) {{ str_replace("/",">>",strtoupper($cat)) }} @else GLOBAL @endif</td></tr>
                @endif
                
                <tr>
                    <td>@if($cat)<i class="cat">{{ $cat }} >> </i>@endif {{ $code }}</td>
                    @foreach (lang::codes() as $v)
                    <td>{!! lang::translate($baseCode, [], $v) !!}</td>
                    @endforeach
                    <td key-name="action">
                    @if (Auth::user()->allow('translation', 'edit'))
                        <a class="btn btn-flat bg-blue" href="{{ admin::url('translation/edit?code='.$baseCode) }}"><i class="fa fa-edit"></i> Edit</a>
                    @endif
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>

@endsection