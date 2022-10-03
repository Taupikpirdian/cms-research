/* ============== VALIDATOR ======================================================== */

/* :example
<input class="form-control required unique" data-table='product' data-field='name' data-not="1">
*/

function escape_HTML(html_str) {
    if (!html_str)
        return '';
    'use strict';

    return html_str.replace(/[&<>"]/g, function (tag) {
		var chars_to_replace = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '"'
        };

		return chars_to_replace[tag] || tag;
	});
}

$.validator.addMethod("unique",
    function(value, element) {
        var result = false;

        var table = $(element).attr('data-table');
        var field = $(element).attr('data-field');
        var not = $(element).attr('data-not');
		var module = $(element).attr('data-module');

        if (!table || !field) { return true; }

        var post = {table:table,field:field,value:value,not:not,module:module, _token:$token};

        if ($(element).attr('data-foreign')) {
            post.foreign = $(element).attr('data-foreign');
            post.foreignID = $($(element).attr('data-foreign-target')).val();
        }

        $.ajax({
            type:"POST",
            async: false,
            url: $url + '/validate/unique', // script to validate in server side
            data: post,
            success: function(data) {
                result = ($.trim(data) == 'true') ? true : false;
            }
        });

        return result;
    }, "This value is already taken! Try another."
);

$.validator.classRuleSettings.unique = { unique: true };

$.validator.addMethod('uniqueAll',
	function(value, element) {
		var by = $(element).attr('data-unique-by');
		var that = $(element);
		var unique = false;
		if (!that.val())
			return true;

		$(by).each(function() {
			if ($(this).attr('name') == that.attr('name'))
				return;

			if ($(this).val() && $(this).val() == that.val())
				unique = true;
		});

		return unique == false;
	}, 'This value is already taken! Try another.'
);

$.validator.classRuleSettings.uniqueAll = { uniqueAll:true };

/* =============== CFIND ========================================================== */

var cfind = [];

cfind.url = $url;

cfind.afterSet = function(obj, json) {
    var cfind = {};
    if (json)
        cfind = JSON.parse(json);
	obj.parent().find('.cfind-tool').remove();

	if (obj.val()) {
        obj.removeClass('empty');
        obj.attr('ext',cfind.type);
		if (obj.attr('cfind-type') == 'image') {
			this.setImage(obj, cfind.path);
		}
		var tool = $('<div class="cfind-tool"></div>');
        tool.append('<i>filename : ' + cfind.path.split('/').reverse()[0] + '</i>');
		if (!obj.hasClass('required') && !obj.attr('cfind-disable')) {
			tool.append('<a href=# class="cfind-clear fa fa-trash-o"></a>');
		}
		tool.append('<a href=# class="cfind-download fa fa-download"></a>');

        if (obj.attr('cfind-alt')) {

			tool.append('<div class="cfind-alt-container"></div>');
            tool.find('.cfind-alt-container').append('<i class="fa fa-info"></i>');

			$('.sticky-lang ul li a').each(function() {
				var lang = $(this).attr('toggle-target').replace('-container','');

				if (obj.attr('cfind-disable')) {
					var alt = $('<span style="display:block;margin-left:20px;" class="toggle-target lang-target-container" toggle-target="' + lang + '-container">' + escape_HTML(cfind['alt_' + lang]) + '</span>');
				} else {
					var alt = $('<input type="text"  name="alt_' + lang + '" class="cfind-alt toggle-target lang-target-container" info="alt text" toggle-target="' + lang + '-container">');
					alt.val(cfind['alt_' + lang]);
				}
				if ($(this).hasClass('active'))
					alt.css('display','block');
				else
					alt.css('display','none');
				tool.find('.cfind-alt-container').append(alt);
			});
        }

		obj.parent().append(tool);
	} else {
		obj.addClass('empty');
        obj.removeAttr('style');
	}

    if (cfind && obj.attr('cfind-type') == 'image' &&
       (cfind['image-width'] < 300 && cfind['image-height'] < 110)) {
        obj.css('background-size','auto');
    } else {
        obj.css('background-size','contain');
    }
}

