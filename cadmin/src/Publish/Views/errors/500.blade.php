@extends('errors/layout-master')

@section('content')
	<h1><i class="fa fa-bug"></i> 500/Internal Sever Error</h1>
	<?php /*<p>
    Uh.. oh... telah terjadi sesuatu yang tidak kita inginkan.<br>
    Tapi hey... tidak perlu panik seperti itu. tetap tenang dan cobalah hubungi Admin kami untuk memeriksa dan (jika diperlukan) memperbaiki masalah ini.<br>
    </p>*/?>

    @if (isset($e) && Auth::check())
        <hr>
            
        <p style="font-size:11px !important;line-height:15px;">
            <b>Error</b> : {{ $e->getMessage() }}<br>
            <b>File</b> : {{ $e->getFile() }}<br>
            <b>Line</b> : {{ $e->getLine() }}<br>
        </p>
    @endif
        
@endsection