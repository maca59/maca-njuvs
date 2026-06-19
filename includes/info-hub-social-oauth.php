<?php
/**
 * Meta OAuth connection for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'maca_menulist_info_hub_meta_oauth_admin_init', 5);
add_action('rest_api_init', 'maca_menulist_info_hub_meta_oauth_register_rest_routes');

/**
 * Handle OAuth redirects and callbacks in admin.
 *
 * @return void
 */
function maca_menulist_info_hub_meta_oauth_admin_init() {
    if (!is_admin() || !maca_menulist_user_can_manage_secrets()) {
        return;
    }

    if (!function_exists('maca_menulist_info_hub_admin_page')) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $oauth = isset($_GET['maca_meta_oauth']) ? sanitize_key(wp_unslash($_GET['maca_meta_oauth'])) : '';

    if ($oauth === '') {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

    if ($page !== maca_menulist_info_hub_admin_page()) {
        return;
    }

    if ($oauth === 'start') {
        maca_menulist_info_hub_meta_oauth_start();
    }
}

/**
 * Register public OAuth callback route (avoids wp-admin redirect issues with Meta).
 *
 * @return void
 */
function maca_menulist_info_hub_meta_oauth_register_rest_routes() {
    register_rest_route(
        'maca-njuvs/v1',
        '/info-hub/meta-oauth/callback',
        array(
            'methods'             => 'GET',
            'callback'            => 'maca_menulist_info_hub_meta_oauth_rest_callback',
            'permission_callback' => '__return_true',
        )
    );
}

/**
 * REST OAuth callback — state links the request to the admin who started login.
 *
 * @return void
 */
function maca_menulist_info_hub_meta_oauth_rest_callback() {
    maca_menulist_info_hub_meta_oauth_callback();
}

/**
 * Transient key for OAuth state tied to a WordPress user ID.
 *
 * @param int $user_id User ID.
 * @return string
 */
function maca_menulist_info_hub_meta_oauth_state_transient_key($user_id) {
    return 'maca_njuvs_meta_oauth_state_' . absint($user_id);
}

/**
 * Transient key that maps OAuth state to the initiating admin user.
 *
 * @param string $state OAuth state.
 * @return string
 */
function maca_menulist_info_hub_meta_oauth_user_lookup_transient_key($state) {
    return 'maca_njuvs_meta_oauth_uid_' . hash('sha256', 'maca_njuvs_meta_oauth|' . (string) $state);
}

/**
 * Begin Facebook OAuth.
 *
 * @return void
 */
function maca_menulist_info_hub_meta_oauth_start() {
    if (!maca_menulist_info_hub_meta_has_app_credentials()) {
        wp_die(esc_html__('Enter Meta App ID and App Secret first.', 'maca-njuvs'));
    }

    $user_id = get_current_user_id();
    $state = wp_create_nonce('maca_njuvs_meta_oauth');

    set_transient(maca_menulist_info_hub_meta_oauth_state_transient_key($user_id), $state, 15 * MINUTE_IN_SECONDS);
    set_transient(maca_menulist_info_hub_meta_oauth_user_lookup_transient_key($state), $user_id, 15 * MINUTE_IN_SECONDS);

    $url = maca_menulist_info_hub_meta_oauth_authorize_url($state);

    nocache_headers();
    // Meta OAuth requires an external redirect; wp_safe_redirect() blocks off-site hosts.
    wp_redirect($url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
    exit;
}

/**
 * Build the Meta OAuth authorize URL with a fully encoded redirect_uri.
 *
 * @param string $state OAuth state nonce.
 * @return string
 */
function maca_menulist_info_hub_meta_oauth_authorize_url($state) {
    $params = array(
        'client_id' => maca_menulist_info_hub_meta_get_app_id(),
        'redirect_uri' => maca_menulist_info_hub_meta_oauth_redirect_uri(),
        'state' => (string) $state,
        'scope' => maca_menulist_info_hub_meta_oauth_scopes(),
        'response_type' => 'code',
    );

    $base = 'https://www.facebook.com/' . MACA_MENULIST_INFO_HUB_META_GRAPH_VERSION . '/dialog/oauth';

    return $base . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

/**
 * OAuth callback — exchange code and store page list for selection.
 *
 * @return void
 */
function maca_menulist_info_hub_meta_oauth_callback() {
    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    $error = isset($_GET['error_description']) ? sanitize_text_field(wp_unslash($_GET['error_description'])) : '';
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    $user_id = 0;

    if ($state !== '') {
        $user_id = (int) get_transient(maca_menulist_info_hub_meta_oauth_user_lookup_transient_key($state));
    }

    if ($user_id <= 0 && is_user_logged_in()) {
        $user_id = get_current_user_id();
    }

    if ($user_id <= 0) {
        wp_die(esc_html__('Invalid OAuth state. Try connecting again.', 'maca-njuvs'));
    }

    $user = get_userdata($user_id);

    if (
        !$user
        || (
            !user_can($user_id, 'manage_options')
            && !user_can($user_id, MACA_MENULIST_CAP_MANAGE_SECRETS)
        )
    ) {
        wp_die(esc_html__('You do not have permission to connect Facebook for this site.', 'maca-njuvs'));
    }

    $expected = get_transient(maca_menulist_info_hub_meta_oauth_state_transient_key($user_id));

    if ($expected === false || $state === '' || !hash_equals((string) $expected, $state)) {
        wp_die(esc_html__('Invalid OAuth state. Try connecting again.', 'maca-njuvs'));
    }

    delete_transient(maca_menulist_info_hub_meta_oauth_state_transient_key($user_id));
    delete_transient(maca_menulist_info_hub_meta_oauth_user_lookup_transient_key($state));

    if ($code === '') {
        wp_die(esc_html($error !== '' ? $error : __('Facebook authorization was cancelled.', 'maca-njuvs')));
    }

    $token_result = maca_menulist_info_hub_meta_graph_request(
        'oauth/access_token',
        array(
            'client_id' => maca_menulist_info_hub_meta_get_app_id(),
            'client_secret' => maca_menulist_info_hub_meta_get_app_secret(),
            'redirect_uri' => maca_menulist_info_hub_meta_oauth_redirect_uri(),
            'code' => $code,
        ),
        'GET'
    );

    if (!$token_result['ok'] || empty($token_result['body']['access_token'])) {
        wp_die(esc_html($token_result['error']));
    }

    $short_token = (string) $token_result['body']['access_token'];

    $long_result = maca_menulist_info_hub_meta_graph_request(
        'oauth/access_token',
        array(
            'grant_type' => 'fb_exchange_token',
            'client_id' => maca_menulist_info_hub_meta_get_app_id(),
            'client_secret' => maca_menulist_info_hub_meta_get_app_secret(),
            'fb_exchange_token' => $short_token,
        ),
        'GET'
    );

    if (!$long_result['ok'] || empty($long_result['body']['access_token'])) {
        wp_die(esc_html($long_result['error']));
    }

    $user_token = (string) $long_result['body']['access_token'];
    $expires_in = isset($long_result['body']['expires_in']) ? (int) $long_result['body']['expires_in'] : 0;

    maca_menulist_info_hub_meta_set('user_token', maca_menulist_info_hub_encrypt_secret($user_token));

    if ($expires_in > 0) {
        maca_menulist_info_hub_meta_set('token_expires', time() + $expires_in);
    }

    $pages_result = maca_menulist_info_hub_meta_graph_request(
        'me/accounts',
        array(
            'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            'limit' => 100,
        ),
        'GET',
        $user_token
    );

    if (!$pages_result['ok'] || empty($pages_result['body']['data']) || !is_array($pages_result['body']['data'])) {
        wp_die(esc_html($pages_result['error'] !== '' ? $pages_result['error'] : __('No Facebook pages found for this account.', 'maca-njuvs')));
    }

    set_transient(
        'maca_njuvs_meta_pages_' . $user_id,
        $pages_result['body']['data'],
        15 * MINUTE_IN_SECONDS
    );

    if (!is_user_logged_in() || get_current_user_id() !== $user_id) {
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, false, is_ssl());
    }

    wp_safe_redirect(
        maca_menulist_info_hub_admin_url(
            'social',
            array(
                'maca_meta_oauth' => 'select_page',
            )
        )
    );
    exit;
}

/**
 * Refresh stored page token from /me/accounts using user token.
 *
 * @return bool
 */
function maca_menulist_info_hub_meta_refresh_page_token() {
    $page_id = (string) maca_menulist_info_hub_meta_get('page_id', '');
    $user_token = maca_menulist_info_hub_meta_get_user_token();

    if ($page_id === '' || $user_token === '') {
        return false;
    }

    $pages_result = maca_menulist_info_hub_meta_graph_request(
        'me/accounts',
        array(
            'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            'limit' => 100,
        ),
        'GET',
        $user_token
    );

    if (!$pages_result['ok'] || empty($pages_result['body']['data'])) {
        return false;
    }

    foreach ($pages_result['body']['data'] as $page) {
        if (!is_array($page) || (string) ($page['id'] ?? '') !== $page_id) {
            continue;
        }

        maca_menulist_info_hub_meta_store_page_connection($page);
        return true;
    }

    return false;
}

/**
 * Persist selected Facebook page + linked Instagram account.
 *
 * @param array<string, mixed> $page Page payload from Graph API.
 * @return void
 */
function maca_menulist_info_hub_meta_store_page_connection($page) {
    $page_id = (string) ($page['id'] ?? '');
    $page_name = (string) ($page['name'] ?? '');
    $page_token = (string) ($page['access_token'] ?? '');

    maca_menulist_info_hub_meta_set('page_id', $page_id);
    maca_menulist_info_hub_meta_set('page_name', $page_name);
    maca_menulist_info_hub_meta_set('page_token', maca_menulist_info_hub_encrypt_secret($page_token));

    $ig_id = '';
    $ig_username = '';

    if (!empty($page['instagram_business_account']) && is_array($page['instagram_business_account'])) {
        $ig_id = (string) ($page['instagram_business_account']['id'] ?? '');
        $ig_username = (string) ($page['instagram_business_account']['username'] ?? '');
    }

    maca_menulist_info_hub_meta_set('ig_user_id', $ig_id);
    maca_menulist_info_hub_meta_set('ig_username', $ig_username);
}

/**
 * Save selected page from admin form.
 *
 * @param string $page_id Facebook page ID.
 * @return bool
 */
function maca_menulist_info_hub_meta_select_page($page_id) {
    $pages = get_transient('maca_njuvs_meta_pages_' . get_current_user_id());

    if (!is_array($pages)) {
        return false;
    }

    $page_id = (string) $page_id;

    foreach ($pages as $page) {
        if (!is_array($page) || (string) ($page['id'] ?? '') !== $page_id) {
            continue;
        }

        maca_menulist_info_hub_meta_store_page_connection($page);
        delete_transient('maca_njuvs_meta_pages_' . get_current_user_id());
        return true;
    }

    return false;
}

/**
 * Send a test post to connected channels.
 *
 * @return array{facebook: string, instagram: string}
 */
function maca_menulist_info_hub_meta_test_publish() {
    $caption = sprintf(
        /* translators: %s: site name */
        __('Test post from %s via maca Njuvs.', 'maca-njuvs'),
        get_bloginfo('name')
    );

    $results = array(
        'facebook' => '',
        'instagram' => '',
    );

    $fb = maca_menulist_info_hub_publish_facebook($caption, '');
    $results['facebook'] = $fb['ok']
        ? __('Facebook test post published.', 'maca-njuvs')
        : $fb['error'];

    if (maca_menulist_info_hub_meta_has_instagram()) {
        $image = maca_menulist_info_hub_meta_get('test_image_url', '');

        if ($image === '') {
            $results['instagram'] = __('Add a test image URL in Social settings to test Instagram.', 'maca-njuvs');
        } else {
            $ig = maca_menulist_info_hub_publish_instagram($caption, $image);
            $results['instagram'] = $ig['ok']
                ? __('Instagram test post published.', 'maca-njuvs')
                : $ig['error'];
        }
    }

    return $results;
}