cfind.set = function(cfind) {
    this.obj.val(cfind);
    this.obj.parent().parent().find('.cfind-alt').val('');
	this.afterSet(this.obj,cfind);
}

cfind.setImage = function(obj, path) {
    obj.css('background-image', 'url("' + $mediaPath + '/' + path + '")');
}

cfind.reset = function()
{
    $('input[type=text].cfind').each(function() {
        $(this).attr('readonly', 'readonly');
		cfind.afterSet($(this), $(this).val());
    });
}

cfind.reset();

$('body').on('click', 'input[type=text].cfind', function() {
    if ($(this).attr('cfind-disable')) return false;

    cfind.obj = $(this);

    type = $(this).attr('cfind-type');
    if (!type) type = 'image';

	var url = cfind.url + '/' + type + '?opener=cfind';

	if ($(this).attr('cfind-ext'))
		url += '&ext=' + $(this).attr('cfind-ext');

	if ($(this).attr('cfind-thumb'))
        url += '&thumb=' + $(this).attr('cfind-thumb');
	if ($(this).attr('cfind-convert'))
		url += '&convert=' + $(this).attr('cfind-convert');

    window.open(url, 'Cfind', 'toolbar=0,status=0,width=1200,height=600');
    return false;
});

$('body').on('click', 'a.cfind-clear', function() {
	var obj = $(this).parent().parent().find('.cfind');
	obj.each(function() {
		$(this).val('');
	});

	cfind.afterSet(obj);

	return false;
});

$('body').on('click', 'a.cfind-download', function() {
	var obj = $(this).parent().parent().find('.cfind');
	if (!obj.val()) {
		$(this).hide();
		return false;
	}

    var cfind = JSON.parse(obj.val());
	var url = $mediaPath + '/' + cfind.path;

	window.open(url, '_blank');

	return false;
});

$('body').on('change','input.cfind-alt',function() {
    var obj = $(this).parent().parent().parent().find('.cfind');

    var json = {};
    if (obj.val())
        json = JSON.parse(obj.val());

    json[$(this).attr('name')] = $(this).val();
    obj.val(JSON.stringify(json));
});


/* ========================= BULK CHECK ============================== */

$('input.check-all').on('ifChecked', function() {
    var target = $(this).attr('target');

    $(target).each(function() {
        if (!$(this).is(':checked')) {
            $(this).iCheck('check');
        }
    });
});

$('input.check-all').on('ifUnchecked', function() {
    var target = $(this).attr('target');

    $(target).each(function() {
        if ($(this).is(':checked')) {
            $(this).iCheck('uncheck');
        }
    });
});

$('.table-responsive .bulk-check').on('ifChecked', function() {
    $(this).parents('tr').addClass('selected');
});

$('.table-responsive .bulk-check').on('ifUnchecked', function() {
    $(this).parents('tr').removeClass('selected');
});

/* ======================= ETC ============================ */

$(document).on('click', '.need-confirm', function() {
    var msg = $(this).attr('data-confirm');
    if (!msg) msg = 'Are you sure to continue?';

    return confirm(msg);
});


/* ======================== FORM INJECT VALIDATION ====================================== */

$('.form-validate').each(function() {
	$(this).validate({
		ignore:'.ignore',
		invalidHandler: function(form, validator) {
			$.each(validator.errorList, function(k, v) {
				var container = $(this.element).parents('.toggle-target');
				if (container.attr('toggle-target')) { //if inside toggle target, show container
					var actor = $('.toggle-actor[toggle-target=' + container.attr('toggle-target') + ']');
					if (actor.is('[toggle-group]')) {
						actor.trigger('click');
						return false;
					}

					if ($(this.element).is(':hidden')) {
						actor.trigger('click');
						return false;
					}
				}
			});
		},
		submitHandler: function() {
			$('.numeric').each(function() {
				if (!$(this).hasClass('numeric-formated'))
					return;
				$(this).val(string2num($(this).val()));
			});

			return true;
		}
	});

	$.extend($(this).validate().settings, {
		onkeyup: false,
		onfocusout: false
	});
});



/* ============================== CKEDITOR ============================= */

