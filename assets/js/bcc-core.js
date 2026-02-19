/* global jQuery */
(function($) {
    'use strict';

    // Toast notification system
    window.bccToast = function(msg, type = "success", duration = 2200) {
        let $t = $(".bcc-toast");

        if (!$t.length) {
            $("body").append('<div class="bcc-toast"></div>');
            $t = $(".bcc-toast");
        }

        if ($t.data("timeout")) {
            clearTimeout($t.data("timeout"));
        }

        $t.removeClass("show bcc-toast-error").text(msg);

        if (type === "error") {
            $t.addClass("bcc-toast-error");
        }

        void $t[0].offsetWidth;
        $t.addClass("show");

        const timeout = setTimeout(() => {
            $t.removeClass("show");
        }, duration);

        $t.data("timeout", timeout);
    };

    // Helper functions
    window.bccParseOptions = function(str) {
        const map = {};
        if (!str) return map;

        str.split(",").forEach(pair => {
            const parts = pair.split(":");
            if (parts.length >= 2) {
                const key = parts.shift().trim();
                const val = parts.join(":").trim();
                if (key && val) {
                    map[key] = val;
                }
            }
        });
        return map;
    };

    window.bccEscapeHtml = function(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    window.bccIsUrlField = function(name) {
        return name && (name.includes("url") || name.includes("link"));
    };

    window.bccBuildSelect = function(options, selected) {
        const map = window.bccParseOptions(options);
        if (Object.keys(map).length === 0) {
            return '<input class="bcc-inline-input" type="text" value="">';
        }

        let html = '<select class="bcc-inline-input">';
        
        if (!selected && !map.hasOwnProperty('')) {
            html += '<option value="">— Select —</option>';
        }

        Object.keys(map).forEach(key => {
            const sel = String(key) === String(selected) ? " selected" : "";
            html += `<option value="${window.bccEscapeHtml(key)}"${sel}>${window.bccEscapeHtml(map[key])}</option>`;
        });

        html += "</select>";
        return html;
    };

    window.bccShowLoading = function($el, show = true) {
        if (show) {
            $el.addClass("bcc-loading").prop("disabled", true);
        } else {
            $el.removeClass("bcc-loading").prop("disabled", false);
        }
    };

})(jQuery);