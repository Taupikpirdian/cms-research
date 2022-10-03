var $dUnique = 0;

/* Dependency Plugin */
$.fn.dependency = function(param) {
    var actor = param.actor,
        enableAt = param.enableAt,
        disableAt = param.disableAt;

    if (!actor)
        actor = $dependencyGlobalActor;

    if (!actor)
        return;

    $(this).each(function(k, v) {

        if (!$(this).hasClass('d-enable'))
            $(this).addClass('d-enable');

        if (!$(this).dependencyActor)
            $(this).dependencyActor = actor;

        if (enableAt && enableAt instanceof Array) {

            if (typeof $(this).attr('d-enable-at') != 'undefined')
                $.each($(this).attr('d-enable-at'), function(k, v) {
                    enableAt.push(v);
                });

            $(this).attr('d-enable-at', enableAt);
        }
        
        if (disableAt && disableAt instanceof Array) {
            if (typeof $(this).attr('d-disable-at') != 'undefined') {
                $.each($(this).attr('d-disable-at').split(','), function(k, v) {
                    disableAt.push(v);
                });
            }

            $(this).attr('d-disable-at', disableAt);
        }

        //work actor
        var actorId = $(actor).attr('id');
        if (!actorId) {
            $dUnique++;
            actorId = 'dependency_' + $dUnique;
            $(actor).attr('id', actorId);
        }

        $(this).attr('d-actor-id', actorId);

        if (!$(actor).hasClass('d-actor'))
            $(actor).addClass('d-actor');

        $(window).load(function() {
            setTimeout(function() {
                $('.d-actor').each(function() {
                    if ($(this).hasClass('d-active'))
                        return;

                    $(this).change(function() {
                        var obj = $(this);

                        $('.d-enable').each(function() {
                            if ($(this).attr('d-actor-id') != $(obj).attr('id'))
                                return;

                            var target = $(this);
                            var inputs = target.find('input,select,texarea');

                            var enableAt = $(this).attr('d-enable-at');
                            if (enableAt) {
                                if (enableAt.indexOf(obj.val()) >= 0) {
                                    $(this).show();
                                    inputs.removeClass('ignore');
                                } else {
                                    $(this).hide();
                                    inputs.val('');
                                    inputs.addClass('ignore');
                                }
                            }
                            
                            var disableAt = $(this).attr('d-disable-at');
                            if (disableAt) {
                                if (disableAt.indexOf(obj.val()) >= 0) {
                                    $(this).hide();
                                    inputs.val('');
                                    inputs.addClass('ignore');
                                } else {
                                    $(this).show();
                                    inputs.removeClass('ignore');
                                }
                            }
                        });
                    });

                    $(this).addClass('d-active');
                    $(this).trigger('change');
                });
            }, 500); 
        });
    });
};

var widgetDependency = function(source,type) {
    $('.widgets[widget-source=' + source + ']').dependency({enableAt:[type]});
}
            