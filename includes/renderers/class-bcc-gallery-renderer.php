<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 *  Gallery Renderer (View + Edit)
 * ======================================================
 */

class BCC_Gallery_Renderer {

    /* ======================================================
       VIEW MODE
    ====================================================== */

    public static function render_view(int $post_id, int $row = 0): void {

        $collection = BCC_Gallery_Repository::get_or_create_collection(
            $post_id,
            get_current_user_id(),
            $row
        );

        if (!$collection) {
            echo '<div class="bcc-gallery-empty">—</div>';
            return;
        }

        $result = BCC_Gallery_Repository::get_images_paged((int) $collection->id, 1, 12);
        $images = $result['items'] ?? [];

        if (!$images) {
            echo '<div class="bcc-gallery-empty">—</div>';
            return;
        }

        self::render_main_slider($images);
    }

    /* ======================================================
       EDIT MODE
    ====================================================== */

    public static function render_edit(int $post_id, string $data_attrs, int $row = 0): void {

        $collection = BCC_Gallery_Repository::get_or_create_collection(
            $post_id,
            get_current_user_id(),
            $row
        );

        if (!$collection) {
            echo '<div class="bcc-gallery-empty">Unable to load gallery</div>';
            return;
        }

        $result = BCC_Gallery_Repository::get_images_paged((int) $collection->id, 1, 12);

        $images = $result['items'] ?? [];
        $total  = (int) ($result['total'] ?? 0);
        $shown  = is_array($images) ? count($images) : 0;

        echo '<div class="bcc-gallery-container" ' . $data_attrs .
             ' data-post="' . esc_attr($post_id) . '"' .
             ' data-row="' . esc_attr($row) . '">';

        /* =============================
           MAIN SLIDER
        ============================= */

        echo '<div class="bcc-gallery-slider-section">';
        self::render_main_slider($images);
        echo '</div>';

        /* =============================
           THUMB SLIDER
        ============================= */

        echo '<div class="bcc-gallery-thumb-slider">';

        echo '<button type="button" class="bcc-thumb-arrow bcc-thumb-prev" aria-label="Scroll thumbnails left">‹</button>';

        echo '<div class="bcc-gallery-thumbnails" data-total="' . esc_attr($total) . '" data-page="1">';

        foreach ($images as $img) {
            self::render_thumbnail($img);
        }

        echo '</div>';

        echo '<button type="button" class="bcc-thumb-arrow bcc-thumb-next" aria-label="Scroll thumbnails right">›</button>';

        echo '</div>';

        /* =============================
           ACTIONS
        ============================= */
echo '<div class="bcc-gallery-actions-wrapper">';
echo '<div class="bcc-gallery-action-buttons">';
echo '<button type="button" class="button button-primary bcc-gallery-upload">';
echo '<span class="dashicons dashicons-upload"></span> Upload Images';
echo '</button>';

echo '<button type="button" class="button bcc-bulk-delete">Delete Selected</button>';
echo '</div>'; // Close action-buttons

echo '<div class="bcc-gallery-count-wrapper">';
echo '<span class="bcc-gallery-count"></span>';
echo '</div>'; // Close count-wrapper
echo '</div>'; // Close actions-wrapper
    }

    /* ======================================================
       MAIN IMAGE SLIDER
    ====================================================== */
private static function render_main_slider(array $images): void {

    echo '<div class="bcc-gallery-slider-wrapper">';
    echo '<div class="bcc-gallery-slider">';

    $first = true;
    foreach ($images as $img) {
        $active_class = $first ? 'active' : '';
        $first = false;
        
        echo '<div class="bcc-slider-item ' . $active_class . '">';
        echo '<img src="' . esc_url($img->url) . '" loading="lazy" alt="">';
        echo '</div>';
    }

    echo '</div>'; // Close bcc-gallery-slider
    
    // Add slider controls if there are images
    if (!empty($images)) {
        echo '<div class="bcc-slider-controls">';
        echo '<button type="button" class="bcc-slider-prev" aria-label="Previous image">';
        echo '<span class="dashicons dashicons-arrow-left-alt2"></span>';
        echo '</button>';
        
        echo '<div class="bcc-slider-dots">';
        foreach ($images as $index => $img) {
            $dot_active = $index === 0 ? 'active' : '';
            echo '<span class="bcc-slider-dot ' . $dot_active . '" data-index="' . $index . '"></span>';
        }
        echo '</div>';
        
        echo '<button type="button" class="bcc-slider-next" aria-label="Next image">';
        echo '<span class="dashicons dashicons-arrow-right-alt2"></span>';
        echo '</button>';
        echo '</div>'; // Close bcc-slider-controls
        
        echo '<div class="bcc-slider-counter">1 / ' . count($images) . '</div>';
    }
    
    echo '</div>'; // Close bcc-gallery-slider-wrapper
}

    /* ======================================================
       THUMBNAIL
    ====================================================== */
private static function render_thumbnail(object $img): void {

    echo '<div class="bcc-gallery-thumb-wrapper" draggable="true" data-id="' . esc_attr($img->id) . '">';

    echo '<input type="checkbox" class="bcc-thumb-select">';

    echo '<img src="' . esc_url($img->thumbnail ?: $img->url) . '" loading="lazy" alt="">';

    echo '<span class="bcc-gallery-remove" title="Remove image">×</span>';

    echo '</div>';
}

}