var removeUrlHash = function()
{
	var loc = window.location.href,
    index = loc.indexOf('#');

	if (index > 0) {
		history.pushState("", document.title, loc.substring(0, index));
	}
}

var ckeditorReset = function()
{
	removeUrlHash();

	$('form .my-ckeditor').each(function() {
		var html = $($(this).get(0).outerHTML);
        html.val($(this).val());
        var parent = $(this).parents('.ckeditor-container');
		parent.html('');
		parent.append(html);
	});


	$('form .my-ckeditor').each(function() {
		var id = $(this).attr('id');
		if (!$(this).attr('id')) {
			id = 'ckeditor_' + uniqid();
			$(this).attr('id', id);
		}

		setTimeout(function() {
			$('#' + id).ckeditor();
		}, 100);
	});
}

$('input[type=submit]').click(function(){
	removeUrlHash();

    if (typeof(CKEDITOR) !== "undefined") {
        for(instanceName in CKEDITOR.instances ) {
            CKEDITOR.instances[ instanceName ].updateElement() ;
        }
    }

    $('textarea.ckeditor').each(function() {
        if ($(this).val().replace(/(<([^>]+)>)/ig,"") == '')
            $(this).val('');
    });
});


/* ======================== RUN JS ================================= */

var loc = window.location.href,
    index = loc.indexOf('#');

if (index > 0) {
  window.location = loc.substring(0, index);
}

function formLoad()
{
	if ($('.lang-target-container').length < 1 || $('.sticky-lang').find('.toggle-actor').length <= 1) {
		$('.sticky-lang').hide();
        $('.copy-translation').hide();
	} else {
		$('.sticky-lang').show();
        $('.copy-translation').show();
	}

	$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    });

    $('.datepicker').each(function() {
        if ($(this).val() == '0000-00-00')
            $(this).val('');
    });

    $('form .datepicker').datepicker({ format:'yyyy-mm-dd', autoclose:true });
    $('form .select2').select2();
    $('form .numeric').numeric();

    $('form .date-range-picker').daterangepicker({ "opens": "left"});
    $('form .colorpicker').colorpicker();
    $('form .simple-editor').simpleEditor();
}

$(document).ready(function() {
	formLoad();
	ckeditorReset();
});

$('.sticky-lang a.toggle-actor').on('set', function() {
	if (!$(this).hasClass('active'))
		return;
	var lang = $(this).html();
	$('.lang-target-container[toggle-target=' + $(this).attr('toggle-target') + ']').each(function() {
		var labelContainer = $(this).parent().parent().find('> label.control-label');
		if (labelContainer.find('span.info.info-lang').length < 1)
			labelContainer.append('<br/><span class="info info-lang"></span>');

		if ($('.sticky-lang').find('.toggle-actor').length > 1)
			labelContainer.find('> span.info.info-lang').html('( ' + lang + ' )');

		$('.sticky-lang > span > em').html(lang);
	});

	$('.sticky-lang').find('a.toggle-actor').removeClass('active');
	$(this).addClass('active');

	$('.sticky-lang').css('width', $('.sticky-lang > ul').width() + 'px');
}).on('click', function() {
	$(this).trigger('set');
}).trigger('set');


/* ============== STICKYS ON FORM ========================== */

$('.sticky-lang > span').click(function() {
	$(this).parent().find('ul').show();
});

$('.sticky-lang').mouseleave(function() {
	$(this).find('ul').hide();
});

$('.sticky-title').on('set', function() {
	var html = striptags($('.box-title').html());

	var subtitle = '';
	$('.form-subtitle').each(function() {
		if (!$(this).is(':visible'))
			return;
		var offsetTop = $(this).offset().top;
		if ($(window).scrollTop() >= offsetTop) {
			subtitle = striptags($(this).html());
		}
	});

	if (subtitle)
		html += ' / ' + subtitle;

	$(this).find('span > em').html(html);
}).trigger('set');

$(window).load(function() {
	var offsetTopLang = $('.stickys').offset() ? $('.stickys').offset().top : 0;

	function stickys()
    {
		if ($(window).scrollTop() >= offsetTopLang) {
            $('.stickys').parents('.box-body').addClass('scrolled');
			$('.stickys').css('width', $('.box-body').width() + 20 + 'px');
        } else {
            $('.stickys').parents('.box-body').removeClass('scrolled');
			$('.stickys').css('width', '100%');
        }

		$('.sticky-title').trigger('set');
    }

    $(window).scroll(function() {
        stickys();
    });
});

