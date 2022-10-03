<?php
$offline = false;

try {
	$webConfig = \Cactuar\Admin\Models\Conf::initial('site-setting');
	if (!$webConfig) {
		$offline = true;
	} else {
		$offline = $webConfig->status == 'offline' ? true : false;
	}
} catch(\Exception $e) {}

?>

@if($offline == true)
@include('errors.offline')
@php(die())
@else
@extends('errors.layout-master')

@section('content')

<h1><i class="fa fa-map-o"></i> 404/Page not found</h1>
<h3>Well, you finally found it!</h3>
<p>
    Looks like the page you're trying to visit doesn't exists.<br>
    Please check the URL and try your luck again.
</p>
<br/>
<a href="{{ url('') }}" class='btn'>Take me home</a>

@endsection
@endif