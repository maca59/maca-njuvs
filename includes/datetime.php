<?php
/**
 * WordPress site timezone helpers for maca Njuvs.
 *
 * Stored datetimes use site-local MySQL format (Y-m-d H:i:s) via current_time().
 * Display uses Inställningar → Allmänt (tidszon, datum- och tidsformat).
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Current datetime for DB storage (site timezone, MySQL format).
 *
 * @return string
 */
function maca_njuvs_wp_now_mysql() {
    return current_time('mysql');
}

/**
 * Current date in site timezone (Y-m-d).
 *
 * @return string
 */
function maca_njuvs_wp_today() {
    return wp_date('Y-m-d');
}

/**
 * Site-local MySQL datetime for a point N days ago (0 = now).
 *
 * @param int $days Number of days.
 * @return string
 */
function maca_njuvs_wp_days_ago_mysql($days) {
    $days = max(0, (int) $days);
    $timezone = wp_timezone();
    $date = new DateTimeImmutable('now', $timezone);

    if ($days > 0) {
        $date = $date->modify('-' . $days . ' days');
    }

    return $date->format('Y-m-d H:i:s');
}

/**
 * Shift "now" in site timezone and format (e.g. modifier "-1 day").
 *
 * @param string $modifier PHP DateTime modify string.
 * @param string $format   PHP date format; "mysql" and "timestamp" are aliases.
 * @return int|string
 */
function maca_njuvs_wp_now_modify($modifier, $format = 'Y-m-d') {
    $timezone = wp_timezone();
    $date = new DateTimeImmutable('now', $timezone);

    if ($modifier !== '') {
        $date = $date->modify($modifier);
    }

    if ($format === 'timestamp') {
        return $date->getTimestamp();
    }

    if ($format === 'mysql') {
        return $date->format('Y-m-d H:i:s');
    }

    return wp_date($format, $date->getTimestamp());
}

/**
 * Parse a site-local calendar date (Y-m-d) to Unix timestamp (noon local).
 *
 * @param string $date Date Y-m-d.
 * @return int|false
 */
function maca_njuvs_wp_date_to_timestamp($date) {
    $date = trim((string) $date);
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    return maca_njuvs_wp_mysql_to_timestamp($date . ' 12:00:00');
}

/**
 * Create a DateTimeImmutable for site-local wall-clock components.
 *
 * @param string $value Normalized datetime string (Y-m-d H:i:s).
 * @return DateTimeImmutable|false
 */
function maca_njuvs_create_site_wall_datetime($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return false;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        $value .= ':00';
    }

    $timezone = wp_timezone();

    if (PHP_VERSION_ID >= 80200) {
        $date = date_create_immutable_from_format('Y-m-d H:i:s', $value, $timezone);

        return $date === false ? false : $date;
    }

    return date_create_immutable($value, $timezone);
}

/**
 * Format a Unix timestamp as site-local MySQL datetime.
 *
 * @param int $timestamp Unix timestamp.
 * @return string
 */
function maca_njuvs_unix_to_site_mysql($timestamp) {
    $timezone = wp_timezone();
    $date = (new DateTimeImmutable('@' . (int) $timestamp))->setTimezone($timezone);

    return $date->format('Y-m-d H:i:s');
}

/**
 * Format a Unix timestamp for HTML datetime-local inputs (site timezone).
 *
 * @param int|null $timestamp Optional Unix timestamp; defaults to now.
 * @return string
 */
function maca_njuvs_wp_timestamp_to_datetime_local($timestamp = null) {
    if ($timestamp === null) {
        $mysql = maca_njuvs_wp_now_mysql();
        $parsed = maca_njuvs_wp_mysql_to_timestamp($mysql);

        if ($parsed === false) {
            return '';
        }

        $timestamp = $parsed;
    }

    $mysql = maca_njuvs_unix_to_site_mysql((int) $timestamp);

    return str_replace(' ', 'T', substr($mysql, 0, 16));
}

/**
 * Parse an HTML datetime-local value to Unix timestamp (site timezone).
 *
 * @param string $value datetime-local value (Y-m-dTH:i or Y-m-dTH:i:s).
 * @return int|false
 */