/* ============== FORMS ============== */

$(document).ready(function() {

	/* ============== WIDGETS ============== */	
	
	function widgetAdd(obj)
	{
        var source = obj.attr('widget-source');
        var el = $('.widget-source[widget-source="' + source + '"]').clone();

        obj.find('> .widget-container').append('<div class="widget-row">' + el.html() + '</div>');
        widgetLimit(obj);
        widgetIdx();

        $('.toggle-actor.active').trigger('click');
        setTimeout(function() { formLoad(); ckeditorReset(); }, 100);
	}
	
	function widgetLimit(obj)
	{
	    var limit = parseInt(obj.attr('widget-max'));
        var len = parseInt(obj.find('> .widget-container > .widget-row').length);
        if  (limit < 1)
            return;

        if (len >= limit)
            obj.find('> .widget-add').hide();
        else
            obj.find('> .widget-add').show();
	}
	
	function widgetIdx()
	{
        $('.box-body').find('> .widgets > .widget-container > .widget-row').each(function() { //parent only
            var id = uniqid();
            $(this).find('.widget-id').val(id);
        });
        
		$('.widget-row').each(function(k,v) {
			if ($(this).hasClass('widget-idx'))
                return;
            
            $(this).addClass('widget-idx');
            var unique = uniqid();
			
			$(this).find('[name$="[]"]').each(function(kk, vv) {
				$(this).attr('name', $(this).attr('name').replace('[]', '[' + unique + ']'));
			});
		});
	}
	
    widgetIdx();
    
	$(document).on('click', '.widget-add', function() {
		var container = $(this).parent();
		widgetAdd(container);
		
		return false;
	});
    
    $(document).on('click', '.widget-remove', function() {
		
		if (!confirm('Are you sure to remove selected row?'))
			return false;
		
		var row = $(this).parent().parent();
		var widget = row.parent().parent();
        
		row.fadeOut({
			complete:function() {
				row.remove();
				widgetLimit(widget);
			}
		});
		return false;
	});	
    
    $(document).on('click', '.widget-sort-down', function() {
		var source = $(this).parent().parent();
		var target = source.next();
		
		var sourceId = source.attr('id');
		if (!sourceId) {
			sourceId = uniqid();
			source.attr('id', sourceId);
		}
		
		var targetId = target.attr('id');
		if (!targetId) {
			targetId = uniqid();
			target.attr('id', targetId);
		}
		
		$("#" + sourceId).swap({  
            target: targetId,
            opacity: "0.5",
            speed: 500,
            callback: function() {
                target.insertBefore(source); 
				source.removeAttr('style');
				target.removeAttr('style');
				ckeditorReset();
                widgetIdx();
            }
        }); 
		
		return false;
	});
    
    $(document).on('click', '.widget-sort-up', function() {
		
		var source = $(this).parent().parent();
		var target = source.prev();
		
		var sourceId = source.attr('id');
		if (!sourceId) {
			sourceId = uniqid();
			source.attr('id', sourceId);
		}
		
		var targetId = target.attr('id');
		if (!targetId) {
			targetId = uniqid();
			target.attr('id', targetId);
		}
		
		$("#" + sourceId).swap({  
            target: targetId,
            opacity: "0.5",
            speed: 500,
            callback: function() {
                target.insertAfter(source); 
				source.removeAttr('style');
				target.removeAttr('style');
				ckeditorReset();
                widgetIdx();
            }  
        }); 
		
		return false;
	});
});

$(document).on('change','.title',function() {
	if ($(this).attr('lang')) {
		$('input.permalink[lang=' + $(this).attr('lang') + ']').val(slug($(this).val()));
		$('input[origin=meta_title][lang=' + $(this).attr('lang') + ']').val($(this).val());
	} else {
		$('input.permalink').val(slug($(this).val()));
		$('input[origin=meta_title]').val($(this).val());
	}
});
