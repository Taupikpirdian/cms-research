
<style>
    .data-listing-xhr { position:relative; clear:both; }
    .data-listing-xhr .xhr-loading { position:absolute; left:25px; top:40px; font-weight:bold; font-style:italic; }
    .data-listing-xhr .table-responsive { padding:0px; }
    .data-listing-xhr > div { /*display:table-cell;*/ }
</style>

<link rel='stylesheet' href='{{ asset('bootstrap/multiple-select/bootstrap-multiselect.css')}}'>
<script src='{{ asset('bootstrap/multiple-select/bootstrap-multiselect.js') }}'></script>
<script>

var xhrResources = [];

var dataListingXhr = function(obj,url)
{
    if (!url)
        url = obj.attr('data-url');
    
    if (xhrResources[url])
        xhrResources[url].abort();
    
    var post = [];
    if (obj.find('.admin-filter').length > 0)
        post = obj.find('.admin-filter').serialize();
    
    obj.find('.xhr-loading').show();
    //return;
    xhrResources[url] = $.ajax({
        url:url,
        method:'get',
        data:post
    }).success(function(html) {
        obj.find('.xhr-html').html(html);
        obj.find('.multiselect').multiselect({
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
        obj.find('.date-range-picker').daterangepicker({ "opens": "left"});
    }).complete(function() {
        obj.find('.xhr-loading').hide();
    });
};
  
$('.data-listing-xhr').each(function() {
    $(this).html('<div class="xhr-html"></div><div class="xhr-loading">Loading data......</div>')
    dataListingXhr($(this));
});
$('.data-listing-xhr').on('submit','.admin-filter',function() {
    dataListingXhr($(this).parents('.data-listing-xhr')); 
    return false;
});
$('.data-listing-xhr').on('click','.pagination a',function() {
    var obj = $(this).parents('.data-listing-xhr');
    dataListingXhr(obj,$(this).attr('href'));
    return false;
});
    
</script>