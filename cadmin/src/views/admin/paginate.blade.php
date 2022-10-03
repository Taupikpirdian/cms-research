<div class="clearfix"></div>
<div class="col-md-12">
    <div class="box-footer">
        <span class="text-light-blue">Found <b>{{ $listing->total() }}</b> entries</span>
        <ul class="pagination pagination-sm no-margin pull-right">
             {!! $listing->appends(Request::query())->render() !!}
        </ul>
    </div>
</div>