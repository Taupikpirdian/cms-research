$('.cfind-tools li.cfind-create-folder .fa, .cfind-tools li.cfind-create-file .fa').click(function() {
	var parent = $(this).parent();
	if (parent.attr('data-disable') == '1') return;
	
	var active = parent.attr('data-active');
	$('.cfind-tools li[data-active="true"]').removeAttr('data-active');
	
	if (active != 'true') {
		parent.attr('data-active', 'true');
		parent.find('input[type=text]').select();
	}
});

$('.cfind-tools li.cfind-delete-folder .fa').click(function() {
	var parent = $(this).parent();
	if (parent.attr('data-disable') == '1') return;
	
	if (!confirm('Are you sure to remove these folder ?')) return false;
	
	$('.cfind-tools li.cfind-delete-folder > div > form input[type=submit]').trigger('click');
});

$('.cfind-item-file[data-is-image="true"]').each(function() {
	$(this).find('figure').css('background-image', 'url(\'' + $(this).attr('data-url') + '\')');
});

$('.cfind-item-file').click(function() {
	if ($(this).attr('data-type') == 'folder') return true;
	if (!$(this).attr('data-name')) return true;
	$('.cfind-detail > div').show();
	
	//set selector
	$('.cfind-item-file[data-active="true"]').removeAttr('data-active');
	$(this).attr('data-active', 'true');
	
	//change property of detail
	$('.cfind-detail > div').show();
	$('.cfind-detail > div').find('h3').html($(this).attr('data-name'));
	$('.cfind-detail > div').find('figure').css('background-image', $(this).find('figure').css('background-image'));
	$('.cfind-detail > div').find('p').html('Size : ' + $(this).attr('data-size') + '<br/>Create : ' + $(this).attr('data-ctime') + '<br/>Modified : ' + $(this).attr('data-mtime') + '');
	
	$('.cfind-detail').attr('data-path', $(this).attr('data-path'));
	$('.cfind-detail').attr('data-url', $(this).attr('data-url'));
	
	var that = $(this);
    
    $.each(['path','url','size','mtime','ctime','image-width','image-height', 'type'],function(k,v) {
        $('.cfind-detail').attr('data-' + v,that.attr('data-' + v)); 
    });
	
	if ($(this).attr('data-image-width')) {
		$('.cfind-detail > div').find('p').append('<br/>Width : ' + $(this).attr('data-image-width') + 'px');
	}
	if ($(this).attr('data-image-height')) {
		$('.cfind-detail > div').find('p').append('<br/>Height : ' + $(this).attr('data-image-height') + 'px');
	}
	
	$('.cfind-detail > div').find('.cfind-use').attr('data-url', $(this).attr('data-url'));
	$('.cfind-detail > div').find('.cfind-delete-file-container input[name="path"]').val($(this).attr('data-path-enc'));
	$('input[name=source]').val($(this).attr('data-path'));
    
	return false;
}); /*.dblclick(function() {
	ckeditor($(this).attr('data-url'));
});	*/

$('.cfind-detail .remove').click(function() {
	if (!$('.cfind-detail > div').find('.cfind-delete-file-container input[name="path"]').val()) return false;
	if (!confirm('Are you sure to remove [' + $('.cfind-detail > div h3').html() + '] ?')) return false;
	
	$('.cfind-detail > div .cfind-delete-file-container > form input[type=submit]').trigger('click');
	
	return false;
});

$('.cfind-use-ckeditor').click(function() {
	var detail = $('.cfind-detail');
	ckeditor(detail.attr('data-url'));
});

$('.cfind-use-cfind').click(function() {
	var detail = $('.cfind-detail');
	
    var json = {};
    $.each(['path','size','mtime','ctime','image-width','image-height','type'],function(k,v) {
       json[v] = detail.attr('data-' + v); 
    });
    
    var str = JSON.stringify(json);
    
    window.opener.cfind.set(str);
	window.close();
});

function ckeditor(url) {
	if (typeof(window.opener.CKEDITOR) == 'undefined') return;
	
	function getUrlParam( paramName ) {
		var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' ) ;
		var match = window.location.search.match(reParam) ;

		return ( match && match.length > 1 ) ? match[ 1 ] : null ;
	}
	
	var funcNum = getUrlParam( 'CKEditorFuncNum' );
	var fileUrl = url;
	window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
	window.close();
	
	return false;
}



$('.cfind-use-crop').click(function() {
    $('.cfind-select-area').find('img.selection-area').html('');
    var url = $('.cfind-detail').attr('data-url');
    var img = new Image();
    
    img.onload = function(src)
    {
        $('.cfind-main').hide();
        $('.cfind-select-area').show();
        $('.cfind-select-area input[name=width], .cfind-select-area input[name=height]').val(0);
        
        $('.cfind-select-area .selection-area').html(img);    
        $('.cfind-select-area .selection-area img').imgAreaSelect({
            aspectRatio:$('.cfind-use-crop').attr('crop-ratio'),
            imageHeight:$('.cfind-item-file[data-active=true]').attr('data-image-height'),
            imageWidth:$('.cfind-item-file[data-active=true]').attr('data-image-width'),
            outerColor:'#f00',
            outerOpacity:.7,
            onSelectEnd: function (img, selection) {
                $('.cfind-select-area input[name=width]').val(selection.width);
                $('.cfind-select-area input[name=height]').val(selection.height);
                
                $('.cfind-select-area input[name=x1]').val(selection.x1);
                $('.cfind-select-area input[name=y1]').val(selection.y1);
                
                if (selection.width > 0 && selection.height > 0) 
                    $('.cfind-select-area a.selection-area-confirm').show();
                else
                    $('.cfind-select-area a.selection-area-confirm').hide();
            }
        });
    }
    
    img.src = url;
});

$('.cfind-select-area .selection-area-back').click(function() {
    $('.cfind-main').show();
    $('.cfind-select-area').hide();
    $('div[style^="position: absolute"]').remove();
    
    return false;
});

$('.cfind-select-area .selection-area-confirm').click(function() {
    var form = $('.cfind-select-area form');
    var toWidth = parseInt(form.find('input[name=toWidth]').val());
    var baseWidth = parseInt(form.find('input[name=width]').val());
    var toHeight = parseInt(form.find('input[name=toHeight]').val());
    var baseHeight = parseInt(form.find('input[name=height]').val());
    
    if (baseWidth < 1 || baseHeight < 1)
        return;
    
    if (
        (toWidth > baseWidth || toHeight > baseHeight)
        && !confirm('Selected size is smaller than recommended size. Continue?')
        ) {
        return;
    }
    
    form.trigger('submit');
});

$('.cfind-use-auto-crop').click(function() {
    var toWidth = parseInt($(this).attr('crop-width'));
    var baseWidth = parseInt($('.cfind-item-file[data-active=true]').attr('data-image-width'));
    var toHeight = parseInt($(this).attr('crop-height'));
    var baseHeight = parseInt($('.cfind-item-file[data-active=true]').attr('data-image-height'));
    
    if (
        (toWidth > baseWidth || toHeight > baseHeight)
        && !confirm('Original file is smaller than recommended size. Continue?')
        ) {
        return;
    }
    
    $('form#auto_crop').trigger('submit');
});

/* ADDITIONAL */

$('.cfind-tools > .cfind-div:before').click(function() {
	$('.cfind-tools form').hide();
});