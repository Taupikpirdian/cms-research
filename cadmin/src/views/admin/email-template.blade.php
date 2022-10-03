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

<form method="post" class="form-validate" style='max-width:1200px;'>
    <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
    <div class="col-md-12 col-xs-12 ">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{!! array_get($data, 'label') !!} </h3><br><br>
                {!! array_get($data, 'description') !!}
                <p></p>
                
                @if ($res->id)
                <div>
                    <a href="#" class="send-test-email btn btn-flat bg-green"><i class="fa fa-location-arrow"></i> Send test email</a>
                    <script>
                        
                        function validEmail(m) {
                            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(m)) {
                                return true;
                            }
                            return false;
                        }
                        $('.send-test-email').click(function() {
                            var email = prompt('Please enter target email Address. Multiple value, seperate by comma (,)');
                            if (!email)
                                return false;

                            var valid = true;
                            $.each(email.split(','),function(k,v) {
                                if (validEmail(v) != true)
                                    valid = false;
                            });

                            if (valid != true) {
                                alert('Please valid email address. multiple value, seperate by comma (,)');
                                return false;
                            }

                            if (confirm('Send test email. Your unsaved work may be lost, continue?') != true)
                                return false;

                            window.location.href="{{ admin::url(admin::module().'/send-test-email?unique='.$data['unique']) }}&email=" + email;
                            return false; 
                        });
                    </script>
                </div>
                @endif
                
                @if (!empty(array_get($data, 'selectors')))
                    <div class=" pull-right" style="max-width:270px;margin-bottom:20px;">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                            <select name="jumper" class="form-control" style="max-width:200px;">
                                @foreach (array_get($data, 'selectors') as $k => $v) 
                                    <option value="{{ $k }}" @if ($k == $data['unique']) selected @endif>{{ $v }}</option>
                                @endforeach
                            </select>
                            <script>
                                $('select[name=jumper]').change(function() {
                                    if (confirm('Jump to Email Template "' + $(this).find('option:selected').html() + '". Your unsaved work may be lost') != true)
                                        return $(this).val('{{ $data['unique'] }}');
                                    
                                    window.location.href='{{ admin::url(admin::module().'/email-template?unique=') }}' + $(this).val();
                                });
                            </script>
                        </div>
                    </div>
                @endif
            </div>
			
            <div class="box-body">
                
                {!! array_get($data, 'form') !!}
            </div>
		</div>
	</div>
	
    <div class="col-md-12 col-xs-12">
    @if (!$res->id)
	    <input type="submit" class="btn btn-flat bg-blue" value="Save & enable sending email" onClick="return confirm('by clicking it will be enabling email sending from the template?');">
    @else
        <input type="submit" class="btn btn-flat bg-blue" value="Update Data" onClick="return confirm('save data?')">&nbsp;
	@if(array_get($conf,'required') != true)
        <input type="submit" name="delete" class="btn btn-flat bg-red" value="Disable sending Email" onClick="return confirm('disable email sending from the email template?')">
	@endif
    @endif
    </div>
</form>

{!! array_get($data, 'append') !!}

@include('cactuar::admin.component.copy-translation-js')

<script>

    $(document).ready(function() {
        $.validator.addMethod("multiEmail", 
            function(value, element) {
                if (!value)
                    return true;

                var valid = true;
                $.each(value.split(','),function(k,v) {
                    if (validEmail(v) != true)
                        valid = false;
                });

                return valid;
            }, $.validator.format("Please valid email address. multiple value, seperate by comma (,)")
        );
        $.validator.classRuleSettings.multiEmail = { multiEmail: true };
    });
</script>
@endsection
