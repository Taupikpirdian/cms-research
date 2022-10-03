@extends('errors.layout-master')

@section('content')

<h1><i class="fa fa-warning"></i> Session Expired</h1>
<p>Your session may be expired. Possibly due to too long idle. </p>
<br/>
<a href="{{ url('') }}" class='btn'>Go back</a>
@endsection