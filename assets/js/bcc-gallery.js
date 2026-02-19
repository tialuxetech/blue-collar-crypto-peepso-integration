/* global jQuery, bcc_ajax, wp */
(function($) {
    'use strict';



    $(document).ready(function() {
        
        if (typeof bcc_ajax === 'undefined') {
            console.error('BCC Gallery: bcc_ajax is missing');
            return;
        }

        // Initialize all galleries
        initGalleries();

        // Initialize slider for each gallery
        function initSlider($container) {
            const $slider = $container.find('.bcc-gallery-slider');
            const $items = $slider.find('.bcc-slider-item');
            const $prev = $container.find('.bcc-slider-prev');
            const $next = $container.find('.bcc-slider-next');
            const $dots = $container.find('.bcc-slider-dots');
            const $currentSpan = $container.find('.bcc-current-slide');
            
            if (!$items.length) return;
            
            let currentIndex = 0;
            const totalItems = $items.length;
            
            // Create dots
            $dots.empty();
            for (let i = 0; i < totalItems; i++) {
                $dots.append('<span class="bcc-slider-dot" data-index="' + i + '"></span>');
            }
            
            // Show first slide
            $items.removeClass('active').eq(0).addClass('active');
            $dots.find('.bcc-slider-dot').eq(0).addClass('active');
            $currentSpan.text('1');
            
            // Update counter
            $container.find('.bcc-total-slides').text(totalItems);
            
            // Next button
            $next.off('click').on('click', function() {
                currentIndex = (currentIndex + 1) % totalItems;
                updateSlide(currentIndex);
            });
            
            // Prev button
            $prev.off('click').on('click', function() {
                currentIndex = (currentIndex - 1 + totalItems) % totalItems;
                updateSlide(currentIndex);
            });
            
            // Dot clicks
            $dots.off('click', '.bcc-slider-dot').on('click', '.bcc-slider-dot', function() {
                currentIndex = $(this).data('index');
                updateSlide(currentIndex);
            });
            
            function updateSlide(index) {
                $items.removeClass('active').eq(index).addClass('active');
                $dots.find('.bcc-slider-dot').removeClass('active').eq(index).addClass('active');
                $currentSpan.text(index + 1);
            }
        }

        // Initialize all galleries
        function initGalleries() {
            $('.bcc-gallery-container').each(function() {
                const $container = $(this);
                
                // Add hidden file input if missing
                if (!$container.find('.bcc-gallery-file-input').length) {
                    const $fileInput = $('<input type="file" class="bcc-gallery-file-input" accept="image/*" multiple style="display: none;">');
                    $container.append($fileInput);
                }
                
                // Initialize slider
                initSlider($container);
            });
        }

        // Handle upload button click
        $(document).on('click', '.bcc-gallery-upload', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $container = $btn.closest('.bcc-gallery-container');
            const $fileInput = $container.find('.bcc-gallery-file-input');
            
            $fileInput.val('').trigger('click');
        });

        // Handle file selection
        $(document).on('change', '.bcc-gallery-file-input', function(e) {
            const $input = $(this);
            const $container = $input.closest('.bcc-gallery-container');
            const files = e.target.files;
            
            if (!files.length) return;
            
            const formData = new FormData();
            formData.append('action', 'bcc_upload_gallery_images');
            formData.append('nonce', bcc_ajax.nonce);
            formData.append('post_id', $container.data('post'));
            formData.append('field', $container.data('field'));
            formData.append('repeater', $container.data('repeater') || 0);
            formData.append('row', $container.data('row') || 0);
            formData.append('sub', $container.data('sub') || '');
            
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            
            $container.addClass('bcc-loading');
            
            $.ajax({
                url: bcc_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if (typeof window.bccToast === 'function') {
                            window.bccToast('Images uploaded! Refreshing...');
                        }
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        $container.removeClass('bcc-loading');
                        if (typeof window.bccToast === 'function') {
                            window.bccToast('Upload failed', 'error');
                        }
                    }
                },
                error: function() {
                    $container.removeClass('bcc-loading');
                    if (typeof window.bccToast === 'function') {
                        window.bccToast('Upload error', 'error');
                    }
                }
            });
        });

        // Handle remove button
        $(document).on('click', '.bcc-gallery-remove', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            const $wrapper = $btn.closest('.bcc-gallery-thumb-wrapper');
            const $container = $btn.closest('.bcc-gallery-container');
            const imageId = $wrapper.data('id');
            
            if (!imageId) return;
            
            if (!confirm('Remove this image?')) return;
            
            $container.addClass('bcc-loading');
            
            $.post(bcc_ajax.ajax_url, {
                action: 'bcc_delete_gallery_image',
                nonce: bcc_ajax.nonce,
                image_id: imageId
            }).done(function(response) {
                if (response.success) {
                    if (typeof window.bccToast === 'function') {
                        window.bccToast('Image removed');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    $container.removeClass('bcc-loading');
                    if (typeof window.bccToast === 'function') {
                        window.bccToast('Delete failed', 'error');
                    }
                }
            }).fail(function() {
                $container.removeClass('bcc-loading');
                if (typeof window.bccToast === 'function') {
                    window.bccToast('Error', 'error');
                }
            });
        });

        // Re-initialize on dynamic content
        $(document).ajaxComplete(function() {
            initGalleries();
        });

    });

})(jQuery);