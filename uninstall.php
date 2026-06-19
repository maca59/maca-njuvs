<?php
/**
 * Uninstall maca Njuvs.
 *
 * Tables and options are kept by default so content survives uninstall/reinstall.
 * To remove all data, define MACA_NJUVS_UNINSTALL_DROP_DATA as true before uninstalling.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (!defined('MACA_NJUVS_UNINSTALL_DROP_DATA') || !MACA_NJUVS_UNINSTALL_DROP_DATA) {
    return;
}

global $wpdb;

$maca_njuvs_tables = array(
    $wpdb->prefix . 'maca_njuvs_news',
    $wpdb->prefix . 'maca_njuvs_events',
    $wpdb->prefix . 'maca_njuvs_event_exceptions',
    $wpdb->prefix . 'maca_njuvs_social_log',
);

foreach ($maca_njuvs_tables as $maca_njuvs_table) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $wpdb->query("DROP TABLE IF EXISTS `$maca_njuvs_table`");
}

$maca_njuvs_options = array(
    'maca_njuvs_enabled',
    'maca_njuvs_meta_app_id',
    'maca_njuvs_meta_app_secret',
    'maca_njuvs_meta_page_token',
    'maca_njuvs_meta_user_token',
    'maca_njuvs_meta_page_id',
    'maca_njuvs_meta_page_name',
    'maca_njuvs_meta_ig_user_id',
    'maca_njuvs_meta_ig_username',
    'maca_njuvs_meta_token_expires',
    'maca_njuvs_meta_test_image_url',
);

foreach ($maca_njuvs_options as $maca_njuvs_option) {
    delete_option($maca_njuvs_option);
}

wp_clear_scheduled_hook('maca_njuvs_social_cron');
