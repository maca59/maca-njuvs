<?php
/**
 * Import WordPress posts (wp_posts) into maca Njuvs news.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('MACA_NJUVS_IMPORTED_POST_META')) {
    define('MACA_NJUVS_IMPORTED_POST_META', '_maca_njuvs_imported_news_id');
}

/**
 * Post types that can be imported.
 *
 * @return array<string, string>
 */
function maca_njuvs_import_wp_post_types() {
    return array(
        'post' => __('Posts', 'maca-njuvs'),
        'page' => __('Pages', 'maca-njuvs'),
    );
}

/**
 * Import statistics for the admin UI.
 *
 * @param array<string, mixed> $args Query args.
 * @return array<string, int>
 */
function maca_njuvs_import_wp_posts_stats($args = array()) {
    $args = wp_parse_args(
        is_array($args) ? $args : array(),
        array(
            'post_type' => 'post',
            'category_id' => 0,
        )
    );

    $query_args = maca_njuvs_import_wp_posts_query_args($args);
    unset($query_args['fields']);

    $query = new WP_Query($query_args);
    $total = (int) $query->found_posts;

    $imported_args = $query_args;
    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin-only import UI; meta_query counts already-imported posts.
    $imported_args['meta_query'] = array(
        array(
            'key' => MACA_NJUVS_IMPORTED_POST_META,
            'compare' => 'EXISTS',
        ),
    );
    $imported_args['posts_per_page'] = 1;

    $imported_query = new WP_Query($imported_args);

    return array(
        'total' => $total,
        'imported' => (int) $imported_query->found_posts,
        'pending' => max(0, $total - (int) $imported_query->found_posts),
    );
}

/**
 * @param array<string, mixed> $args Query args.
 * @return array<string, mixed>
 */
function maca_njuvs_import_wp_posts_query_args($args) {
    $args = wp_parse_args(
        is_array($args) ? $args : array(),
        array(
            'post_type' => 'post',
            'category_id' => 0,
            'skip_imported' => false,
            'limit' => -1,
        )
    );

    $post_type = sanitize_key((string) $args['post_type']);
    if (!isset(maca_njuvs_import_wp_post_types()[ $post_type ])) {
        $post_type = 'post';
    }

    $query_args = array(
        'post_type' => $post_type,
        'post_status' => array('publish', 'future', 'draft', 'pending', 'private'),
        'posts_per_page' => (int) $args['limit'],
        'orderby' => 'date',
        'order' => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows' => ((int) $args['limit']) > 0,
    );

    if ((int) $args['category_id'] > 0) {
        $query_args['cat'] = (int) $args['category_id'];
    }

    if (!empty($args['skip_imported'])) {
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin-only import UI; meta_query skips already-imported posts.
        $query_args['meta_query'] = array(
            array(
                'key' => MACA_NJUVS_IMPORTED_POST_META,
                'compare' => 'NOT EXISTS',
            ),
        );
    }

    return $query_args;
}

/**
 * @param int $post_id WordPress post ID.
 * @return int Imported news ID, zero when not imported.
 */
function maca_njuvs_import_wp_post_get_news_id($post_id) {
    return (int) get_post_meta((int) $post_id, MACA_NJUVS_IMPORTED_POST_META, true);
}

/**
 * Map wp_posts row to maca Njuvs news fields.
 *
 * @param WP_Post $post Post object.
 * @return array<string, mixed>
 */
