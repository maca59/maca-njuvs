<?php
/**
 * Public iCal feed and calendar subscription helpers for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', 'maca_njuvs_handle_info_events_ical_request', 0);
add_action('parse_request', 'maca_njuvs_parse_request_info_events_ical', 0);
add_filter('pre_handle_404', 'maca_njuvs_prevent_info_events_ical_404', 10, 2);

/**
 * Fixed public feed filename.
 *
 * @return string
 */
function maca_njuvs_get_info_events_ics_filename() {
    return 'maca-njuvs-events.ics';
}

/**
 * HTTPS URL for the events iCal feed.
 *
 * @return string
 */
function maca_njuvs_get_info_events_ics_url() {
    return home_url('/' . maca_njuvs_get_info_events_ics_filename());
}

/**
 * webcal URL for calendar subscription.
 *
 * @return string
 */
function maca_njuvs_get_info_events_webcal_url() {
    $url = maca_njuvs_get_info_events_ics_url();

    if (stripos($url, 'https://') === 0) {
        return 'webcal://' . substr($url, 8);
    }

    if (stripos($url, 'http://') === 0) {
        return 'webcal://' . substr($url, 7);
    }

    return $url;
}

/**
 * Google Calendar subscription URL.
 *
 * @return string
 */
function maca_njuvs_get_info_events_google_calendar_url() {
    return 'https://www.google.com/calendar/render?cid=' . rawurlencode(maca_njuvs_get_info_events_webcal_url());
}

/**
 * Whether the current request targets the iCal feed.
 *
 * @return bool
 */
function maca_njuvs_is_info_events_ical_request() {
    if (defined('MACA_NJUVS_EVENTS_ICS_REQUEST') && MACA_NJUVS_EVENTS_ICS_REQUEST) {
        return true;
    }

    maca_njuvs_bootstrap_info_events_ical_query_vars();

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['maca_njuvs_events_ics']) && sanitize_text_field(wp_unslash($_GET['maca_njuvs_events_ics'])) === '1') {
        return true;
    }

    return (string) get_query_var('maca_njuvs_events_ics') === '1';
}

/**
 * Match feed URL when rewrite rules are stale.
 *
 * @return bool
 */
function maca_njuvs_bootstrap_info_events_ical_query_vars() {
    if ((string) get_query_var('maca_njuvs_events_ics') === '1') {
        return false;
    }

    if (!function_exists('maca_njuvs_request_path_matches_slug')) {
        return false;
    }

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = sanitize_text_field($request_uri);
    $filename = maca_njuvs_get_info_events_ics_filename();

    if (!maca_njuvs_request_path_matches_slug($path, $filename)) {
        return false;
    }

    global $wp_query;

    if ($wp_query instanceof WP_Query) {
        set_query_var('maca_njuvs_events_ics', '1');
        $wp_query->is_404 = false;
        $wp_query->is_home = false;
        $wp_query->is_singular = false;
    }

    return true;
}

/**
 * @param mixed $wp Unused parse_request argument.
 * @return void
 */
function maca_njuvs_parse_request_info_events_ical($wp) {
    unset($wp);
    maca_njuvs_bootstrap_info_events_ical_query_vars();
}

/**
 * @param bool|null $preempt  Whether to short-circuit 404 handling.
 * @param WP_Query  $wp_query Main query instance.
 * @return bool|null
 */
function maca_njuvs_prevent_info_events_ical_404($preempt, $wp_query) {
    if ($preempt) {
        return $preempt;
    }

    if ((string) get_query_var('maca_njuvs_events_ics') === '1') {
        if ($wp_query instanceof WP_Query) {
            $wp_query->is_404 = false;
        }

        return true;
    }

    if (maca_njuvs_bootstrap_info_events_ical_query_vars()) {
        return true;
    }

    return $preempt;
}

/**
 * Serve the public iCal feed.
 *
 * @return void
 */
function maca_njuvs_handle_info_events_ical_request() {
    if (!maca_njuvs_is_info_events_ical_request()) {
        return;
    }

    if (!maca_njuvs_enabled()) {
        status_header(404);
        exit;
    }

    if (!defined('MACA_NJUVS_EVENTS_ICS_REQUEST')) {
        define('MACA_NJUVS_EVENTS_ICS_REQUEST', true);
    }

    $body = maca_njuvs_info_hub_build_ical_feed();
    $filename = maca_njuvs_get_info_events_ics_filename();

    nocache_headers();
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('X-Robots-Tag: noindex, nofollow', true);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    echo wp_kses($body, array());
    exit;
}

/**
 * Escape text for iCalendar properties.
 *
 * @param string $text Raw text.
 * @return string
 */
