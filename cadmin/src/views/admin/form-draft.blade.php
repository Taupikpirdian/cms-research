@if (isset($formValidate) && is_array($formValidate)) 
    <div class="callout callout-danger alert alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>
            @foreach($formValidate AS $e)
                {!! $e !!}<br/>
            @endforeach
    </div>
@endif

<div class='stickys clearfix'>
    <div class="sticky sticky-title btn btn-flat bg-gray">
        <span><i class="fa fa-tag"></i> <em></em></label>
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
            @foreach (lang::codes() as $lang)
                <li><a href=# class='toggle-actor toggle-actor' data-lang="{{$lang}}" toggle-target='{{$lang}}-container' toggle-group='lang'>{{ Config::get('cadmin.lang.labels.'.$lang) }}</a></li>
            @endforeach
        </ul>
    </div>
</div>

<a href="#" class="show-draft btn btn-flat bg-blue"><i class="fa fa-files-o"></i> Show Draft Content</a>
<a href="#" class="show-origin btn btn-flat bg-gray"><i class="fa fa-file-text-o"></i> Show Original Content</a>
<br><br>

{!! $form !!}

<a href="#" class="show-draft btn btn-flat bg-blue"><i class="fa fa-files-o"></i> Show Draft Content</a>
<a href="#" class="show-origin btn btn-flat bg-gray"><i class="fa fa-file-text-o"></i> Show Original Content</a>

<style>
    .form-group[origin^="origin-content-"] { display:none; }
    .widgets[widget-source^="origin-widget-"] { display:none; }
    .show-draft { display:none; }
</style>

<script>
    $('.show-draft').click(function() {
        
        $('.form-group[origin^="origin-content-"]').hide();
        $('.form-group:not([origin^="origin-content-"])').show();
        
        $('.widgets[widget-source^="origin-widget-"]').hide();
        $('.widgets[widget-source^="origin-widget-"] .form-group').show();
        $('.widgets:not([widget-source^="origin-widget-"])').show();
        $('.form-validate input[type="submit"]').show();
        $('.show-draft').hide();
        $('.show-origin').show();
        
        return false;
    });
    
    $('.show-origin').click(function() {
        $('.form-group[origin^="origin-content-"]').show();
        $('.form-group:not([origin^="origin-content-"])').hide();
        
        $('.widgets[widget-source^="origin-widget-"]').show();
        $('.widgets[widget-source^="origin-widget-"] .form-group').show();
        $('.widgets:not([widget-source^="origin-widget-"])').hide();
        $('.form-validate input[type="submit"]').hide();
        $('.show-draft').show();
        $('.show-origin').hide();
        
        return false;
    });
</script>