function maca_njuvs_import_wp_post_to_news_data(WP_Post $post) {
    $status = 'draft';
    $publish_at = null;

    switch ($post->post_status) {
        case 'publish':
            $status = 'published';
            $publish_at = $post->post_date;
            break;
        case 'future':
            $status = 'scheduled';
            $publish_at = $post->post_date;
            break;
        case 'private':
            $status = 'published';
            $publish_at = $post->post_date;
            break;
        case 'pending':
        case 'draft':
        default:
            $status = 'draft';
            break;
    }

    $image_url = '';
    $thumbnail_id = get_post_thumbnail_id($post->ID);

    if ($thumbnail_id) {
        $image_url = (string) wp_get_attachment_url($thumbnail_id);
    }

    return array(
        'title' => (string) $post->post_title,
        'title_en' => '',
        'excerpt' => (string) $post->post_excerpt,
        'excerpt_en' => '',
        'content' => (string) $post->post_content,
        'content_en' => '',
        'image_url' => $image_url,
        'status' => $status,
        'publish_at' => $publish_at,
        'expires_at' => null,
        'share_web' => in_array($post->post_status, array('publish', 'future', 'private'), true) ? 1 : 0,
        'share_facebook' => 0,
        'share_instagram' => 0,
        'social_fb_status' => 'skipped',
        'social_ig_status' => 'skipped',
        'social_fb_post_id' => '',
        'social_ig_media_id' => '',
        'sort_order' => 0,
    );
}

/**
 * Import one WordPress post as maca Njuvs news.
 *
 * @param int $post_id Post ID.
 * @return int|WP_Error New news ID or error.
 */
function maca_njuvs_import_wp_post($post_id) {
    $post_id = (int) $post_id;
    $post = get_post($post_id);

    if (!$post instanceof WP_Post) {
        return new WP_Error('maca_njuvs_import_missing_post', __('Post not found.', 'maca-njuvs'));
    }

    $allowed_types = array_keys(maca_njuvs_import_wp_post_types());
    if (!in_array($post->post_type, $allowed_types, true)) {
        return new WP_Error('maca_njuvs_import_invalid_type', __('This post type cannot be imported.', 'maca-njuvs'));
    }

    $existing_news_id = maca_njuvs_import_wp_post_get_news_id($post_id);
    if ($existing_news_id > 0 && maca_njuvs_db_get_info_news($existing_news_id)) {
        return new WP_Error(
            'maca_njuvs_import_already_done',
            __('This post has already been imported.', 'maca-njuvs'),
            array('news_id' => $existing_news_id)
        );
    }

    maca_njuvs_db_ensure_info_hub_tables();

    $inserted = maca_njuvs_db_insert_info_news(maca_njuvs_import_wp_post_to_news_data($post));

    if (!$inserted) {
        return new WP_Error('maca_njuvs_import_insert_failed', __('Could not create news item.', 'maca-njuvs'));
    }

    global $wpdb;
    $news_id = (int) $wpdb->insert_id;
    update_post_meta($post_id, MACA_NJUVS_IMPORTED_POST_META, $news_id);

    return $news_id;
}

/**
 * Import multiple WordPress posts.
 *
 * @param array<string, mixed> $args Import args.
 * @return array<string, mixed>
 */
function maca_njuvs_import_wp_posts_batch($args = array()) {
    $args = wp_parse_args(
        is_array($args) ? $args : array(),
        array(
            'post_type' => 'post',
            'category_id' => 0,
            'skip_imported' => true,
            'limit' => 50,
        )
    );

    $query_args = maca_njuvs_import_wp_posts_query_args(
        array(
            'post_type' => $args['post_type'],
            'category_id' => $args['category_id'],
            'skip_imported' => !empty($args['skip_imported']),
            'limit' => max(1, min(200, (int) $args['limit'])),
        )
    );

    $query = new WP_Query($query_args);
    $imported = 0;
    $skipped = 0;
    $failed = 0;
    $errors = array();

    foreach ($query->posts as $post) {
        if (!$post instanceof WP_Post) {
            continue;
        }

        $result = maca_njuvs_import_wp_post((int) $post->ID);

        if (is_wp_error($result)) {
            if ($result->get_error_code() === 'maca_njuvs_import_already_done') {
                ++$skipped;
                continue;
            }

            ++$failed;
            $errors[] = sprintf(
                '%1$s: %2$s',
                (string) $post->post_title,
                $result->get_error_message()
            );
            continue;
        }

        ++$imported;
    }

    if ($imported > 0) {
        update_option('maca_njuvs_enabled', '1', false);
    }

    return array(
        'imported' => $imported,
        'skipped' => $skipped,
        'failed' => $failed,
        'errors' => $errors,
    );
}