function maca_njuvs_info_hub_ical_escape($text) {
    $text = wp_strip_all_tags((string) $text);
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace("\r\n", '\n', $text);
    $text = str_replace("\n", '\n', $text);
    $text = str_replace("\r", '\n', $text);
    $text = str_replace(',', '\,', $text);
    $text = str_replace(';', '\;', $text);

    return $text;
}

/**
 * Fold long iCal lines to 75 octets (RFC 5545).
 *
 * @param string $line Line content without CRLF.
 * @return string
 */
function maca_njuvs_info_hub_ical_fold_line($line) {
    $line = (string) $line;

    if (strlen($line) <= 75) {
        return $line;
    }

    $folded = '';
    $remaining = $line;

    while ($remaining !== '') {
        $limit = $folded === '' ? 75 : 74;
        $chunk = substr($remaining, 0, $limit);

        while ($chunk !== '' && (ord($chunk[strlen($chunk) - 1]) & 0xC0) === 0x80) {
            $chunk = substr($chunk, 0, -1);
        }

        if ($chunk === '') {
            $chunk = substr($remaining, 0, $limit);
        }

        $folded .= ($folded === '' ? '' : "\r\n ") . $chunk;
        $remaining = substr($remaining, strlen($chunk));
    }

    return $folded;
}

/**
 * Format DTSTAMP in UTC.
 *
 * @param int|null $timestamp Unix timestamp.
 * @return string
 */
function maca_njuvs_info_hub_ical_dtstamp($timestamp = null) {
    $timestamp = $timestamp === null ? time() : (int) $timestamp;

    return gmdate('Ymd\THis\Z', $timestamp);
}

/**
 * Format DTSTART/DTEND for an occurrence.
 *
 * @param object $occurrence Occurrence object.
 * @return array{0: string, 1: string}
 */
function maca_njuvs_info_hub_ical_datetime_bounds($occurrence) {
    if (!empty($occurrence->event->is_all_day)) {
        $start_date = substr((string) $occurrence->start_at, 0, 10);
        $end_date = substr((string) $occurrence->end_at, 0, 10);

        if ($start_date === '' || $end_date === '') {
            return array('', '');
        }

        $end_exclusive = $end_date;

        if ($end_exclusive <= $start_date) {
            $start_ts = function_exists('maca_njuvs_wp_date_to_timestamp')
                ? maca_njuvs_wp_date_to_timestamp($start_date)
                : strtotime($start_date . ' 12:00:00');

            if ($start_ts !== false) {
                $end_exclusive = gmdate('Y-m-d', $start_ts + DAY_IN_SECONDS);
            }
        } else {
            $end_ts = function_exists('maca_njuvs_wp_date_to_timestamp')
                ? maca_njuvs_wp_date_to_timestamp($end_date)
                : strtotime($end_date . ' 12:00:00');

            if ($end_ts !== false) {
                $end_exclusive = gmdate('Y-m-d', $end_ts + DAY_IN_SECONDS);
            }
        }

        return array(
            'DTSTART;VALUE=DATE:' . str_replace('-', '', $start_date),
            'DTEND;VALUE=DATE:' . str_replace('-', '', $end_exclusive),
        );
    }

    $start_ts = function_exists('maca_njuvs_wp_mysql_to_timestamp')
        ? maca_njuvs_wp_mysql_to_timestamp((string) $occurrence->start_at)
        : strtotime((string) $occurrence->start_at);
    $end_ts = function_exists('maca_njuvs_wp_mysql_to_timestamp')
        ? maca_njuvs_wp_mysql_to_timestamp((string) $occurrence->end_at)
        : strtotime((string) $occurrence->end_at);

    if ($start_ts === false || $end_ts === false) {
        return array('', '');
    }

    if ($end_ts <= $start_ts) {
        $end_ts = $start_ts + HOUR_IN_SECONDS;
    }

    return array(
        'DTSTART:' . gmdate('Ymd\THis\Z', $start_ts),
        'DTEND:' . gmdate('Ymd\THis\Z', $end_ts),
    );
}

/**
 * Build UID for an occurrence.
 *
 * @param object $occurrence Occurrence object.
 * @return string
 */
function maca_njuvs_info_hub_ical_occurrence_uid($occurrence) {
    $host = wp_parse_url(home_url(), PHP_URL_HOST);

    if (!is_string($host) || $host === '') {
        $host = 'localhost';
    }

    $date = str_replace('-', '', (string) $occurrence->occurrence_date);

    return 'maca-info-event-' . (int) $occurrence->event_id . '-' . $date . '@' . $host;
}

/**
 * Build the full iCal document.
 *
 * @return string
 */
