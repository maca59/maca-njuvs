<?php
/**
 * Database layer for maca Njuvs social publish log.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Social log table name.
 *
 * @return string
 */
function maca_njuvs_db_info_social_log_table() {
    global $wpdb;

    return $wpdb->prefix . 'maca_njuvs_social_log';
}

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange

/**
 * Create social log table.
 *
 * @return void
 */
function maca_njuvs_db_create_info_social_log_table() {
    global $wpdb;

    if (!isset($wpdb) || empty($wpdb)) {
        return;
    }

    $table = maca_njuvs_db_info_social_log_table();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        object_type varchar(16) NOT NULL DEFAULT 'news',
        object_id bigint(20) unsigned NOT NULL DEFAULT 0,
        channel varchar(16) NOT NULL DEFAULT 'facebook',
        status varchar(16) NOT NULL DEFAULT 'pending',
        external_id varchar(128) NOT NULL DEFAULT '',
        message text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY object_ref (object_type, object_id),
        KEY channel (channel),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Ensure social log table exists.
 *
 * @return void
 */
function maca_njuvs_db_ensure_info_social_log_table() {
    static $ensured = false;

    if ($ensured) {
        return;
    }

    global $wpdb;

    $table = maca_njuvs_db_info_social_log_table();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));

    if ($exists !== $table) {
        maca_njuvs_db_create_info_social_log_table();
    }

    $ensured = true;
}

/**
 * @param array<string, mixed> $data Log row.
 * @return int|false
 */
function maca_njuvs_db_insert_info_social_log($data) {
    global $wpdb;

    return $wpdb->insert(
        maca_njuvs_db_info_social_log_table(),
        array(
            'object_type' => sanitize_key($data['object_type'] ?? 'news'),
            'object_id' => intval($data['object_id'] ?? 0),
            'channel' => sanitize_key($data['channel'] ?? 'facebook'),
            'status' => sanitize_key($data['status'] ?? 'pending'),
            'external_id' => (string) ($data['external_id'] ?? ''),
            'message' => isset($data['message']) ? (string) $data['message'] : '',
        ),
        array('%s', '%d', '%s', '%s', '%s', '%s')
    );
}

/**
 * @param int $limit Max rows.
 * @return array<int, object>
 */
function maca_njuvs_db_get_info_social_log($limit = 50) {
    global $wpdb;

    $table = maca_njuvs_db_info_social_log_table();
    $limit = max(1, min(200, intval($limit)));

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table ORDER BY id DESC LIMIT %d",
            $limit
        )
    );
}

/**
 * Drop social log table.
 *
 * @return void
 */
function maca_njuvs_db_drop_info_social_log_table() {
    global $wpdb;

    $table = maca_njuvs_db_info_social_log_table();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
