@extends('cactuar::admin.layout-master')

@section('content')

<div class="col-md-12 ">
    <a href="{{ admin::url('user/create') }}" class="btn btn-app"><i class="fa fa-plus"></i> Create</a>
    <a href="{{ admin::url('user/email-template') }}" class="btn btn-app"><i class="fa fa-envelope"></i> Email Template</a>
</div>

<div class="col-md-12 ">
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title">User Data</h3>
            @include('cactuar::admin.filter')
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <tr>
                    <th>Login Name</th>
                    <th>Display Name</th>
                    <th>Email</th>
                    <th>Privilege</th>
                    <th>Enable</th>
                    <th class="list-date">Create</th>
                    <th class="list-date">Update</th>
                    <th class="list-act">Action</th>
                </tr>
                @foreach ($listing AS $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->display_name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>
                            @if (admin::moduleExists('privilege') && Auth::user()->allow('privilege', 'edit'))
                            <a href="{{ admin::url('privilege/edit?unique='.$item->privilege_id) }}">{{ $item->privilege->label }}</a>
                            @else
                            {{ $item->privilege->label }}
                            @endif
                        </td>
                        <td>
                            @if ($item->is_enable == 'Yes')
                                <span class="label label-success">Yes</span>
                            @else
                                <span class="label label-danger">No</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at }}</td>
                        <td>{{ $item->updated_at }}</td>
                        <td key-name="action">
                            <a href="{{ admin::url('user/edit?unique='.$item->id) }}" class="btn bg-purple btn-flat">
                                <i class="fa fa-edit"></i> Edit</a>
                            <a href="{{ admin::url('user/delete?unique='.$item->id) }}" 
                               class="btn bg-red btn-flat need-confirm" 
                               data-confirm="Are you sure to delete this item?">
                            <i class="fa fa-trash-o"></i> Delete</a>
                            
                            @if (admin::moduleExists('admin-log') && Auth::user()->allow('admin-log', 'index'))
                            <a href="{{ admin::url('admin-log?user_id='.$item->id) }}" class="btn btn-flat bg-green"><i class="fa fa-terminal"></i> Log</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
            
            @include('cactuar::admin.paginate')
        
        </div>
    </div>
</div>

@endsection