function maca_njuvs_wp_datetime_local_to_timestamp($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return false;
    }

    $mysql = maca_njuvs_datetime_local_to_mysql($value);
    if ($mysql === null) {
        return false;
    }

    return maca_njuvs_wp_mysql_to_timestamp($mysql);
}

/**
 * Parse a site-local MySQL datetime string to Unix timestamp.
 *
 * @param string $datetime MySQL datetime (Y-m-d H:i:s).
 * @return int|false
 */
function maca_njuvs_wp_mysql_to_timestamp($datetime) {
    $datetime = trim((string) $datetime);
    if ($datetime === '') {
        return false;
    }

    $date = maca_njuvs_create_site_wall_datetime($datetime);

    if ($date === false) {
        return false;
    }

    return $date->getTimestamp();
}

/**
 * Format a site-local calendar date using WordPress date format.
 *
 * @param string $date  Y-m-d.
 * @param string $format PHP date format; empty = WP date_format option.
 * @param string $empty Value when date is empty.
 * @return string
 */
function maca_njuvs_format_wp_date($date, $format = '', $empty = '') {
    $date = trim((string) $date);
    if ($date === '') {
        return $empty;
    }

    $timestamp = maca_njuvs_wp_date_to_timestamp($date);
    if ($timestamp === false) {
        return $date;
    }

    if ($format === '') {
        $format = get_option('date_format');
    }

    return wp_date($format, $timestamp);
}

/**
 * Format a site-local MySQL datetime using WordPress date/time settings.
 *
 * @param string $datetime MySQL datetime.
 * @param string $format   PHP date format; empty = date + time from WP options.
 * @param string $empty    Value when datetime is empty.
 * @return string
 */
function maca_njuvs_format_wp_datetime($datetime, $format = '', $empty = '—') {
    $datetime = trim((string) $datetime);
    if ($datetime === '') {
        return $empty;
    }

    $timestamp = maca_njuvs_wp_mysql_to_timestamp($datetime);
    if ($timestamp === false) {
        return $datetime;
    }

    if ($format === '') {
        $format = get_option('date_format') . ' ' . get_option('time_format');
    }

    return wp_date($format, $timestamp);
}

/**
 * Format site-local MySQL datetime for HTML datetime-local input (Y-m-d\TH:i).
 *
 * @param string $datetime MySQL datetime.
 * @return string
 */
function maca_njuvs_wp_mysql_to_datetime_local($datetime) {
    $datetime = trim((string) $datetime);
    if ($datetime === '') {
        return '';
    }

    $timestamp = maca_njuvs_wp_mysql_to_timestamp($datetime);
    if ($timestamp === false) {
        return '';
    }

    return maca_njuvs_wp_timestamp_to_datetime_local($timestamp);
}

/**
 * Parse HTML datetime-local or date input as site-local MySQL datetime.
 *
 * @param string $value   Raw input.
 * @param bool   $all_day Treat date-only as start of day.
 * @return string|null
 */
function maca_njuvs_datetime_local_to_mysql($value, $all_day = false) {
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    $timezone = wp_timezone();

    if ($all_day && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value . ' 00:00:00';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $value)) {
        $date = maca_njuvs_create_site_wall_datetime(str_replace('T', ' ', $value));

        if ($date === false) {
            return null;
        }

        return $date->format('Y-m-d H:i:s');
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
        return $value;
    }

    return null;
}

/**
 * Add created_at / updated_at to a DB row before insert or update.
 *
 * @param array<string, mixed> $row       Row data (by reference).
 * @param array<int, string>   $formats   Formats (by reference).
 * @param bool                 $is_insert Whether this is an insert.
 * @return void
 */
function maca_njuvs_db_stamp_row_timestamps(&$row, &$formats, $is_insert = true) {
    $now = maca_njuvs_wp_now_mysql();

    if ($is_insert && !isset($row['created_at'])) {
        $row['created_at'] = $now;
        $formats[] = '%s';
    }

    if (!isset($row['updated_at'])) {
        $row['updated_at'] = $now;
        $formats[] = '%s';
    }
}
