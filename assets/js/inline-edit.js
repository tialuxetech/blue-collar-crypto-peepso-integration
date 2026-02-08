jQuery(document).on('click','.bcc-inline-text, .bcc-inline-select',function(){

    let el        = jQuery(this);
    let oldValue  = el.text().trim();
    let field     = el.data('field');
    let postID    = el.data('validator');
    let options   = el.data('options');

    let repeater  = el.data('repeater');
    let sub       = el.data('sub');
    let row       = el.data('row');

    let input;

    if(el.hasClass('bcc-inline-select')){
        input = jQuery('<select></select>');
        options.split(',').forEach(function(opt){
            let pair = opt.split(':');
            input.append(`<option value="${pair[0]}">${pair[1]}</option>`);
        });
        input.val(oldValue);
    } else {
        input = jQuery('<input type="text">').val(oldValue);
    }

    el.replaceWith(input);
    input.focus();

    input.on('blur change',function(){

        let newValue = input.val();

        jQuery.post(ajaxurl,{
            action: 'bcc_inline_save',
            post_id: postID,
            field: field,
            value: newValue,
            repeater: repeater ? 1 : 0,
            sub: sub,
            row: row
        },function(){

            let span = jQuery('<span class="'+el.attr('class')+'">'+newValue+'</span>');
            span.attr(el.data());
            input.replaceWith(span);

        });

    });

});