function maca_njuvs_info_hub_build_ical_feed() {
    $range_start = wp_date('Y-m-d');
    $range_end = function_exists('maca_njuvs_wp_now_modify')
        ? maca_njuvs_wp_now_modify('+12 months', 'Y-m-d')
        : wp_date('Y-m-d', strtotime('+12 months'));
    $occurrences = maca_njuvs_info_hub_get_occurrences(
        array(
            'range_start' => $range_start,
            'range_end' => $range_end,
            'limit' => 2000,
            'offset' => 0,
            'upcoming_only' => false,
        )
    );

    $calendar_name = get_bloginfo('name');
    if ($calendar_name === '') {
        $calendar_name = __('Events', 'maca-njuvs');
    }

    $lines = array(
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Maca//maca Njuvs//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'REFRESH-INTERVAL;VALUE=DURATION:PT1H',
        'X-PUBLISHED-TTL:PT1H',
        'NAME:' . maca_njuvs_info_hub_ical_escape($calendar_name),
        'X-WR-CALNAME:' . maca_njuvs_info_hub_ical_escape($calendar_name),
    );

    $tz = wp_timezone_string();

    if ($tz !== '') {
        $lines[] = 'X-WR-TIMEZONE:' . maca_njuvs_info_hub_ical_escape($tz);
    }

    foreach ($occurrences as $occurrence) {
        $event = $occurrence->event;
        $summary = maca_njuvs_info_hub_get_event_title($event);
        $description = maca_njuvs_info_hub_get_event_description($event);
        $location = maca_njuvs_info_hub_get_event_location($event);
        list($dtstart, $dtend) = maca_njuvs_info_hub_ical_datetime_bounds($occurrence);

        if ($dtstart === '' || $dtend === '') {
            continue;
        }

        $updated_ts = !empty($event->updated_at)
            ? (function_exists('maca_njuvs_wp_mysql_to_timestamp')
                ? maca_njuvs_wp_mysql_to_timestamp((string) $event->updated_at)
                : strtotime((string) $event->updated_at))
            : time();

        if ($updated_ts === false) {
            $updated_ts = time();
        }

        $created_ts = !empty($event->created_at)
            ? (function_exists('maca_njuvs_wp_mysql_to_timestamp')
                ? maca_njuvs_wp_mysql_to_timestamp((string) $event->created_at)
                : strtotime((string) $event->created_at))
            : $updated_ts;

        if ($created_ts === false) {
            $created_ts = $updated_ts;
        }

        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:' . maca_njuvs_info_hub_ical_occurrence_uid($occurrence);
        $lines[] = 'DTSTAMP:' . maca_njuvs_info_hub_ical_dtstamp($updated_ts);
        $lines[] = 'CREATED:' . maca_njuvs_info_hub_ical_dtstamp($created_ts);
        $lines[] = 'LAST-MODIFIED:' . maca_njuvs_info_hub_ical_dtstamp($updated_ts);
        $lines[] = 'SEQUENCE:0';
        $lines[] = 'STATUS:CONFIRMED';
        $lines[] = 'TRANSP:OPAQUE';
        $lines[] = $dtstart;
        $lines[] = $dtend;
        $lines[] = 'SUMMARY:' . maca_njuvs_info_hub_ical_escape($summary);

        if ($description !== '') {
            $lines[] = 'DESCRIPTION:' . maca_njuvs_info_hub_ical_escape($description);
        }

        if ($location !== '') {
            $lines[] = 'LOCATION:' . maca_njuvs_info_hub_ical_escape($location);
        }

        $lines[] = 'URL:' . esc_url_raw(home_url('/'));
        $lines[] = 'END:VEVENT';
    }

    $lines[] = 'END:VCALENDAR';

    $output = '';

    foreach ($lines as $line) {
        $output .= maca_njuvs_info_hub_ical_fold_line($line) . "\r\n";
    }

    return $output;
}

/**
 * Allowed HTML for calendar subscription markup.
 *
 * @return array<string, array<string, bool>>
 */
function maca_njuvs_info_hub_get_calendar_subscribe_allowed_html() {
    return array(
        'div'    => array(
            'class' => true,
            'id'    => true,
        ),
        'p'      => array('class' => true),
        'h3'     => array('class' => true),
        'ol'     => array('class' => true),
        'li'     => array(),
        'a'      => array(
            'class'  => true,
            'href'   => true,
            'target' => true,
            'rel'    => true,
        ),
        'button' => array(
            'type'           => true,
            'class'          => true,
            'data-feed-url'  => true,
            'aria-haspopup'  => true,
            'aria-controls'  => true,
        ),
        'dialog' => array(
            'class' => true,
            'id'    => true,
            'open'  => true,
        ),
        'form'   => array('method' => true),
    );
}

/**
 * Render calendar subscription buttons.
 *
 * @param array<string, mixed> $args Optional args.
 * @return string
 */
