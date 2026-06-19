<?php
/**
 * Database layer for maca Njuvs news and events.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * News table name.
 *
 * @return string
 */
function maca_menulist_db_info_news_table() {
    global $wpdb;

    return $wpdb->prefix . 'maca_njuvs_news';
}

/**
 * Events table name.
 *
 * @return string
 */
function maca_menulist_db_info_events_table() {
    global $wpdb;

    return $wpdb->prefix . 'maca_njuvs_events';
}

/**
 * Event occurrence exceptions table name.
 *
 * @return string
 */
function maca_menulist_db_info_event_exceptions_table() {
    global $wpdb;

    return $wpdb->prefix . 'maca_njuvs_event_exceptions';
}

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange

/**
 * Create maca Njuvs database tables.
 *
 * @return void
 */
function maca_menulist_db_create_info_hub_tables() {
    global $wpdb;

    if (!isset($wpdb) || empty($wpdb)) {
        return;
    }

    $charset_collate = $wpdb->get_charset_collate();
    $news_table = maca_menulist_db_info_news_table();
    $events_table = maca_menulist_db_info_events_table();
    $exceptions_table = maca_menulist_db_info_event_exceptions_table();

    $sql_news = "CREATE TABLE $news_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL DEFAULT '',
        title_en varchar(255) DEFAULT NULL,
        excerpt text DEFAULT NULL,
        excerpt_en text DEFAULT NULL,
        content longtext DEFAULT NULL,
        content_en longtext DEFAULT NULL,
        image_url varchar(500) DEFAULT NULL,
        status varchar(16) NOT NULL DEFAULT 'draft',
        publish_at datetime DEFAULT NULL,
        expires_at datetime DEFAULT NULL,
        share_web tinyint(1) NOT NULL DEFAULT 1,
        share_facebook tinyint(1) NOT NULL DEFAULT 0,
        share_instagram tinyint(1) NOT NULL DEFAULT 0,
        social_fb_status varchar(16) NOT NULL DEFAULT 'skipped',
        social_ig_status varchar(16) NOT NULL DEFAULT 'skipped',
        social_fb_post_id varchar(64) NOT NULL DEFAULT '',
        social_ig_media_id varchar(64) NOT NULL DEFAULT '',
        sort_order int(11) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY publish_at (publish_at),
        KEY share_web (share_web)
    ) $charset_collate;";

    $sql_events = "CREATE TABLE $events_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL DEFAULT '',
        title_en varchar(255) DEFAULT NULL,
        description text DEFAULT NULL,
        description_en text DEFAULT NULL,
        location varchar(255) DEFAULT NULL,
        location_en varchar(255) DEFAULT NULL,
        image_url varchar(500) DEFAULT NULL,
        price decimal(10,2) DEFAULT NULL,
        start_at datetime NOT NULL,
        end_at datetime NOT NULL,
        is_all_day tinyint(1) NOT NULL DEFAULT 0,
        timezone varchar(64) NOT NULL DEFAULT '',
        recurrence_type varchar(16) NOT NULL DEFAULT 'none',
        recurrence_interval int(11) NOT NULL DEFAULT 1,
        days_of_week varchar(32) DEFAULT NULL,
        recurrence_until date DEFAULT NULL,
        recurrence_count int(11) DEFAULT NULL,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        show_booking_button tinyint(1) NOT NULL DEFAULT 0,
        share_web tinyint(1) NOT NULL DEFAULT 1,
        share_facebook tinyint(1) NOT NULL DEFAULT 0,
        share_instagram tinyint(1) NOT NULL DEFAULT 0,
        social_fb_status varchar(16) NOT NULL DEFAULT 'skipped',
        social_ig_status varchar(16) NOT NULL DEFAULT 'skipped',
        social_fb_post_id varchar(64) NOT NULL DEFAULT '',
        social_ig_media_id varchar(64) NOT NULL DEFAULT '',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY start_at (start_at),
        KEY is_active (is_active),
        KEY recurrence_type (recurrence_type)
    ) $charset_collate;";

    $sql_exceptions = "CREATE TABLE $exceptions_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        event_id bigint(20) unsigned NOT NULL,
        occurrence_date date NOT NULL,
        exception_type varchar(16) NOT NULL DEFAULT 'cancelled',
        new_start_at datetime DEFAULT NULL,
        new_end_at datetime DEFAULT NULL,
        note varchar(255) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY event_id (event_id),
        KEY occurrence_date (occurrence_date),
        UNIQUE KEY event_occurrence (event_id, occurrence_date)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_news);
    dbDelta($sql_events);
    dbDelta($sql_exceptions);
}

/**
 * Ensure maca Njuvs database tables exist.
 *
 * @return void
 */
function maca_menulist_db_ensure_info_hub_tables() {
    static $ensured = false;

    if ($ensured) {
        return;
    }

    global $wpdb;

    $news_table = maca_menulist_db_info_news_table();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $news_table));

    if ($exists !== $news_table) {
        maca_menulist_db_create_info_hub_tables();
    } else {
        maca_menulist_db_migrate_info_hub_event_columns();
    }

    $ensured = true;
}

