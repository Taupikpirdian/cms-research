@extends('cactuar::admin.layout-master')

@section('content')
    @include('cactuar::admin.data-listing')
    <link rel='stylesheet' href='{{ asset('bootstrap/multiple-select/bootstrap-multiselect.css')}}'>
    <script src='{{ asset('bootstrap/multiple-select/bootstrap-multiselect.js') }}'></script>
    <script>
    $('.multiselect').multiselect({
        maxHeight: 200,
		disableIfEmpty: true,
        buttonText: function(options, select) {
            if (options.length < 1)
                return 'no item selected';
            
            if (options.length > 1)
                return options.length + ' items selected';
            
            var label = '';
            options.each(function () {
                label = $(this).text();
                
                var optGroup = $(this).parents('optgroup');
                if (optGroup.length > 0)
                    label += ' (' + optGroup.attr('label') + ')';
                return;
            });
            
            return label;
        }
    });        
    </script>
@endsection