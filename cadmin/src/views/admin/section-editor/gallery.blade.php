@extends('cactuar::admin.section-editor.layout')

@section('content')

<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title">Choose your section</h3>
	</div>
	<div class="box-body">
		<ul class="section-gallery">
			@foreach ($config as $k => $v)
				<li data-key="{{ $k }}" data-form="@if (array_get($v,'forms')) 1 @else 0 @endif">{!! array_get($v,'label') !!}</li>
			@endforeach
		</ul>
	</div>
</div>

<script>
	$('.section-gallery li').click(function() {
		if ($(this).attr('data-form') == 1)
			window.opener.sectionEditor.post(window.opener.$url + '/section-editor/editor',{key:$(this).attr('data-key')});
		else
			window.opener.sectionEditor.compile({label:$(this).html()});
	});
</script>

@endsection