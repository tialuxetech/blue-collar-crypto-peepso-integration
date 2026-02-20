<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 *  Gallery Repository (DB Layer)
 * ======================================================
 */

class BCC_Gallery_Repository {

    /* ======================================================
       TABLE HELPERS
    ====================================================== */

    private static function collections_table() {
        global $wpdb;
        return $wpdb->prefix . 'bcc_collections';
    }

    private static function images_table() {
        global $wpdb;
        return $wpdb->prefix . 'bcc_collection_images';
    }

    /* ======================================================
       COLLECTIONS
    ====================================================== */

    public static function get_or_create_collection(int $post_id, int $user_id, int $sort_order) {

        global $wpdb;
        $table = self::collections_table();

        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE post_id=%d AND sort_order=%d",
                $post_id,
                $sort_order
            )
        );

        if ($existing) {
            return $existing;
        }

        $wpdb->insert(
            $table,
            [
                'post_id'    => $post_id,
                'user_id'    => $user_id,
                'name'       => 'Collection ' . ($sort_order + 1),
                'sort_order' => $sort_order,
                'image_count'=> 0
            ],
            ['%d','%d','%s','%d','%d']
        );

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE id=%d",
                $wpdb->insert_id
            )
        );
    }

    /* ======================================================
       IMAGES
    ====================================================== */

    public static function count_images(int $collection_id): int {

        global $wpdb;
        $table = self::images_table();

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE collection_id=%d",
                $collection_id
            )
        );
    }

    public static function insert_image(int $collection_id, array $data): int {

        global $wpdb;
        $table = self::images_table();

        $wpdb->insert(
            $table,
            [
                'collection_id' => $collection_id,
                'file'          => $data['file'],
                'url'           => $data['url'],
                'thumbnail'     => $data['thumbnail'],
                'size'          => $data['size'],
                'sort_order'    => self::count_images($collection_id)
            ],
            ['%d','%s','%s','%s','%d','%d']
        );

        // increment counter
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . self::collections_table() . "
                 SET image_count = image_count + 1
                 WHERE id=%d",
                $collection_id
            )
        );

        return (int) $wpdb->insert_id;
    }

    public static function get_images(int $collection_id, int $limit = 50): array {

        global $wpdb;
        $table = self::images_table();

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 WHERE collection_id=%d
                 ORDER BY sort_order ASC, id ASC
                 LIMIT %d",
                $collection_id,
                $limit
            )
        );

        return is_array($rows) ? $rows : [];
    }

    public static function get_images_paged(int $collection_id, int $page = 1, int $per_page = 12): array {

        global $wpdb;
        $table = self::images_table();

        $page = max(1, $page);
        $per_page = max(1, min(50, $per_page));
        $offset = ($page - 1) * $per_page;

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 WHERE collection_id=%d
                 ORDER BY sort_order ASC, id ASC
                 LIMIT %d OFFSET %d",
                $collection_id,
                $per_page,
                $offset
            )
        );

        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE collection_id=%d",
                $collection_id
            )
        );

        return [
            'items' => is_array($items) ? $items : [],
            'total' => $total
        ];
    }

    public static function update_sort_orders(int $collection_id, array $ordered_ids): bool {

        global $wpdb;
        $table = self::images_table();

        $ordered_ids = array_values(array_filter(array_map('intval', $ordered_ids)));

        if (!$ordered_ids) return false;

        // verify ownership
        $placeholders = implode(',', array_fill(0, count($ordered_ids), '%d'));
        $ids_sql = $wpdb->prepare($placeholders, ...$ordered_ids);

        $valid = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table
                 WHERE collection_id=%d AND id IN ($ids_sql)",
                $collection_id
            )
        );

        if ($valid !== count($ordered_ids)) {
            return false;
        }

        foreach ($ordered_ids as $index => $id) {
            $wpdb->update(
                $table,
                ['sort_order' => $index],
                ['id' => $id, 'collection_id' => $collection_id],
                ['%d'],
                ['%d','%d']
            );
        }

        return true;
    }

    public static function delete_image(int $image_id) {

        global $wpdb;
        $table = self::images_table();

        $image = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE id=%d",
                $image_id
            )
        );

        if (!$image) return false;

        $wpdb->delete($table, ['id' => $image_id], ['%d']);

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . self::collections_table() . "
                 SET image_count = GREATEST(image_count - 1, 0)
                 WHERE id=%d",
                $image->collection_id
            )
        );

        return $image;
    }
}
