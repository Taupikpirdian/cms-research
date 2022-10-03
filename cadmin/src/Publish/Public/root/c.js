/* =========== common =========================================================== */
var $unique = 0;
function uniqid()
{
    $unique++;
    return $unique;
}

function slug(str)
{
    var out = str
        .toLowerCase()
        .replace('&nbsp;', ' ')
        .replace('&amp;', '')
        .replace(/(<([^>]+)>)/ig,'')
        .replace(/ /g,'-')
        .replace(/[^\w-]+/g,'')
        ;
    
    while(out.indexOf('--') > 0) {
        out = out.replace('--', '-');
    }
    
    return out;
}

function striptags(str)
{
	return str.replace(/(<([^>]+)>)/ig,"");
}

/* ============ autoNumeric ===================================================== */

var num2string = function(num, decLen)
{
    if (!decLen)
        decLen = 0;
    
    var opt = {aSep:',', aDec:'.', mDec:decLen};
    if ($locale == 'id') {
        opt.aSep = '.';
        opt.aDec = ',';
    }
    
    opt.aNeg = '-';
    //if (num.indexOf(opt.aSep) || num.indexOf(opt.aDec)) return num;
	return $.fn.autoNumeric.Format(0, parseFloat(num), opt);
}

var string2num = function(num)
{
    if ($locale == 'id') {
        num = num.replace(/[.]/g, '').replace(/[,]/g, '.');
    } else {
        num = num.replace(/[,]/g, '');
    }
    
    if (isNaN(parseFloat(num))) return 0;
    return parseFloat(num);
}

/* :example
<input class="form-control required numeric" decimal-len="2" negative="1">
*/

$.fn.numeric = function()
{
    this.each(function(key, el) {
        if ($(this).hasClass('numeric-formated')) return;
        
        var id = $(el).attr('id');
        
        if (!id) {
            id = 'num_' + uniqid();
            $(el).attr('id', id);
        }
        
        var opt = {aSep:',', aDec:'.'};
        if ($(el).attr('decimal-len')) {
            opt.mDec = $(el).attr('decimal-len');
        }
        
        if ($(el).attr('negative')) {
            opt.aNeg = '-';
        }
        
        if ($locale == 'id') {
            opt.aSep = '.';
            opt.aDec = ',';
        }
        
        $('#' + id).autoNumeric(opt);
        $('#' + id).addClass('numeric-formated');
		
		if ($('#' + id).val()) {
			$('#' + id).val(num2string($('#' + id).val(), opt.mDec));
		}
	});
}

/* ============== VALIDATOR ======================================================== */

/* :example
<input class="form-control required numeric numMin" data-min="5000">
*/
$.validator.addMethod("numericMin", 
    function(value, element) {
        var min = $(element).attr('data-min');
        if (!min) min = 0;
    
        min     = parseFloat(min);
        value   = string2num(value);  
        
        if (value <= min) return false;
        return true;
    }, $.validator.format("Please enter greater value ")
);

$.validator.addMethod("numericMax", 
    function(value, element) {
        var max = $(element).attr('data-max');
        if (!max) max = 0;
        
        max     = parseFloat(max);
        value   = string2num(value);  
        
        if (value >= max) return false;
        return true;
    }, $.validator.format("Please enter less value ")
);

/* :example
<input class="form-control slug">
*/
$.validator.addMethod('slug',
    function(value, element) {
       return this.optional(element) || /^[a-zA-Z0-9-/]+$/i.test(value) || /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
    }, $.validator.format('Not well format')
);
$.validator.addMethod('permalink',
    function(value, element) {
        if (this.optional(element))
            return true;
        if (/[/]$/.test(value) || /^[/]/.test(value))
            return false;
        return /^[a-zA-Z0-9-/]+$/i.test(value);
    }, $.validator.format('Alphabetic, numeric, dash (-) & slash (/) only. cannot begin or end by slash (/)')
);

