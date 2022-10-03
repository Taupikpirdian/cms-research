<script>

    $(document).on('ready', function() {
        $(function() {

            $('.copy-translation').click(function(){
                if (confirm('Are you sure you want to fill/replace content in another language with content in the selected language (' + $('.sticky-lang span em').html() + ')?') != true)
                    return;
                
                // if (confirm('Are you sure?')) {
                    let langActive = '';
                    let otherLang = [];
                    $('.toggle-actor').each(function(a,b){
                        if ($(b).hasClass('active')) {
                            langActive = $(b).data('lang');
                        }else{
                            otherLang.push($(b).data('lang'));
                        }
                    });
                    
                    $('form').each(function(k,i){

                        let excludeOrigins = {!! json_encode(config('cadmin.cadmin.excl-copy-trans')) !!};
                        $(i).find('textarea[lang='+langActive+']').each(function(c,d){
                            let valActive = null;
                            let element = $(d);
                            let origin = element.attr('origin');
                            let name = element.attr('name');

                            if (excludeOrigins.includes(origin) == true) {
                                return;
                            }

                            let id = element.attr('id');
                            if (element.hasClass('my-ckeditor')) {
                                valActive = CKEDITOR.instances[id].getData();
                            } else {
                                valActive = element.val();
                            }

                            otherLang.forEach(function(i){
                                let nameEl;
                                let langSplit = name.split('_'+langActive);
                                if (name.includes('old_') || name.includes('auto_')) {
                                    let splitter = name.split('old_').length == 1 ? 'auto_' : 'old_';
                                    let nameSplit = name.split(splitter);
                                    let oldNumber = nameSplit[1].replace(/[^A-Za-z0-9]/g, "");
                                    nameEl = name.replace("_"+langActive+"["+splitter+oldNumber+"]","_"+i+"["+splitter+oldNumber+"]");
                                }else if(langSplit.length == 2){
                                    nameEl = langSplit[0]+"_"+i+langSplit[1];
                                }else{
                                    nameEl = name.substring(0,name.length-3)+"_"+i;
                                }
                                if (element.hasClass('my-ckeditor')) {
                                    let idEl = $('textarea[name="'+nameEl+'"]').attr('id');
                                    CKEDITOR.instances[idEl].setData(valActive);
                                }else{
                                    $('textarea[name="'+nameEl+'"]').val(valActive);
                                }
                            });
                        });

                        $(i).find('input[type=text][lang='+langActive+']').each(function(g,h){
                            let valActive = null;
                            let element = $(h);
                            let origin = element.attr('origin');
                            let name = element.attr('name');
                            if (element.parents('.widget-row')) {
                                origin = element.parents('.lang-target-container').attr('container-id');
                            }

                            if (excludeOrigins.includes(origin) == true) {
                                return;
                            }

                            valActive = element.val();
                            otherLang.forEach(function(i){
                                let nameEl;
                                let langSplit = name.split('_'+langActive);
                                if (name.includes('old_') || name.includes('auto_')) {
                                    let splitter = name.split('old_').length == 1 ? 'auto_' : 'old_';
                                    let nameSplit = name.split(splitter);
                                    let oldNumber = nameSplit[1].replace(/[^A-Za-z0-9]/g, "");
                                    nameEl = name.replace("_"+langActive+"["+splitter+oldNumber+"]","_"+i+"["+splitter+oldNumber+"]");
                                }else if(langSplit.length == 2){
                                    nameEl = langSplit[0]+"_"+i+langSplit[1];
                                }else{
                                    nameEl = name.substring(0,name.length-3)+"_"+i;
                                }
                                $('input[type=text][name="'+nameEl+'"]').val(valActive);
                                if (element.hasClass('cfind')) {
                                    cfind.afterSet($('input[type=text][name="'+nameEl+'"]'),valActive);
                                }
                            });
                        });

                   
                        $(i).find('input.cfind').each(function() {
                            let val = $(this).val();
                            if (!val)
                                return;
                            let json = JSON.parse(val);
                            if (json['alt_' + langActive]) {
                                otherLang.forEach(function(other){
                                    json['alt_' + other] = json['alt_' + langActive];
                                });
                                console.log(json);
                                $(this).val(JSON.stringify(json));
                                cfind.afterSet($(this),JSON.stringify(json));
                            }
                        });
                    });

                    $('.copy-text').hide();
                    $('.copied').show();
                    setTimeout(() => {
                        $('.copied').hide();
                        $('.copy-text').show();
                    }, 500);
                // }
            });
        });
    });

</script>
