/* global jQuery, bcc_ajax, bccToast */
(function($) {
    'use strict';

    if (typeof bcc_ajax === "undefined") {
        console.error("[BCC] bcc_ajax missing");
        return;
    }

    // Ensure popover is appended to body
    const $pop = $("#bcc-visibility-popover");
    if ($pop.length) {
        $pop.appendTo("body").hide();
    }

    $(document).on("click", ".bcc-visibility-pill", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $pill = $(this);
        const pillOffset = $pill.offset();
        const pillHeight = $pill.outerHeight();
        
        window.bccVisContext = {
            post: $pill.data("post"),
            field: $pill.data("field"),
            button: $pill
        };

        const $pop = $("#bcc-visibility-popover");
        if (!$pop.length) return;

        const popWidth = $pop.outerWidth();
        const popHeight = $pop.outerHeight();
        const winWidth = $(window).width();
        const winHeight = $(window).height();
        const scrollTop = $(window).scrollTop();

        let left = pillOffset.left;
        let top = pillOffset.top + pillHeight + 5;

        if (left + popWidth > winWidth) {
            left = winWidth - popWidth - 10;
        }
        
        if (top + popHeight > winHeight + scrollTop) {
            top = pillOffset.top - popHeight - 5;
        }

        if (left < 0) left = 10;

        $pop.css({
            position: 'absolute',
            top: top + 'px',
            left: left + 'px',
            zIndex: 999999
        }).fadeIn(150);
    });

    $(document).on("click", ".bcc-vis-option", function (e) {
        e.stopPropagation();

        const value = $(this).data("value");
        if (!window.bccVisContext) return;

        const $pill = window.bccVisContext.button;
        $pill.addClass("bcc-loading");

        $.post(bcc_ajax.ajax_url, {
            action: "bcc_save_field_visibility",
            nonce: bcc_ajax.nonce,
            post_id: window.bccVisContext.post,
            field: window.bccVisContext.field,
            visibility: value
        })
        .done(function (res) {
            if (!res || res.success !== true) {
                window.bccToast(res?.data?.message || "Save failed", "error");
                return;
            }

            const labels = {
                public: "🌍 Public",
                members: "👥 Members",
                private: "🔒 Private"
            };

            $pill
                .text(labels[value])
                .removeClass("public members private")
                .addClass(value)
                .data("current", value);

            window.bccToast("Visibility updated");
        })
        .fail(function () {
            window.bccToast("Failed to update visibility", "error");
        })
        .always(function () {
            $pill.removeClass("bcc-loading");
            $("#bcc-visibility-popover").fadeOut(120);
        });
    });

    // Close popover when clicking outside
    $(document).on("click", function (e) {
        const $pop = $("#bcc-visibility-popover");
        if ($pop.is(":visible") && !$(e.target).closest(".bcc-visibility-pill, #bcc-visibility-popover").length) {
            $pop.fadeOut(120);
        }
    });

    // Close popover on Escape key
    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            $("#bcc-visibility-popover").fadeOut(120);
        }
    });

})(jQuery);