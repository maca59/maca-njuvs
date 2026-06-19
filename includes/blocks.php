<?php
/**
 * Gutenberg blocks for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register dynamic blocks on init.
 *
 * @return void
 */
function maca_njuvs_register_block_type() {
    if (!function_exists('register_block_type')) {
        return;
    }

    $registry = WP_Block_Type_Registry::get_instance();

    if (!$registry->is_registered('maca-njuvs/maca-info-news')) {
        register_block_type(
            'maca-njuvs/maca-info-news',
            array(
                'render_callback' => 'maca_njuvs_render_info_news_block_callback',
                'attributes'      => array(
                    'limit'        => array('type' => 'number', 'default' => 5),
                    'layout'       => array('type' => 'string', 'default' => 'list'),
                    'bannerScroll' => array('type' => 'boolean', 'default' => true),
                    'showImage'    => array('type' => 'boolean', 'default' => true),
                    'showDate'     => array('type' => 'boolean', 'default' => true),
                    'showExcerpt'  => array('type' => 'boolean', 'default' => true),
                ),
            )
        );
    }

    if (!$registry->is_registered('maca-njuvs/maca-info-events')) {
        register_block_type(
            'maca-njuvs/maca-info-events',
            array(
                'render_callback' => 'maca_njuvs_render_info_events_block_callback',
                'attributes'      => array(
                    'limit'          => array('type' => 'number', 'default' => 10),
                    'view'           => array('type' => 'string', 'default' => 'list'),
                    'showImage'      => array('type' => 'boolean', 'default' => true),
                    'showLocation'   => array('type' => 'boolean', 'default' => true),
                    'mondayFirst'    => array('type' => 'boolean', 'default' => true),
                    'showSubscribe'  => array('type' => 'boolean', 'default' => true),
                ),
            )
        );
    }

    // maca Njuvs uses its own tables and block names.
}

add_action('init', 'maca_njuvs_register_block_type', 10);

/**
 * Block editor category for maca Njuvs blocks.
 *
 * @param array<int, array<string, mixed>> $categories Existing categories.
 * @return array<int, array<string, mixed>>
 */
function maca_njuvs_register_block_category($categories) {
    $categories[] = array(
        'slug'  => 'maca-njuvs',
        'title' => __('maca Njuvs', 'maca-njuvs'),
        'icon'  => 'megaphone',
    );

    return $categories;
}

add_filter('block_categories_all', 'maca_njuvs_register_block_category', 10, 1);

/**
 * Block editor scripts and styles.
 *
 * @return void
 */
function maca_njuvs_enqueue_block_editor_assets() {
    if (!function_exists('maca_menulist_info_hub_feature_available') || !maca_menulist_info_hub_feature_available()) {
        return;
    }

    wp_enqueue_script(
        'maca-njuvs-info-news-block-editor',
        MACA_NJUVS_PLUGIN_URL . 'blocks/info-news/editor.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-server-side-render'),
        MACA_NJUVS_VERSION,
        true
    );

    wp_set_script_translations(
        'maca-njuvs-info-news-block-editor',
        'maca-njuvs',
        MACA_NJUVS_PLUGIN_DIR . 'languages'
    );

    wp_enqueue_script(
        'maca-njuvs-info-events-block-editor',
        MACA_NJUVS_PLUGIN_URL . 'blocks/info-events/editor.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-server-side-render'),
        MACA_NJUVS_VERSION,
        true
    );

    wp_set_script_translations(
        'maca-njuvs-info-events-block-editor',
        'maca-njuvs',
        MACA_NJUVS_PLUGIN_DIR . 'languages'
    );

    wp_enqueue_style(
        'maca-njuvs-admin',
        MACA_NJUVS_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        MACA_NJUVS_VERSION
    );

    wp_enqueue_style(
        'maca-njuvs-info-hub',
        MACA_NJUVS_PLUGIN_URL . 'assets/css/info-hub.css',
        array(),
        MACA_NJUVS_VERSION
    );
}

/**
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function maca_njuvs_render_info_news_block_callback($attributes) {
    return maca_menulist_render_info_news_list(
        is_array($attributes) ? $attributes : array(),
        array('preview' => is_admin())
    );
}

/**
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function maca_njuvs_render_info_events_block_callback($attributes) {
    return maca_menulist_render_info_events_list(
        is_array($attributes) ? $attributes : array(),
        array('preview' => is_admin())
    );
}

/**
 * Admin page slug helper.
 *
 * @return string
 */
function maca_njuvs_info_hub_admin_page() {
    return 'maca-njuvs';
}
