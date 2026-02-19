/* global jQuery, bcc_ajax, bccToast, bccBuildSelect, bccEscapeHtml, bccIsUrlField, bccShowLoading */
(function($) {
    'use strict';

    if (typeof bcc_ajax === "undefined") {
        console.error("[BCC] bcc_ajax missing");
        return;
    }

    function buildPayload($el, value) {
        const data = {
            action: "bcc_inline_save",
            nonce: bcc_ajax.nonce,
            post_id: $el.data("post"),
            field: $el.data("field"),
            value: value
        };

        if (parseInt($el.data("repeater"), 10) === 1) {
            data.repeater = 1;
            data.row = $el.data("row");
            data.sub = $el.data("sub");
        }

        return data;
    }

    function startEdit($span) {
        if ($span.data("editing")) return;

        const originalValue = $span.attr("data-value") || "";
        const originalHtml = $span.html();
        const field = String($span.data("field") || "");
        const options = String($span.data("options") || "");
        const placeholder = $span.data("placeholder") || "Enter value...";
        const type = $span.data("type") || "text";

        const isSelect = $span.hasClass("bcc-inline-select");
        const isWysiwyg = type === "wysiwyg" || /<\/?[a-z][\s\S]*>/i.test(originalHtml);

        let inputHtml = "";

        if (isSelect) {
            inputHtml = window.bccBuildSelect(options, originalValue);
        } else if (isWysiwyg) {
            inputHtml = `<textarea class="bcc-inline-input" rows="4" placeholder="${window.bccEscapeHtml(placeholder)}">${window.bccEscapeHtml(originalValue)}</textarea>`;
        } else {
            const inputType = window.bccIsUrlField(field) ? "url" : "text";
            inputHtml = `<input class="bcc-inline-input" type="${inputType}" value="${window.bccEscapeHtml(originalValue)}" placeholder="${window.bccEscapeHtml(placeholder)}">`;
        }

        const controls = `
            <span class="bcc-inline-actions">
                <button type="button" class="bcc-inline-save button button-small">Save</button>
                <button type="button" class="bcc-inline-cancel button button-small">Cancel</button>
            </span>
        `;

        $span
            .data({
                editing: true,
                originalHtml: originalHtml,
                originalValue: originalValue
            })
            .addClass("is-editing")
            .html(inputHtml + controls);

        const $input = $span.find(".bcc-inline-input");
        $input.focus();
        if ($input.is("input") && $input.val()) {
            $input[0].setSelectionRange(0, $input.val().length);
        }

        $span.off(".bcc").on("click.bcc", ".bcc-inline-save", function (e) {
            e.preventDefault();
            doSave($span);
        });

        $span.on("click.bcc", ".bcc-inline-cancel", function (e) {
            e.preventDefault();
            cancelEdit($span);
        });

        $span.on("keydown.bcc", ".bcc-inline-input", function (e) {
            if (e.key === "Enter" && this.tagName !== "TEXTAREA") {
                e.preventDefault();
                doSave($span);
            } else if (e.key === "Escape") {
                e.preventDefault();
                cancelEdit($span);
            }
        });
    }

    function cancelEdit($span) {
        const originalHtml = $span.data("originalHtml") || "Update Now";
        $span
            .off(".bcc")
            .removeClass("is-editing")
            .removeData("editing originalHtml originalValue")
            .html(originalHtml);
    }

    function doSave($span) {
        let val = "";

        if ($span.find("select").length) {
            val = $span.find("select").val();
        } else {
            val = $span.find(".bcc-inline-input").val();
        }

        if (!$span.find("textarea").length) {
            val = val.trim();
        }

        const $saveBtn = $span.find(".bcc-inline-save");
        window.bccShowLoading($saveBtn, true);

        $.post(bcc_ajax.ajax_url, buildPayload($span, val))
            .done(function (res) {
                if (!res || res.success !== true) {
                    window.bccToast(res?.data?.message || "Save failed", "error");
                    cancelEdit($span);
                    return;
                }

                $span.attr("data-value", val);

                if ($span.hasClass("bcc-inline-select")) {
                    const map = window.bccParseOptions($span.data("options") || "");
                    const label = map[val] || val || "—";
                    $span.text(label);
                } else {
                    if ($span.find("textarea").length || $span.data("type") === "wysiwyg") {
                        $span.html(val || "");
                    } else {
                        $span.text(val || "—");
                    }
                }

                $span
                    .off(".bcc")
                    .removeClass("is-editing")
                    .removeData("editing originalHtml originalValue");

                window.bccToast("Saved successfully");
            })
            .fail(function (xhr) {
                let msg = "AJAX error";
                if (xhr.responseJSON?.data?.message) {
                    msg = xhr.responseJSON.data.message;
                }
                window.bccToast(msg, "error");
                cancelEdit($span);
            })
            .always(function () {
                window.bccShowLoading($saveBtn, false);
            });
    }

    // Public API
    window.bccInlineEdit = {
        start: startEdit,
        cancel: cancelEdit,
        save: doSave
    };

    // Event handlers
    $(document).on("click", ".bcc-inline-text, .bcc-inline-select", function (e) {
        if ($(e.target).is("input, textarea, select, button, .button")) return;
        startEdit($(this));
    });

    $(document).on("click", ".bcc-inline-edit-btn", function (e) {
        e.preventDefault();
        startEdit($(this).siblings(".bcc-inline-text, .bcc-inline-select").first());
    });

// ======================================================
// ADD REPEATER ITEM HANDLER WITH SPINNER
// ======================================================

$(document).on('click', '.bcc-add-repeater', function(e) {
    e.preventDefault();
    
    const $btn = $(this);
    const postId = $btn.data('post');
    const field = $btn.data('field');
    const originalText = $btn.text();
    
    console.log('Add button clicked:', {postId, field});
    
    if (!postId || !field) {
        console.error('Missing post_id or field');
        window.bccToast('Missing data', 'error');
        return;
    }
    
    // Show spinner
    $btn.prop('disabled', true)
        .html('<span class="dashicons dashicons-update spinning"></span> Adding...');
    
    // Get current rows
    $.post(bcc_ajax.ajax_url, {
        action: 'bcc_inline_save',
        nonce: bcc_ajax.nonce,
        post_id: postId,
        field: field,
        value: '',
        repeater: 1,
        row: -1,
        sub: 'add_new'
    })
    .done(function(response) {
        if (response && response.success) {
            window.bccToast('Item added successfully');
            // Show success state briefly
            $btn.html('<span class="dashicons dashicons-yes"></span> Added!');
            setTimeout(() => location.reload(), 1000);
        } else {
            window.bccToast(response?.data?.message || 'Failed to add item', 'error');
            // Restore button
            $btn.prop('disabled', false).html(originalText);
        }
    })
    .fail(function(xhr) {
        console.error('AJAX error:', xhr);
        window.bccToast('Error adding item', 'error');
        // Restore button
        $btn.prop('disabled', false).html(originalText);
    });
});


})(jQuery);