(function($) {
    'use strict';

    // Make function globally available
    window.initGallerySlider = function($wrapper) {
        if (!$wrapper || !$wrapper.length) {
            $wrapper = $('.bcc-gallery-slider-wrapper');
        }
        
        $wrapper.each(function() {
            const $wrap = $(this);
            const $slider = $wrap.find('.bcc-gallery-slider');
            const $items = $slider.find('.bcc-slider-item');
            const $prev = $wrap.find('.bcc-slider-prev');
            const $next = $wrap.find('.bcc-slider-next');
            const $dots = $wrap.find('.bcc-slider-dot');
            const $counter = $wrap.find('.bcc-slider-counter');
            
            if ($items.length === 0) return;
            
            let currentIndex = 0;
            
            // Find active index
            $items.each(function(idx) {
                if ($(this).hasClass('active')) {
                    currentIndex = idx;
                }
            });
            
            function showSlide(index) {
                if (index < 0) index = $items.length - 1;
                if (index >= $items.length) index = 0;
                
                $items.removeClass('active');
                $items.eq(index).addClass('active');
                
                $dots.removeClass('active');
                $dots.eq(index).addClass('active');
                
                if ($counter.length) {
                    $counter.text((index + 1) + ' / ' + $items.length);
                }
                
                currentIndex = index;
            }
            
            // Remove old event handlers and attach new ones
            $prev.off('click').on('click', function(e) {
                e.preventDefault();
                showSlide(currentIndex - 1);
            });
            
            $next.off('click').on('click', function(e) {
                e.preventDefault();
                showSlide(currentIndex + 1);
            });
            
            $dots.off('click').on('click', function() {
                const index = $(this).data('index');
                showSlide(index);
            });
            
            // Keyboard navigation
            $wrap.off('keydown').on('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    showSlide(currentIndex - 1);
                } else if (e.key === 'ArrowRight') {
                    showSlide(currentIndex + 1);
                }
            });
        });
    };

    // Initialize on page load
    $(document).ready(function() {
        window.initGallerySlider();
    });

    // Re-initialize when new galleries are added
    $(document).on('bcc-gallery-updated', function() {
        window.initGallerySlider();
    });

    // Select All functionality
    $(document).on('click', '.bcc-bulk-select', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const $container = $btn.closest('.bcc-gallery-container');
        const $checkboxes = $container.find('.bcc-thumb-select');
        
        // Check if all are already selected
        const allChecked = $checkboxes.length === $checkboxes.filter(':checked').length;
        
        // Toggle selection
        $checkboxes.prop('checked', !allChecked);
        
        // Toggle selecting class on thumbnails
        if (!allChecked) {
            $container.find('.bcc-gallery-thumb-wrapper').addClass('bcc-gallery-thumb-selecting');
        } else {
            $container.find('.bcc-gallery-thumb-wrapper').removeClass('bcc-gallery-thumb-selecting');
        }
        
        // Update button text
        $btn.text(allChecked ? 'Select All' : 'Deselect All');
    });

    // Helper function to update count (used by bulk delete)
    function updateCount($container) {
        const count = $container.find('.bcc-gallery-thumb-wrapper').length;
        $container.find('.bcc-gallery-count').text(count + ' image' + (count !== 1 ? 's' : ''));
    }

    // Make refreshMainSlider globally available (reference to the function from bcc-gallery.js)
    // This assumes refreshMainSlider is defined in bcc-gallery.js and accessible
    
})(jQuery);