function maca_njuvs_render_info_calendar_subscribe($args = array()) {
    $args = is_array($args) ? $args : array();
    $preview = !empty($args['preview']);
    $compact = !empty($args['compact']);

    if (!$preview && !maca_njuvs_enabled()) {
        return '';
    }

    if ($preview && !maca_njuvs_enabled()) {
        return '';
    }

    maca_njuvs_info_hub_enqueue_assets();

    $ics_url = maca_njuvs_get_info_events_ics_url();
    $webcal_url = maca_njuvs_get_info_events_webcal_url();
    $google_url = maca_njuvs_get_info_events_google_calendar_url();
    $uid = 'maca-info-subscribe-' . wp_unique_id();

    ob_start();
    $wrapper_classes = array('maca-info-hub', 'maca-info-calendar-subscribe');

    if ($compact) {
        $wrapper_classes[] = 'maca-info-calendar-subscribe--compact';
    }

    $dialog_id = $uid . '-dialog';
    ?>
    <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>" id="<?php echo esc_attr($uid); ?>">
        <?php if ($compact) : ?>
            <button type="button" class="maca-info-subscribe-btn maca-info-subscribe-btn--compact maca-info-subscribe-open" aria-haspopup="dialog" aria-controls="<?php echo esc_attr($dialog_id); ?>">
                <?php esc_html_e('Subscribe', 'maca-njuvs'); ?>
            </button>
            <dialog class="maca-info-calendar-subscribe-dialog" id="<?php echo esc_attr($dialog_id); ?>">
                <div class="maca-info-calendar-subscribe-dialog-inner">
                    <h3 class="maca-info-calendar-subscribe-dialog-title"><?php esc_html_e('Subscribe to events', 'maca-njuvs'); ?></h3>
                    <p class="maca-info-calendar-subscribe-dialog-lead"><?php esc_html_e('The calendar updates automatically when events change in maca Njuvs.', 'maca-njuvs'); ?></p>
                    <ol class="maca-info-calendar-subscribe-dialog-steps">
                        <li><?php esc_html_e('On iPhone/iPad: tap “Subscribe in calendar app”, then open Calendar → Calendars and make sure the subscribed calendar is checked.', 'maca-njuvs'); ?></li>
                        <li><?php esc_html_e('On Android or desktop: use Google Calendar or copy the feed URL into your calendar app.', 'maca-njuvs'); ?></li>
                    </ol>
                    <div class="maca-info-calendar-subscribe-actions">
                        <a class="maca-info-subscribe-btn maca-info-subscribe-btn--primary" href="<?php echo esc_url($webcal_url); ?>">
                            <?php esc_html_e('Subscribe in calendar app', 'maca-njuvs'); ?>
                        </a>
                        <a class="maca-info-subscribe-btn" href="<?php echo esc_url($google_url); ?>" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e('Google Calendar', 'maca-njuvs'); ?>
                        </a>
                        <button type="button" class="maca-info-subscribe-btn maca-info-subscribe-copy" data-feed-url="<?php echo esc_attr($ics_url); ?>">
                            <?php esc_html_e('Copy feed URL', 'maca-njuvs'); ?>
                        </button>
                    </div>
                    <form method="dialog">
                        <button type="submit" class="maca-info-subscribe-btn maca-info-subscribe-dialog-close"><?php esc_html_e('Close', 'maca-njuvs'); ?></button>
                    </form>
                </div>
            </dialog>
        <?php else : ?>
            <p class="maca-info-calendar-subscribe-label"><?php esc_html_e('Subscribe to events', 'maca-njuvs'); ?></p>
            <div class="maca-info-calendar-subscribe-actions">
                <a class="maca-info-subscribe-btn maca-info-subscribe-btn--primary" href="<?php echo esc_url($webcal_url); ?>">
                    <?php esc_html_e('Subscribe in calendar app', 'maca-njuvs'); ?>
                </a>
                <a class="maca-info-subscribe-btn" href="<?php echo esc_url($google_url); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Google Calendar', 'maca-njuvs'); ?>
                </a>
                <button type="button" class="maca-info-subscribe-btn maca-info-subscribe-copy" data-feed-url="<?php echo esc_attr($ics_url); ?>">
                    <?php esc_html_e('Copy feed URL', 'maca-njuvs'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

/**
 * Shortcode for calendar subscription buttons.
 *
 * @return string
 */
function maca_njuvs_info_hub_shortcode_calendar_subscribe() {
    return maca_njuvs_render_info_calendar_subscribe();
}

add_action('init', 'maca_njuvs_info_hub_register_ical_shortcode');

/**
 * Register subscribe shortcode.
 *
 * @return void
 */
function maca_njuvs_info_hub_register_ical_shortcode() {
    add_shortcode('maca_njuvs_calendar_subscribe', 'maca_njuvs_info_hub_shortcode_calendar_subscribe');
}