$.validator.classRuleSettings.numMin = { numericMin: true };
$.validator.classRuleSettings.numMax = { numericMax: true };
$.validator.classRuleSettings.slug = { slug:true };
$.validator.classRuleSettings.permalink = { permalink:true };

/* =============== SLUG ========================================================== */

/* :example
<input class="form-control to-slug" data-target="input[name=slug]">
*/
$(document).on('change', '.to-slug', function() {
	if ($(this).attr('lang')) {
		$($(this).attr('data-target') + '[lang=' + $(this).attr('lang') + ']').val(slug($(this).val()));
		return;
	}
    $($(this).attr('data-target')).val(slug($(this).val())); 
});

/* ===================== TOGGLE =========================== */

$('.toggle-actor').on('show', function() { 
	var target = $(this).attr('toggle-target');
	
	if (!$('.toggle-target[toggle-target=' + target + ']').is(':visible'))
		$('.toggle-target[toggle-target=' + target + ']').fadeIn();
	
	$(this).addClass('active');
}).on('hide', function() {
	var target = $(this).attr('toggle-target');
	$('.toggle-target[toggle-target=' + target + ']').hide();
	
	$(this).removeClass('active');
}).on('click', function() {
	var target = $(this).attr('toggle-target');
	var group = $(this).attr('toggle-group');
	
	if (group != undefined && group) { //if group, show current, hide other group
		$(this).trigger('show');
		$('.toggle-actor[toggle-group=' + group + ']').not(this).trigger('hide');
	} else { //if not group, toggle show-hide
		if ($('.toggle-target[toggle-target=' + target + ']').is(':hidden')) 
			$(this).trigger('show');
		else
			$(this).trigger('hide');
	}
	
	return false;
});

$('.toggle-actor[toggle-group]:first').trigger('click');


/* ===================== SIMPLE EDITOR =========================== */

$.fn.simpleEditor = function() {
    $(this).each(function() {
        var me = $(this);
        var parent = me.parent();
        
        if (me.hasClass('simple-editor-enable'))
            return;
        
        me.addClass('simple-editor-enable');
        
        var id = me.attr('id');
        if (!id) {
            id = 'editor_' + uniqid();
            me.attr('id', id);
        }
        
        parent.find('.simple-editor-tool').remove();
		
        var tool = $('<div id="tool_' + id + '" class="simple-editor-tool" style="display:none;"></div>');
        
		if (me.attr('tools')) {
			var toolOpt = me.attr('tools').split(',');
			
			if (toolOpt.indexOf('b') >= 0)
				tool.append('<a data-wysihtml-command="bold" class="fa fa-bold">&nbsp;</a>');
			if (toolOpt.indexOf('i') >= 0)
				tool.append('<a data-wysihtml-command="italic" class="fa fa-italic">&nbsp;</a>');
			if (toolOpt.indexOf('h1') >= 0)
				tool.append('<a data-wysihtml-command="formatBlock" data-wysihtml-command-value="h1" class="fa fa-header"><sub>1</sub></a>');
			if (toolOpt.indexOf('h2') >= 0)
				tool.append('<a data-wysihtml-command="formatBlock" data-wysihtml-command-value="h2" class="fa fa-header"><sub>2</sub></a>');
			if (toolOpt.indexOf('h3') >= 0)
				tool.append('<a data-wysihtml-command="formatBlock" data-wysihtml-command-value="h3" class="fa fa-header"><sub>3</sub></a>');
			
			parent.prepend(tool);
        }
		
        var editor = new wysihtml.Editor(id, {
            toolbar:        "tool_" + id,
            parserRules:    wysihtmlParserRules,
            useLineBreaks:  true,
            handleTabKey: false
        });
        
        editor.element = $(this);
        
        editor.on("blur", function() {
            if (this.element.attr('disable-new-line'))
                this.setValue(this.getValue().replace('<br>', ''));
            this.element.trigger('blur');
        }).on('change', function() {
            this.element.trigger('change');
        }).on('focus', function() {
            this.element.trigger('focus');
        }).on('focus', function() {
            this.element.trigger('focus');
        });
    });
};

/* TO-DO 
*/
