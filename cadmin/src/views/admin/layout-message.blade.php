@if(isset($adminWarning))
<div class="callout callout-warning alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <p>{!! $adminWarning !!}</p>
    </div>
@endif

@if (Session::get('success'))
    <div class="callout callout-success alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>{!! Session::get('success') !!}</p>
    </div>
@endif

@if (Session::get('error'))
    <div class="callout callout-danger alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>{!! Session::get('error') !!}</p>
    </div>
@endif

@if (count($errors) > 0)
<div class="callout callout-danger alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>
            @foreach($errors->all() AS $e)
                {!! $e !!}<br/>
            @endforeach
        </p>
    </div>
@endif


@if (isset($errc))
    <div class="callout callout-danger alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>
            @foreach($errc AS $e)
                {!! $e !!}<br/>
            @endforeach
        </p>
    </div>
@endif

@if (isset($warningc)) 
    <div class="callout callout-warning alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>
            @foreach($warningc AS $e)
                {!! $e !!}<br/>
            @endforeach
        </p>
    </div>
@endif