/**
 * Add event columns introduced after an earlier maca Njuvs release.
 *
 * @return void
 */
function maca_menulist_db_migrate_info_hub_event_columns() {
    global $wpdb;

    $table = maca_menulist_db_info_events_table();
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        return;
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    if (!$wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'price'")) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE $table ADD COLUMN price decimal(10,2) DEFAULT NULL AFTER image_url");
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    if (!$wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'show_booking_button'")) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("ALTER TABLE $table ADD COLUMN show_booking_button tinyint(1) NOT NULL DEFAULT 0 AFTER is_active");
    }
}

/**
 * Drop maca Njuvs database tables.
 *
 * @return void
 */
function maca_menulist_db_drop_info_hub_tables() {
    global $wpdb;

    foreach (
        array(
            maca_menulist_db_info_event_exceptions_table(),
            maca_menulist_db_info_events_table(),
            maca_menulist_db_info_news_table(),
        ) as $table
    ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }
}

/**
 * @return array<int, object>
 */
function maca_menulist_db_get_info_news_items($status = '') {
    global $wpdb;

    $table = maca_menulist_db_info_news_table();
    $where = '';
    $params = array();

    if ($status !== '') {
        $where = 'WHERE status = %s';
        $params[] = (string) $status;
    }

    $sql = "SELECT * FROM $table $where ORDER BY sort_order ASC, COALESCE(publish_at, created_at) DESC, id DESC";

    if (!empty($params)) {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
    return $wpdb->get_results($sql);
}

/**
 * @param int $news_id News ID.
 * @return object|null
 */
function maca_menulist_db_get_info_news($news_id) {
    global $wpdb;

    $table = maca_menulist_db_info_news_table();

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is plugin-controlled.
    $sql = 'SELECT * FROM `' . $table . '` WHERE id = %d';

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return $wpdb->get_row($wpdb->prepare($sql, intval($news_id)));
}

/**
 * @param array<string, mixed> $data Row data.
 * @return int|false
 */
function maca_menulist_db_insert_info_news($data) {
    global $wpdb;

    list($row, $formats) = maca_menulist_db_prepare_info_news_write($data);

    if (empty($row)) {
        return false;
    }

    maca_menulist_db_stamp_row_timestamps($row, $formats, true);

    return $wpdb->insert(maca_menulist_db_info_news_table(), $row, $formats);
}

/**
 * @param int                  $news_id News ID.
 * @param array<string, mixed> $data    Row data.
 * @return int|false
 */
function maca_menulist_db_update_info_news($news_id, $data) {
    global $wpdb;

    list($row, $formats) = maca_menulist_db_prepare_info_news_write($data);

    if (empty($row)) {
        return false;
    }

    maca_menulist_db_stamp_row_timestamps($row, $formats, false);

    return $wpdb->update(
        maca_menulist_db_info_news_table(),
        $row,
        array('id' => intval($news_id)),
        $formats,
        array('%d')
    );
}

/**
 * @param array<string, mixed> $data Row data.
 * @return array{0: array<string, mixed>, 1: array<int, string>}
 */
function maca_menulist_db_prepare_info_news_write($data) {
    $schema = array(
        'title' => '%s',
        'title_en' => '%s',
        'excerpt' => '%s',
        'excerpt_en' => '%s',
        'content' => '%s',
        'content_en' => '%s',
        'image_url' => '%s',
        'status' => '%s',
        'publish_at' => '%s',
        'expires_at' => '%s',
        'share_web' => '%d',
        'share_facebook' => '%d',
        'share_instagram' => '%d',
        'social_fb_status' => '%s',
        'social_ig_status' => '%s',
        'social_fb_post_id' => '%s',
        'social_ig_media_id' => '%s',
        'sort_order' => '%d',
    );

    $row = array();
    $formats = array();

    foreach ($schema as $key => $format) {
        if (array_key_exists($key, $data)) {
            $row[ $key ] = $data[ $key ];
            $formats[] = $format;
        }
    }

    return array($row, $formats);
}

/**
 * @param int $news_id News ID.
 * @return int|false
 */
function maca_menulist_db_delete_info_news($news_id) {
    global $wpdb;

    return $wpdb->delete(
        maca_menulist_db_info_news_table(),
        array('id' => intval($news_id)),
        array('%d')
    );
}

/**
 * @return array<int, object>
 */
function maca_menulist_db_get_info_events($active_only = false) {
    global $wpdb;

    $table = maca_menulist_db_info_events_table();
    $where = $active_only ? 'WHERE is_active = 1' : '';

    return $wpdb->get_results(
        "SELECT * FROM $table $where ORDER BY start_at ASC, id ASC"
    );
}

/**
 * @param int $event_id Event ID.
 * @return object|null
 */
function maca_menulist_db_get_info_event($event_id) {
    global $wpdb;

    $table = maca_menulist_db_info_events_table();

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is plugin-controlled.
    $sql = 'SELECT * FROM `' . $table . '` WHERE id = %d';

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return $wpdb->get_row($wpdb->prepare($sql, intval($event_id)));
}

/**
 * @param array<string, mixed> $data Row data.
 * @return int|false
 */
function maca_menulist_db_insert_info_event($data) {
    global $wpdb;

    list($row, $formats) = maca_menulist_db_prepare_info_event_write($data);

    if (empty($row)) {
        return false;
    }

    maca_menulist_db_stamp_row_timestamps($row, $formats, true);

    return $wpdb->insert(maca_menulist_db_info_events_table(), $row, $formats);
}

/**
 * @param int                  $event_id Event ID.
 * @param array<string, mixed> $data     Row data.
 * @return int|false
 */
function maca_menulist_db_update_info_event($event_id, $data) {
    global $wpdb;

    list($row, $formats) = maca_menulist_db_prepare_info_event_write($data);

    if (empty($row)) {
        return false;
    }

    maca_menulist_db_stamp_row_timestamps($row, $formats, false);

    return $wpdb->update(
        maca_menulist_db_info_events_table(),
        $row,
        array('id' => intval($event_id)),
        $formats,
        array('%d')
    );
}

/**
 * @param array<string, mixed> $data Row data.
 * @return array{0: array<string, mixed>, 1: array<int, string>}
 */
function maca_menulist_db_prepare_info_event_write($data) {
    $schema = array(
        'title' => '%s',
        'title_en' => '%s',
        'description' => '%s',
        'description_en' => '%s',
        'location' => '%s',
        'location_en' => '%s',
        'image_url' => '%s',
        'price' => '%s',
        'start_at' => '%s',
        'end_at' => '%s',
        'is_all_day' => '%d',
        'timezone' => '%s',
        'recurrence_type' => '%s',
        'recurrence_interval' => '%d',
        'days_of_week' => '%s',
        'recurrence_until' => '%s',
        'recurrence_count' => '%d',
        'is_active' => '%d',
        'show_booking_button' => '%d',
        'share_web' => '%d',
        'share_facebook' => '%d',
        'share_instagram' => '%d',
        'social_fb_status' => '%s',
        'social_ig_status' => '%s',
        'social_fb_post_id' => '%s',
        'social_ig_media_id' => '%s',
    );

    $row = array();
    $formats = array();

    foreach ($schema as $key => $format) {
        if (array_key_exists($key, $data)) {
            $row[ $key ] = $data[ $key ];
            $formats[] = $format;
        }
    }

    return array($row, $formats);
}

/**
 * @param int $event_id Event ID.
 * @return int|false
 */
function maca_menulist_db_delete_info_event($event_id) {
    global $wpdb;

    $event_id = intval($event_id);

    $wpdb->delete(
        maca_menulist_db_info_event_exceptions_table(),
        array('event_id' => $event_id),
        array('%d')
    );

    return $wpdb->delete(
        maca_menulist_db_info_events_table(),
        array('id' => $event_id),
        array('%d')
    );
}

/**
 * @param int $event_id Event ID.
 * @return array<int, object>
 */
function maca_menulist_db_get_info_event_exceptions($event_id) {
    global $wpdb;

    $table = maca_menulist_db_info_event_exceptions_table();

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is plugin-controlled.
    $sql = 'SELECT * FROM `' . $table . '` WHERE event_id = %d ORDER BY occurrence_date ASC';

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    return $wpdb->get_results($wpdb->prepare($sql, intval($event_id)));
}

/**
 * @param array<string, mixed> $data Row data.
 * @return int|false
 */
function maca_menulist_db_insert_info_event_exception($data) {
    global $wpdb;

    $row = array(
        'event_id' => intval($data['event_id'] ?? 0),
        'occurrence_date' => (string) ($data['occurrence_date'] ?? ''),
        'exception_type' => sanitize_key($data['exception_type'] ?? 'cancelled'),
        'new_start_at' => isset($data['new_start_at']) ? $data['new_start_at'] : null,
        'new_end_at' => isset($data['new_end_at']) ? $data['new_end_at'] : null,
        'note' => isset($data['note']) ? (string) $data['note'] : '',
    );

    if ($row['event_id'] <= 0 || $row['occurrence_date'] === '') {
        return false;
    }

    return $wpdb->replace(
        maca_menulist_db_info_event_exceptions_table(),
        $row,
        array('%d', '%s', '%s', '%s', '%s', '%s')
    );
}

/**
 * @param int $exception_id Exception ID.
 * @return int|false
 */
function maca_menulist_db_delete_info_event_exception($exception_id) {
    global $wpdb;

    return $wpdb->delete(
        maca_menulist_db_info_event_exceptions_table(),
        array('id' => intval($exception_id)),
        array('%d')
    );
}

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
