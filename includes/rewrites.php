<?php
/**
 * Rewrite rules for maca Njuvs (iCal feed).
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register maca Njuvs rewrite rules.
 *
 * @return void
 */
function maca_njuvs_register_rewrites() {
    $ics_file = maca_njuvs_get_info_events_ics_filename();

    add_rewrite_rule(
        '^' . preg_quote($ics_file, '/') . '/?$',
        'index.php?' . maca_njuvs_ics_query_var() . '=1',
        'top'
    );
}

/**
 * iCal query var name.
 *
 * @return string
 */
function maca_njuvs_ics_query_var() {
    return 'maca_njuvs_events_ics';
}

/**
 * Register custom query vars.
 *
 * @param array<int, string> $vars Query vars.
 * @return array<int, string>
 */
function maca_njuvs_query_vars($vars) {
    if (!is_array($vars)) {
        $vars = array();
    }

    $vars[] = maca_njuvs_ics_query_var();

    return $vars;
}

/**
 * Current request path without leading/trailing slashes.
 *
 * @return string
 */
function maca_njuvs_get_request_path() {
    global $wp;

    $path = '';

    if (isset($wp->request) && (string) $wp->request !== '') {
        $path = trim((string) $wp->request, '/');
    }

    if ($path === '') {
        $uri_path = '';

        if (isset($_SERVER['REQUEST_URI'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $request_uri = wp_unslash($_SERVER['REQUEST_URI']);
            $uri_path = sanitize_text_field(
                (string) wp_parse_url($request_uri, PHP_URL_PATH)
            );
        }

        $uri_path = trim($uri_path, '/');

        if (strpos($uri_path, 'index.php/') === 0) {
            $uri_path = trim(substr($uri_path, strlen('index.php/')), '/');
        } elseif ($uri_path === 'index.php') {
            $uri_path = '';
        }

        $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

        if ($home_path !== '' && strpos($uri_path, $home_path) === 0) {
            $uri_path = trim(substr($uri_path, strlen($home_path)), '/');
        }

        $path = $uri_path;
    }

    return strtolower($path);
}

/**
 * Whether a request path ends with the given slug.
 *
 * @param string $path Request path.
 * @param string $slug Target slug.
 * @return bool
 */
function maca_njuvs_request_path_matches_slug($path, $slug) {
    $path = strtolower(trim((string) $path, '/'));
    $slug = strtolower(trim((string) $slug, '/'));

    if ($path === '' || $slug === '') {
        return false;
    }

    if ($path === $slug) {
        return true;
    }

    if (strlen($path) <= strlen($slug) || substr($path, -strlen($slug)) !== $slug) {
        return false;
    }

    $prefix = substr($path, 0, -strlen($slug));

    return $prefix !== '' && substr($prefix, -1) === '/';
}

add_action('init', 'maca_njuvs_register_rewrites', 10);
add_filter('query_vars', 'maca_njuvs_query_vars');

/**
 * Force HTTPS for media URLs when the site is served over SSL.
 *
 * @param string $url Raw URL.
 * @return string
 */
function maca_njuvs_normalize_url($url) {
    if ($url === '') {
        return '';
    }

    $url = trim((string) $url);

    if (is_ssl() || wp_parse_url(home_url(), PHP_URL_SCHEME) === 'https') {
        return set_url_scheme($url, 'https');
    }

    return $url;
}

/**
 * Schedule a rewrite flush on the next request.
 *
 * @return void
 */
function maca_njuvs_schedule_rewrite_flush() {
    update_option('maca_njuvs_flush_rewrite_rules', '1', false);
}

/**
 * Flush rewrite rules once after settings changes.
 *
 * @return void
 */
function maca_njuvs_maybe_flush_rewrite_rules() {
    if (get_option('maca_njuvs_flush_rewrite_rules') !== '1') {
        return;
    }

    delete_option('maca_njuvs_flush_rewrite_rules');
    flush_rewrite_rules(false);
}

add_action('init', 'maca_njuvs_maybe_flush_rewrite_rules', 99);
