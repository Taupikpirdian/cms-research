var xhrListing = [];

$.fn.dataListing = function(path) {
	$(document).ready(function() {
		$(this).addClass('data-listing');
		$(this).html('\
			<div class="data-listing-container">\
				<form class="data-listing-toolboxs">\
					<input type="hidden" name="page" value="1">\
				</form>\
			</div>\
			<div class="data-listing-loading">Loading data...</div>');
		$(this).attr('data-path',path);
		
		$(this).on('call',function() {
			var path = $(this).attr('data-path');
			var url = $url + '/' + path;
			var obj = $(this);
			var data = [];
			
			if (xhrListing[path])
				xhrListing[path].abort();
			
			$(this).find('.data-listing-loading').show();
			
			xhrListing = $.ajax({
				url:url,
				method:'get',
				data:obj.find('form.data-listing-toolboxs').serialize(),
			}).success(function(html) {
				obj.find('.data-listing-container').html(html);
			}).complete(function() {
				obj.find('.data-listing-loading').hide();
			});
		}).trigger('call');
		
		$(this).on('submit','form.data-listing-toolboxs',function() {
			$(this).parents('.data-listing').trigger('call');
		});
		$(this).on('click','paginate a',function() {
			var forms = $(this).parents('.data-listing').find('form.data-listing-toolboxs');
			forms.find('input[name=page]').val($(this).attr('data-page'));
			forms.trigger('submit');
		});
	});
});