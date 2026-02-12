(function($){

console.log('BCC Inline Editor Loaded');

/* ======================================================
   CLICK TEXT OR SELECT
====================================================== */

$(document).on('click','.bcc-inline-text, .bcc-inline-select',function(){

    const el = $(this);

    if(el.data('editing')) return;
    el.data('editing', true);

    const field    = el.data('field');
    const postID   = el.data('post');
    const options  = el.data('options');

    const repeater = el.data('repeater') || 0;
    const sub      = el.data('sub') || '';
    const row      = el.data('row') || 0;

    const oldValue = el.data('value') || el.text().trim();

    let input;

    /* ---------- Build Input ---------- */

    if(el.hasClass('bcc-inline-select')){

        input = $('<select class="bcc-inline-input"></select>');

        if(options){
            options.split(',').forEach(function(opt){
                let pair = opt.split(':');
                input.append(`<option value="${pair[0]}">${pair[1]}</option>`);
            });
        }

        input.val(oldValue);

    } else {

        input = $('<input type="text" class="bcc-inline-input">').val(oldValue);

    }

    const actions = $(`
        <div class="bcc-inline-actions">
            <button type="button" class="bcc-inline-save">Save</button>
            <button type="button" class="bcc-inline-cancel">Cancel</button>
        </div>
    `);

    el.hide().after(input).after(actions);
    input.focus();

    /* ======================================================
       HELPERS
    ====================================================== */

    function closeEditor(){
        input.remove();
        actions.remove();
        el.show();
        el.data('editing', false);
    }

    function saveValue(){

        const newValue = input.val();

        $.post(bcc_ajax.ajax_url,{
            action: 'bcc_inline_save',
            nonce: bcc_ajax.nonce,
            post_id: postID,
            field: field,
            value: newValue,
            repeater: repeater ? 1 : 0,
            sub: sub,
            row: row
        },function(response){

            if(response && response.success){
                el.text(newValue || 'Update Now');
                el.data('value', newValue);
            } else {
                alert('Save failed');
            }

            closeEditor();
        });
    }

    /* ======================================================
       EVENTS
    ====================================================== */

    actions.find('.bcc-inline-save').on('click', function(e){
        e.preventDefault();
        saveValue();
    });

    actions.find('.bcc-inline-cancel').on('click', function(e){
        e.preventDefault();
        closeEditor();
    });

    input.on('keydown', function(e){

        if(e.key === 'Enter'){
            e.preventDefault();
            saveValue();
        }

        if(e.key === 'Escape'){
            e.preventDefault();
            closeEditor();
        }

    });

});

})(jQuery);

jQuery(document).on('click','.bcc-delete-repeater',function(){

    if(!confirm('Delete this row?')){
        return;
    }

    let btn = jQuery(this);

    jQuery.post(bcc_ajax.ajax_url,{
        action: 'bcc_delete_repeater_row',
        nonce: bcc_ajax.nonce,
        post_id: btn.data('post'),
        field: btn.data('field'),
        row: btn.data('row')
    },function(resp){

        if(resp.success){
            btn.closest('.bcc-slide').fadeOut(200,function(){
                jQuery(this).remove();
            });
        } else {
            alert(resp.data || 'Delete failed');
        }

    });

});

jQuery(document).on('click','.bcc-add-repeater',function(){

    let btn = jQuery(this);

    jQuery.post(bcc_ajax.ajax_url,{
        action: 'bcc_add_repeater_row',
        nonce: bcc_ajax.nonce,
        post_id: btn.data('post'),
        field: btn.data('field')
    },function(resp){

        if(!resp.success){
            alert(resp.data || 'Add failed');
            return;
        }

        // Nuclear option: reload slider
        location.reload();

    });

});
function bccInitSortable(){

    jQuery('.bcc-slider').each(function(){

        if (jQuery(this).hasClass('ui-sortable')) return;

        jQuery(this).sortable({
            items: '> .bcc-slide',
            handle: '.bcc-drag-handle',
            tolerance: 'pointer',
            containment: 'document',
            placeholder: 'bcc-sort-placeholder',

            start: function(e, ui){
                ui.placeholder.height(ui.item.outerHeight());
                ui.placeholder.width(ui.item.outerWidth());
            },

            update: function(){

                let slider = jQuery(this);
                let wrap   = slider.closest('.bcc-slider-wrap');

                let order = [];

                slider.children('.bcc-slide').each(function(){
                    order.push(jQuery(this).data('row'));
                });

                jQuery.post(bcc_ajax.ajax_url,{
                    action: 'bcc_reorder_repeater',
                    nonce: bcc_ajax.nonce,
                    post_id: wrap.data('post'),
                    field: wrap.data('field'),
                    order: order
                });

            }
        });

    });

}

jQuery(document).ready(bccInitSortable);
jQuery(document).ajaxComplete(bccInitSortable);
