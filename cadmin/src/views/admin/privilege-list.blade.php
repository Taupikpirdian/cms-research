@extends('cactuar::admin.layout-master')

@section('content')

<div class="col-md-12 ">
    <a href="{{ admin::url('privilege/create') }}" class="btn btn-app"><i class="fa fa-plus"></i> Create</a>
</div>

<div class="col-md-12 ">
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title">Privilege Data</h3>
            @include('cactuar::admin.filter')
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <tr>
                    <th>Label</th>
                    <th>User count</th>
                    <th class="list-date">Create</th>
                    <th class="list-date">Update</th>
                    <th class="list-act">Action</th>
                </tr>
                @foreach ($listing AS $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td>{{ $item->users->count() }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td>{{ $item->updated_at }}</td>
                        <td key-name="action"><a href="{{ admin::url('privilege/edit?unique='.$item->id) }}" class="btn bg-purple btn-flat">
                            <i class="fa fa-edit"></i> Edit</a>
                            @if ($item->delAble)
                                <a href="{{ admin::url('privilege/delete?unique='.$item->id) }}" 
                                   class="btn bg-red btn-flat need-confirm" 
                                   data-confirm="Are you sure to delete this item?">
                                <i class="fa fa-trash-o"></i> Delete</a>
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