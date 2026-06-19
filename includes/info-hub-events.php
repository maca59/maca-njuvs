<?php
/**
 * Recurring event occurrence expansion for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fetch active web-visible events for occurrence expansion.
 *
 * @return array<int, object>
 */
function maca_menulist_info_hub_db_get_expandable_events() {
    global $wpdb;

    $table = maca_menulist_db_info_events_table();

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is plugin-controlled.
    $sql = 'SELECT * FROM `' . $table . '`
        WHERE is_active = 1
        AND share_web = 1
        ORDER BY start_at ASC, id ASC';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
    return $wpdb->get_results($sql);
}

/**
 * @param array<int, int> $event_ids Event IDs.
 * @return array<int, array<string, object>>
 */
function maca_menulist_info_hub_get_exceptions_map($event_ids) {
    $event_ids = array_values(array_filter(array_map('intval', $event_ids)));

    if (empty($event_ids)) {
        return array();
    }

    global $wpdb;

    $table = maca_menulist_db_info_event_exceptions_table();
    $placeholders = implode(',', array_fill(0, count($event_ids), '%d'));

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$table}` WHERE event_id IN ($placeholders)", $event_ids));

    $map = array();

    foreach ($rows as $row) {
        $event_id = (int) $row->event_id;
        $date = (string) $row->occurrence_date;

        if (!isset($map[ $event_id ])) {
            $map[ $event_id ] = array();
        }

        $map[ $event_id ][ $date ] = $row;
    }

    return $map;
}

/**
 * Build occurrence start/end datetimes on a calendar date.
 *
 * @param object $event Event row.
 * @param string $date  Y-m-d.
 * @return array{0: string, 1: string}
 */
function maca_menulist_info_hub_occurrence_bounds($event, $date) {
    $start_at = (string) $event->start_at;
    $end_at = (string) $event->end_at;
    $start_time = strlen($start_at) >= 19 ? substr($start_at, 11, 8) : '00:00:00';
    $end_time = strlen($end_at) >= 19 ? substr($end_at, 11, 8) : '23:59:59';

    if (!empty($event->is_all_day)) {
        return array($date . ' 00:00:00', $date . ' 23:59:59');
    }

    $occ_start = $date . ' ' . $start_time;
    $occ_end = $date . ' ' . $end_time;

    if ($occ_end < $occ_start) {
        $start_ts = maca_menulist_wp_mysql_to_timestamp($occ_start);
        if ($start_ts !== false) {
            $end_date = wp_date('Y-m-d', $start_ts + DAY_IN_SECONDS);
            $occ_end = $end_date . ' ' . $end_time;
        }
    }

    return array($occ_start, $occ_end);
}

/**
 * Create a normalized occurrence object.
 *
 * @param object $event           Event row.
 * @param string $occurrence_date Y-m-d.
 * @param string $start_at        MySQL datetime.
 * @param string $end_at          MySQL datetime.
 * @param object|null $exception  Exception row if any.
 * @return object|null
 */
function maca_menulist_info_hub_make_occurrence($event, $occurrence_date, $start_at, $end_at, $exception = null) {
    if ($exception && (string) $exception->exception_type === 'cancelled') {
        return null;
    }

    if ($exception && (string) $exception->exception_type === 'modified') {
        if (!empty($exception->new_start_at)) {
            $start_at = (string) $exception->new_start_at;
        }
        if (!empty($exception->new_end_at)) {
            $end_at = (string) $exception->new_end_at;
        }
    }

    return (object) array(
        'event_id' => (int) $event->id,
        'occurrence_date' => $occurrence_date,
        'start_at' => $start_at,
        'end_at' => $end_at,
        'event' => $event,
        'exception' => $exception,
        'is_recurring' => isset($event->recurrence_type) && (string) $event->recurrence_type !== 'none',
    );
}

/**
 * Last date for a recurring series.
 *
 * @param object $event Event row.
 * @return string|null Y-m-d
 */
function maca_menulist_info_hub_series_end_date($event) {
    if (!empty($event->recurrence_until)) {
        return (string) $event->recurrence_until;
    }

    return null;
}

/**
 * Whether another occurrence is allowed by recurrence_count.
 *
 * @param object $event     Event row.
 * @param int    $index     Zero-based occurrence index in series.
 * @return bool
 */
function maca_menulist_info_hub_series_allows_index($event, $index) {
    if ($index < 0) {
        return false;
    }

    if (!empty($event->recurrence_count) && (int) $event->recurrence_count > 0) {
        return $index < (int) $event->recurrence_count;
    }

    return true;
}

/**
 * Expand one event into occurrences within a date range.
 *
 * @param object                              $event      Event row.
 * @param string                              $range_start Y-m-d.
 * @param string                              $range_end   Y-m-d.
 * @param array<string, object>               $exceptions Keyed by occurrence_date.
 * @return array<int, object>
 */
function maca_menulist_info_hub_expand_event($event, $range_start, $range_end, $exceptions = array()) {
    $recurrence = isset($event->recurrence_type) ? (string) $event->recurrence_type : 'none';
    $series_start = substr((string) $event->start_at, 0, 10);
    $series_end = maca_menulist_info_hub_series_end_date($event);

    if ($series_start === '') {
        return array();
    }

    $expand_from = max($range_start, $series_start);
    $expand_to = $range_end;

    if ($series_end !== null && $series_end < $expand_from) {
        return array();
    }

    if ($series_end !== null) {
        $expand_to = min($expand_to, $series_end);
    }

    if ($expand_to < $expand_from) {
        return array();
    }

    $occurrences = array();
    $series_index = 0;

    if ($recurrence === 'none') {
        $date = $series_start;
        if ($date >= $expand_from && $date <= $expand_to) {
            list($start_at, $end_at) = maca_menulist_info_hub_occurrence_bounds($event, $date);
            $exception = isset($exceptions[ $date ]) ? $exceptions[ $date ] : null;
            $occ = maca_menulist_info_hub_make_occurrence($event, $date, $start_at, $end_at, $exception);

            if ($occ) {
                $occurrences[] = $occ;
            }
        }

        return $occurrences;
    }

    $interval = max(1, (int) ($event->recurrence_interval ?? 1));
    $iterate_from = $series_start;
    $iterate_to = $expand_to;
    $cursor = strtotime($iterate_from . ' 12:00:00');
    $end_ts = strtotime($iterate_to . ' 12:00:00');
    $anchor_ts = strtotime($series_start . ' 12:00:00');

    if ($cursor === false || $end_ts === false || $anchor_ts === false) {
        return array();
    }

    $allowed_days = maca_menulist_parse_days_of_week($event->days_of_week ?? '');

    if ($recurrence === 'weekly' && empty($allowed_days)) {
        $allowed_days = array((int) wp_date('w', $anchor_ts));
    }

    while ($cursor <= $end_ts) {
        $date = wp_date('Y-m-d', $cursor);
        $days_since = (int) floor(($cursor - $anchor_ts) / DAY_IN_SECONDS);
        $matches = false;

        if ($recurrence === 'daily') {
            $matches = ($days_since % $interval) === 0;
        } elseif ($recurrence === 'weekly') {
            $weekday = (int) wp_date('w', $cursor);
            $weeks_since = (int) floor($days_since / 7);
            $matches = in_array($weekday, $allowed_days, true) && ($weeks_since % $interval) === 0;
        } elseif ($recurrence === 'monthly') {
            $anchor_day = (int) wp_date('j', $anchor_ts);
            $cursor_day = (int) wp_date('j', $cursor);
            $months_since = ((int) wp_date('Y', $cursor) - (int) wp_date('Y', $anchor_ts)) * 12
                + ((int) wp_date('n', $cursor) - (int) wp_date('n', $anchor_ts));
            $matches = ($months_since % $interval) === 0 && $cursor_day === $anchor_day;
        }

        if ($matches && $date >= $series_start) {
            if (!maca_menulist_info_hub_series_allows_index($event, $series_index)) {
                break;
            }

            if ($date >= $expand_from && $date <= $expand_to) {
                list($start_at, $end_at) = maca_menulist_info_hub_occurrence_bounds($event, $date);
                $exception = isset($exceptions[ $date ]) ? $exceptions[ $date ] : null;
                $occ = maca_menulist_info_hub_make_occurrence($event, $date, $start_at, $end_at, $exception);

                if ($occ) {
                    $occurrences[] = $occ;
                }
            }

            ++$series_index;

            if (!maca_menulist_info_hub_series_allows_index($event, $series_index)) {
                break;
            }
        }

        $cursor += DAY_IN_SECONDS;
    }

    return $occurrences;
}

/**
 * Expand all events into occurrences.
 *
 * @param array<string, mixed> $args Query args.
 * @return array<int, object>
 */
function maca_menulist_info_hub_get_occurrences($args = array()) {
    if (!maca_njuvs_enabled()) {
        return array();
    }

    $defaults = array(
        'range_start' => wp_date('Y-m-d'),
        'range_end' => function_exists('maca_menulist_wp_now_modify')
            ? maca_menulist_wp_now_modify('+3 months', 'Y-m-d')
            : wp_date('Y-m-d', strtotime('+3 months')),
        'limit' => 50,
        'offset' => 0,
        'upcoming_only' => true,
    );
    $args = wp_parse_args($args, $defaults);

    $range_start = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $args['range_start']) ? (string) $args['range_start'] : wp_date('Y-m-d');
    $range_end = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $args['range_end'])
        ? (string) $args['range_end']
        : (function_exists('maca_menulist_wp_now_modify')
            ? maca_menulist_wp_now_modify('+3 months', 'Y-m-d')
            : wp_date('Y-m-d', strtotime('+3 months')));

    if ($range_end < $range_start) {
        $range_end = $range_start;
    }

    $events = maca_menulist_info_hub_db_get_expandable_events();

    if (empty($events)) {
        return array();
    }

    $event_ids = array_map(
        static function ($event) {
            return (int) $event->id;
        },
        $events
    );
    $exceptions_map = maca_menulist_info_hub_get_exceptions_map($event_ids);
    $all = array();

    foreach ($events as $event) {
        $event_id = (int) $event->id;
        $exceptions = isset($exceptions_map[ $event_id ]) ? $exceptions_map[ $event_id ] : array();
        $expanded = maca_menulist_info_hub_expand_event($event, $range_start, $range_end, $exceptions);
        $all = array_merge($all, $expanded);
    }

    usort(
        $all,
        static function ($a, $b) {
            $cmp = strcmp((string) $a->start_at, (string) $b->start_at);

            if ($cmp !== 0) {
                return $cmp;
            }

            return $a->event_id <=> $b->event_id;
        }
    );

    if (!empty($args['upcoming_only'])) {
        $now = function_exists('maca_menulist_wp_now_mysql')
            ? maca_menulist_wp_now_mysql()
            : current_time('mysql');

        $all = array_values(
            array_filter(
                $all,
                static function ($occ) use ($now) {
                    return (string) $occ->end_at >= $now;
                }
            )
        );
    }

    $offset = max(0, (int) $args['offset']);
    $limit = max(1, min(5000, (int) $args['limit']));

    return array_slice($all, $offset, $limit);
}

/**
 * Admin label for recurrence type.
 *
 * @param string $type Recurrence type.
 * @return string
 */
function maca_menulist_info_hub_recurrence_label($type) {
    $labels = array(
        'none' => __('One-time', 'maca-njuvs'),
        'daily' => __('Daily', 'maca-njuvs'),
        'weekly' => __('Weekly', 'maca-njuvs'),
        'monthly' => __('Monthly', 'maca-njuvs'),
    );

    return isset($labels[ $type ]) ? $labels[ $type ] : $type;
}

/**
 * Summary of recurrence settings for admin list.
 *
 * @param object $event Event row.
 * @return string
 */
function maca_menulist_info_hub_recurrence_summary($event) {
    $type = isset($event->recurrence_type) ? (string) $event->recurrence_type : 'none';

    if ($type === 'none') {
        return maca_menulist_info_hub_recurrence_label('none');
    }

    $interval = max(1, (int) ($event->recurrence_interval ?? 1));
    $parts = array(maca_menulist_info_hub_recurrence_label($type));

    if ($interval > 1) {
        /* translators: %d: interval number */
        $parts[] = sprintf(__('every %d', 'maca-njuvs'), $interval);
    }

    if ($type === 'weekly') {
        $days = maca_menulist_parse_days_of_week($event->days_of_week ?? '');

        if (!empty($days)) {
            $names = maca_menulist_weekday_short_labels_ordered();
            $day_labels = array();

            foreach (maca_menulist_sort_weekdays_for_display($days) as $day) {
                if (isset($names[ $day ])) {
                    $day_labels[] = $names[ $day ];
                }
            }

            if (!empty($day_labels)) {
                $parts[] = implode(', ', $day_labels);
            }
        }
    }

    if (!empty($event->recurrence_until)) {
        $parts[] = sprintf(
            /* translators: %s: end date */
            __('until %s', 'maca-njuvs'),
            wp_date(get_option('date_format'), strtotime((string) $event->recurrence_until))
        );
    } elseif (!empty($event->recurrence_count)) {
        $parts[] = sprintf(
            /* translators: %d: number of occurrences */
            _n('%d time', '%d times', (int) $event->recurrence_count, 'maca-njuvs'),
            (int) $event->recurrence_count
        );
    }

    return implode(' · ', $parts);
}

/**
 * Format datetime for an occurrence.
 *
 * @param object $occurrence Occurrence object.
 * @return string
 */
function maca_menulist_info_hub_format_occurrence_datetime($occurrence) {
    $pseudo = (object) array(
        'start_at' => $occurrence->start_at,
        'end_at' => $occurrence->end_at,
        'is_all_day' => !empty($occurrence->event->is_all_day),
    );

    return maca_menulist_info_hub_format_event_datetime($pseudo);
}

/**
 * Short time label for calendar cells.
 *
 * @param object $occurrence Occurrence object.
 * @return string
 */
function maca_menulist_info_hub_format_occurrence_time_short($occurrence) {
    if (!empty($occurrence->event->is_all_day)) {
        return '';
    }

    $ts = maca_menulist_wp_mysql_to_timestamp((string) $occurrence->start_at);

    if ($ts === false) {
        return '';
    }

    return wp_date(get_option('time_format'), $ts);
}

/**
 * Resolve calendar month from query string or fallback.
 *
 * @param string $fallback Fallback month.
 * @return string
 */
function maca_menulist_info_hub_resolve_calendar_month($fallback = '') {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $month = isset($_GET['maca_cal_month']) ? sanitize_text_field(wp_unslash($_GET['maca_cal_month'])) : '';

    if (preg_match('/^\d{4}-\d{2}$/', $month)) {
        return $month;
    }

    if ($fallback !== '' && preg_match('/^\d{4}-\d{2}$/', $fallback)) {
        return $fallback;
    }

    return wp_date('Y-m');
}

/**
 * First and last date of a month (Y-m).
 *
 * @param string $month Y-m.
 * @return array{0: string, 1: string}|null
 */
function maca_menulist_info_hub_month_bounds($month) {
    if (!preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
        return null;
    }

    $year = (int) $matches[1];
    $mon = (int) $matches[2];
    $start = sprintf('%04d-%02d-01', $year, $mon);
    $start_ts = maca_menulist_wp_mysql_to_timestamp($start . ' 12:00:00');

    if ($start_ts === false) {
        return null;
    }

    $end_ts = strtotime('+1 month -1 day', $start_ts);

    if ($end_ts === false) {
        return null;
    }

    return array($start, wp_date('Y-m-d', $end_ts));
}

/**
 * Build calendar navigation URL for another month.
 *
 * @param string $month Y-m target month.
 * @return string
 */
function maca_menulist_info_hub_calendar_month_url($month) {
    global $wp;

    $base = home_url(add_query_arg(array(), $wp->request));

    if (is_singular()) {
        $base = get_permalink();
    }

    return add_query_arg('maca_cal_month', $month, $base);
}
