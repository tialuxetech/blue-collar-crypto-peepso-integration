<?php
if (!defined('ABSPATH')) exit;

class BCC_Gallery_Renderer {
    
    /**
     * Render gallery in view mode
     */
    public static function render_view($post_id, $repeater_row = 0): void {
        // Get images from ACF repeater field
        $images = [];
        if (have_rows('nft_collections', $post_id)) {
            $row = 0;
            while (have_rows('nft_collections', $post_id)) {
                the_row();
                if ($row == $repeater_row) {
                    $image_ids = get_sub_field('collection_gallery');
                    if ($image_ids && is_array($image_ids)) {
                        foreach ($image_ids as $image_id) {
                            $images[] = [
                                'url' => wp_get_attachment_image_url($image_id, 'large'),
                                'thumbnail' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
                            ];
                        }
                    }
                    break;
                }
                $row++;
            }
        }
        
        if (empty($images)) {
            echo '<div class="bcc-gallery-empty">—</div>';
            return;
        }
        
        self::render_slider($images);
    }
    
    /**
     * Render gallery in edit mode with upload capability
     */
    public static function render_edit($post_id, string $data_attrs, $repeater_row = 0): void {
        // Get current images
        $images = [];
        $image_count = 0;
        $image_ids = [];
        
        if (have_rows('nft_collections', $post_id)) {
            $row = 0;
            while (have_rows('nft_collections', $post_id)) {
                the_row();
                if ($row == $repeater_row) {
                    $image_ids = get_sub_field('collection_gallery');
                    if ($image_ids && is_array($image_ids)) {
                        $image_count = count($image_ids);
                        foreach ($image_ids as $image_id) {
                            $images[] = [
                                'id' => $image_id,
                                'url' => wp_get_attachment_image_url($image_id, 'large'),
                                'thumbnail' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
                            ];
                        }
                    }
                    break;
                }
                $row++;
            }
        }
        
        echo '<div class="bcc-gallery-container" ' . $data_attrs . ' data-post="' . $post_id . '" data-row="' . $repeater_row . '">';
        
        // SLIDER SECTION
        echo '<div class="bcc-gallery-slider-section">';
        if (!empty($images)) {
            self::render_slider($images);
        } else {
            echo '<div class="bcc-gallery-empty-state">';
            echo '<p>No images yet. Upload your first image below.</p>';
            echo '</div>';
        }
        echo '</div>';
        
        // UPLOAD SECTION
        echo '<div class="bcc-gallery-upload-section">';
        
        // Thumbnails preview
        if (!empty($images)) {
            echo '<div class="bcc-gallery-thumbnails">';
            foreach ($images as $image) {
                self::render_thumbnail($image);
            }
            echo '</div>';
        }
        
        // Upload button and count
        echo '<div class="bcc-gallery-actions">';
        echo '<button type="button" class="button button-primary bcc-gallery-upload">';
        echo '<span class="dashicons dashicons-upload"></span> Upload Images';
        echo '</button>';
        echo '<span class="bcc-gallery-count">' . $image_count . ' image' . ($image_count !== 1 ? 's' : '') . '</span>';
        echo '</div>';
        
        // Hidden file input
        echo '<input type="file" class="bcc-gallery-file-input" accept="image/*" multiple style="display: none;">';
        
        echo '</div>'; // Close upload section
        echo '</div>'; // Close gallery container
    }
    
    /**
     * Render image slider
     */
    private static function render_slider(array $images): void {
        $slider_id = 'bcc-slider-' . uniqid();
        ?>
        <div class="bcc-gallery-slider-wrapper">
            <div class="bcc-gallery-slider" id="<?php echo $slider_id; ?>">
                <?php foreach ($images as $image): ?>
                    <div class="bcc-slider-item">
                        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($images) > 1): ?>
            <div class="bcc-slider-controls">
                <button type="button" class="bcc-slider-prev" aria-label="Previous image">←</button>
                <div class="bcc-slider-dots"></div>
                <button type="button" class="bcc-slider-next" aria-label="Next image">→</button>
            </div>
            <?php endif; ?>
            
            <div class="bcc-slider-counter">
                <span class="bcc-current-slide">1</span> / <span class="bcc-total-slides"><?php echo count($images); ?></span>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render thumbnail for edit mode
     */
    private static function render_thumbnail(array $image): void {
        ?>
        <div class="bcc-gallery-thumb-wrapper" data-id="<?php echo esc_attr($image['id']); ?>">
            <img src="<?php echo esc_url($image['thumbnail']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" class="bcc-gallery-thumb-img">
            <span class="bcc-gallery-remove" title="Remove image">×</span>
        </div>
        <?php
    }
}