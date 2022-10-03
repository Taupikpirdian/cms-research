@if (isset($formValidate) && is_array($formValidate))
    <div class="callout callout-danger alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>
            @foreach($formValidate AS $e)
                {!! $e !!}<br/>
            @endforeach
        </p>
    </div>
@endif

<div class='stickys clearfix'>
    <div class="sticky sticky-title btn btn-flat bg-gray">
        <span><i class="fa fa-tag"></i> <em></em></span>
    </div>
    <div class="sticky btn btn-flat bg-yellow copy-translation">
        <span>
            <span class="copy-text"><i class="fa fa-copy"></i> <span><em>Copy</em></span></span>
            <span class="copied" style="display:none"><i class="fa fa-check"></i> Copied</span>
        </span>
    </div>
    <div class="sticky sticky-lang btn btn-flat bg-purple">
        <span><i class="fa fa-language"></i> <em></em></span>
        <ul>
            @foreach (Config::get('cadmin.lang.codes') as $lang)
                <li><a href=# class='toggle-actor toggle-actor' data-lang="{{$lang}}" toggle-target='{{$lang}}-container' toggle-group='lang'>{{ Config::get('cadmin.lang.labels.'.$lang) }}</a></li>
            @endforeach
        </ul>
    </div>
</div>

{!! $form !!}
