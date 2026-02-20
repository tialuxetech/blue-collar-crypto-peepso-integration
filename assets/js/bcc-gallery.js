(function ($) {
  'use strict';

  /* ======================================================
     AJAX HELPER
  ====================================================== */

  function bccPost(action, data) {
    return $.post(bcc_ajax.ajax_url, $.extend({
      action: action,
      nonce: bcc_ajax.nonce
    }, data));
  }

  /* ======================================================
     INIT
  ====================================================== */

  $(document).ready(function () {
    $('.bcc-gallery-container').each(function () {
      initGallery($(this));
    });
    
    // Initialize bulk actions
    initBulkActions();
  });

  /* ======================================================
     BULK ACTIONS
  ====================================================== */

  function initBulkActions() {
    
    // Select All / Deselect All
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
    
    // Bulk Delete
    $(document).on('click', '.bcc-bulk-delete', function(e) {
      e.preventDefault();
      
      const $btn = $(this);
      const $container = $btn.closest('.bcc-gallery-container');
      
      if (!$container.length) {
        console.error('Gallery container not found');
        return;
      }
      
      const postId = parseInt($container.data('post'), 10);
      const row = parseInt($container.data('row'), 10);
      
      if (!postId) {
        alert('Missing post ID');
        return;
      }
      
      // Get all selected thumbnails
      const $selected = $container.find('.bcc-thumb-select:checked');
      const $selectedThumbs = $selected.closest('.bcc-gallery-thumb-wrapper');
      
      if (!$selectedThumbs.length) {
        alert('No images selected.');
        return;
      }
      
      if (!confirm('Delete ' + $selectedThumbs.length + ' selected image(s)?')) {
        return;
      }
      
      // Disable button and show loading
      $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span> Deleting...');
      
      // Track deletions
      let deletedCount = 0;
      let totalToDelete = $selectedThumbs.length;
      let failedDeletions = [];
      
      // Delete each selected image
      $selectedThumbs.each(function() {
        const $thumb = $(this);
        const imageId = parseInt($thumb.data('id'), 10);
        
        if (!imageId) {
          deletedCount++;
          $thumb.remove();
          checkComplete();
          return;
        }
        
        // Use bccPost function
        bccPost('bcc_delete_gallery_image', {
          post_id: postId,
          row: row,
          image_id: imageId
        })
        .done(function(res) {
          if (res && res.success) {
            $thumb.remove();
          } else {
            failedDeletions.push(imageId);
            console.error('Delete failed for image:', imageId, res);
          }
        })
        .fail(function(xhr, status, error) {
          failedDeletions.push(imageId);
          console.error('AJAX error:', error);
        })
        .always(function() {
          deletedCount++;
          checkComplete();
        });
      });
      
      function checkComplete() {
        if (deletedCount === totalToDelete) {
          
          // Refresh the main slider
          refreshMainSlider($container, postId, row);
          
          // Update count
          updateCount($container);
          
          // Re-enable button
          $btn.prop('disabled', false).text('Delete Selected');
          
          // Remove selecting class
          $container.find('.bcc-gallery-thumb-wrapper').removeClass('bcc-gallery-thumb-selecting');
          
          if (failedDeletions.length > 0) {
            alert(failedDeletions.length + ' image(s) could not be deleted. Please try again.');
          } else {
            // Show success message if you have a toast system
            if (typeof window.bccToast === 'function') {
              window.bccToast('Images deleted successfully');
            }
          }
        }
      }
    });
  }

  /* ======================================================
     MAIN INIT
  ====================================================== */

  function initGallery($container) {
    const postId = parseInt($container.data('post'), 10);
    const row    = parseInt($container.data('row'), 10);

    initUpload($container, postId, row);
    initDelete($container, postId, row);
    initThumbSlider($container);
    initDragSort($container, postId, row);
    initAutoLazyLoad($container, postId, row);
    
    // Add hover class for checkboxes
    $container.on('mouseenter', '.bcc-gallery-thumb-wrapper', function() {
      $(this).addClass('bcc-gallery-thumb-hover');
    }).on('mouseleave', '.bcc-gallery-thumb-wrapper', function() {
      $(this).removeClass('bcc-gallery-thumb-hover');
    });
  }

  /* ======================================================
     UPLOAD - Modified to refresh main slider
  ====================================================== */

  function initUpload($container, postId, row) {
    const $input  = $container.find('.bcc-gallery-file-input');
    const $button = $container.find('.bcc-gallery-upload');

    $button.on('click', function () {
      $input.trigger('click');
    });

    $input.on('change', function () {
      if (!this.files.length) return;

      const formData = new FormData();
      formData.append('action', 'bcc_upload_gallery_images');
      formData.append('nonce', bcc_ajax.nonce);
      formData.append('post_id', postId);
      formData.append('row', row);

      for (let i = 0; i < this.files.length; i++) {
        formData.append('files[]', this.files[i]);
      }

      // Show loading state
      $button.prop('disabled', true).text('Uploading...');

      $.ajax({
        url: bcc_ajax.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false
      }).done(function (res) {
        if (!res || !res.success) {
          alert('Upload failed');
          return;
        }

        // Add new thumbnails
        res.data.images.forEach(function (img) {
          appendThumb($container, img);
        });

        // Refresh the main slider with all images
        refreshMainSlider($container, postId, row);

        updateCount($container);
        initDragSort($container, postId, row);

      }).fail(function() {
        alert('Upload failed - server error');
      }).always(function() {
        // Restore button
        $button.prop('disabled', false).text('Upload Images');
      });

      this.value = '';
    });
  }

  /* ======================================================
     REFRESH MAIN SLIDER
  ====================================================== */

  function refreshMainSlider($container, postId, row) {
    // Fetch all images for this collection
    $.post(bcc_ajax.ajax_url, {
      action: 'bcc_gallery_list_images',
      nonce: bcc_ajax.nonce,
      post_id: postId,
      row: row,
      page: 1,
      per_page: 50
    }).done(function(res) {
      if (!res || !res.success) return;
      
      const images = res.data.items || [];
      const $sliderSection = $container.find('.bcc-gallery-slider-section');
      
      if (!$sliderSection.length) return;
      
      // Rebuild the main slider HTML
      let sliderHtml = '<div class="bcc-gallery-slider-wrapper">';
      sliderHtml += '<div class="bcc-gallery-slider">';
      
      images.forEach(function(img, index) {
        const activeClass = index === 0 ? 'active' : '';
        sliderHtml += `<div class="bcc-slider-item ${activeClass}">`;
        sliderHtml += `<img src="${img.url}" loading="lazy" alt="">`;
        sliderHtml += '</div>';
      });
      
      sliderHtml += '</div>';
      
      // Add controls if there are images
      if (images.length > 0) {
        sliderHtml += '<div class="bcc-slider-controls">';
        sliderHtml += '<button type="button" class="bcc-slider-prev"><span class="dashicons dashicons-arrow-left-alt2"></span></button>';
        
        sliderHtml += '<div class="bcc-slider-dots">';
        images.forEach(function(img, index) {
          const dotActive = index === 0 ? 'active' : '';
          sliderHtml += `<span class="bcc-slider-dot ${dotActive}" data-index="${index}"></span>`;
        });
        sliderHtml += '</div>';
        
        sliderHtml += '<button type="button" class="bcc-slider-next"><span class="dashicons dashicons-arrow-right-alt2"></span></button>';
        sliderHtml += '</div>';
        
        sliderHtml += `<div class="bcc-slider-counter">1 / ${images.length}</div>`;
      }
      
      sliderHtml += '</div>'; // Close wrapper
      
      // Replace the old slider with the new one
      $sliderSection.html(sliderHtml);
      
      // Trigger event to reinitialize sliders
      $(document).trigger('bcc-gallery-updated');
    });
  }

  /* ======================================================
     DELETE - Single image
  ====================================================== */

  function initDelete($container, postId, row) {
    $container.on('click', '.bcc-gallery-remove', function () {
      const $thumb = $(this).closest('.bcc-gallery-thumb-wrapper');
      const id     = parseInt($thumb.data('id'), 10);

      if (!id) return;

      if (!confirm('Delete image?')) return;

      // Show loading state
      $(this).text('...');

      bccPost('bcc_delete_gallery_image', {
        post_id: postId,
        row: row,
        image_id: id
      }).done(function (res) {
        if (!res || !res.success) {
          alert('Delete failed');
          return;
        }

        $thumb.remove();
        
        // Refresh the main slider after deletion
        refreshMainSlider($container, postId, row);
        
        updateCount($container);
      }).fail(function() {
        alert('Delete failed - server error');
      });
    });
  }

  /* ======================================================
     AUTO LAZY LOAD
  ====================================================== */

  function initAutoLazyLoad($container, postId, row) {
    const $strip = $container.find('.bcc-gallery-thumbnails');

    $strip.on('scroll', function () {
      const total = parseInt($strip.data('total'), 10);
      const page  = parseInt($strip.data('page') || 1, 10);
      const shown = $container.find('.bcc-gallery-thumb-wrapper').length;

      if (shown >= total) return;

      const nearEnd =
        $strip[0].scrollLeft + $strip.outerWidth() >=
        $strip[0].scrollWidth - 200;

      if (!nearEnd) return;

      // Prevent spamming
      if ($strip.data('loading')) return;
      $strip.data('loading', true);

      bccPost('bcc_gallery_list_images', {
        post_id: postId,
        row: row,
        page: page + 1,
        per_page: 12
      }).done(function (res) {
        if (!res || !res.success) return;

        $strip.data('page', res.data.page);

        res.data.items.forEach(function (img) {
          appendThumb($container, img);
        });

        initDragSort($container, postId, row);
        $strip.data('loading', false);
      });
    });
  }

  /* ======================================================
     THUMB SLIDER
  ====================================================== */

  function initThumbSlider($container) {
    const $strip = $container.find('.bcc-gallery-thumbnails');
    const thumbWidth = 100;
    const scrollAmount = thumbWidth * 6;

    $container.on('click', '.bcc-thumb-next', function () {
      $strip[0].scrollLeft += scrollAmount;
    });

    $container.on('click', '.bcc-thumb-prev', function () {
      $strip[0].scrollLeft -= scrollAmount;
    });
  }

  /* ======================================================
     DRAG SORT
  ====================================================== */

  function initDragSort($container, postId, row) {
    const $thumbs = $container.find('.bcc-gallery-thumbnails');
    if (!$thumbs.length) return;

    let dragged = null;

    $thumbs.find('.bcc-gallery-thumb-wrapper')
      .attr('draggable', true)
      .off('dragstart dragend dragover')
      .on('dragstart', function (e) {
        dragged = this;
        $(this).addClass('is-dragging');
        e.originalEvent.dataTransfer.effectAllowed = 'move';
      })
      .on('dragend', function () {
        $(this).removeClass('is-dragging');
        dragged = null;
        saveOrder($container, postId, row);
      })
      .on('dragover', function (e) {
        e.preventDefault();
        if (!dragged || dragged === this) return;

        const rect = this.getBoundingClientRect();
        const after = (e.originalEvent.clientX - rect.left) > rect.width / 2;

        if (after) {
          this.after(dragged);
        } else {
          this.before(dragged);
        }
      });
  }

  /* ======================================================
     SAVE ORDER
  ====================================================== */

  function saveOrder($container, postId, row) {
    const ids = [];

    $container.find('.bcc-gallery-thumb-wrapper').each(function () {
      const id = parseInt($(this).data('id'), 10);
      if (id) ids.push(id);
    });

    if (!ids.length) return;

    bccPost('bcc_gallery_reorder_images', {
      post_id: postId,
      row: row,
      order: ids
    });
  }

  /* ======================================================
     HELPERS
  ====================================================== */

  function appendThumb($container, img) {
    const html = `
      <div class="bcc-gallery-thumb-wrapper" draggable="true" data-id="${img.id}">
        <input type="checkbox" class="bcc-thumb-select">
        <img src="${img.thumbnail || img.url}" loading="lazy">
        <span class="bcc-gallery-remove">×</span>
      </div>
    `;
    $container.find('.bcc-gallery-thumbnails').append(html);
    
    // Trigger event to reinitialize
    $(document).trigger('bcc-gallery-updated');
  }

  function updateCount($container) {
    const count = $container.find('.bcc-gallery-thumb-wrapper').length;
    $container.find('.bcc-gallery-count')
      .text(count + ' image' + (count !== 1 ? 's' : ''));
  }

  // Make functions available globally if needed
  window.bccRefreshGallerySlider = refreshMainSlider;

})(jQuery);