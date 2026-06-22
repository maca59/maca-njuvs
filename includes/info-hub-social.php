<?php
/**
 * Meta Graph API publishing for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('MACA_NJUVS_META_GRAPH_VERSION')) {
    define('MACA_NJUVS_META_GRAPH_VERSION', 'v21.0');
}

if (!defined('MACA_NJUVS_SOCIAL_CRON_HOOK')) {
    define('MACA_NJUVS_SOCIAL_CRON_HOOK', 'maca_njuvs_social_cron');
}

/**
 * Required OAuth scopes for page + Instagram publishing.
 *
 * @return string
 */
function maca_njuvs_info_hub_meta_oauth_scopes() {
    return implode(
        ',',
        array(
            'pages_show_list',
            'pages_manage_posts',
            'pages_read_engagement',
            'instagram_basic',
            'instagram_content_publish',
            'business_management',
        )
    );
}

/**
 * @param string $option Option suffix.
 * @return string
 */
function maca_njuvs_info_hub_meta_option_name($option) {
    return 'maca_njuvs_meta_' . $option;
}

/**
 * @param string $key     Option key without prefix.
 * @param mixed  $default Default value.
 * @return mixed
 */
function maca_njuvs_info_hub_meta_get($key, $default = '') {
    return get_option(maca_njuvs_info_hub_meta_option_name($key), $default);
}

/**
 * @param string $key   Option key without prefix.
 * @param mixed  $value Value.
 * @return void
 */
function maca_njuvs_info_hub_meta_set($key, $value) {
    update_option(maca_njuvs_info_hub_meta_option_name($key), $value, false);
}

/**
 * @return string
 */
function maca_njuvs_info_hub_meta_get_app_id() {
    return (string) maca_njuvs_info_hub_meta_get('app_id', '');
}

/**
 * @return string
 */
function maca_njuvs_info_hub_meta_get_app_secret() {
    return maca_njuvs_info_hub_decrypt_secret((string) maca_njuvs_info_hub_meta_get('app_secret', ''));
}

/**
 * @return string
 */
function maca_njuvs_info_hub_meta_get_page_token() {
    return maca_njuvs_info_hub_decrypt_secret((string) maca_njuvs_info_hub_meta_get('page_token', ''));
}

/**
 * @return string
 */
function maca_njuvs_info_hub_meta_get_user_token() {
    return maca_njuvs_info_hub_decrypt_secret((string) maca_njuvs_info_hub_meta_get('user_token', ''));
}

/**
 * Whether Meta app credentials are configured.
 *
 * @return bool
 */
function maca_njuvs_info_hub_meta_has_app_credentials() {
    return maca_njuvs_info_hub_meta_get_app_id() !== '' && maca_njuvs_info_hub_meta_get_app_secret() !== '';
}

/**
 * Whether Facebook page is connected.
 *
 * @return bool
 */
function maca_njuvs_info_hub_meta_is_connected() {
    return maca_njuvs_info_hub_meta_get('page_id', '') !== ''
        && maca_njuvs_info_hub_meta_get_page_token() !== '';
}

/**
 * Whether Instagram Business is linked to the connected page.
 *
 * @return bool
 */
function maca_njuvs_info_hub_meta_has_instagram() {
    return maca_njuvs_info_hub_meta_get('ig_user_id', '') !== '';
}

/**
 * OAuth redirect URI registered in the Meta app.
 *
 * @return string
 */
function maca_njuvs_info_hub_meta_oauth_redirect_uri() {
    return rest_url('maca-njuvs/v1/info-hub/meta-oauth/callback');
}

/**
 * @param string $path   Graph path.
 * @param array<string, mixed> $params Query/body params.
 * @param string $method HTTP method.
 * @param string $token  Access token.
 * @return array{ok: bool, code: int, body: array<string, mixed>|null, error: string}
 */
function maca_njuvs_info_hub_meta_graph_request($path, $params = array(), $method = 'GET', $token = '') {
    $path = ltrim((string) $path, '/');
    $url = 'https://graph.facebook.com/' . MACA_NJUVS_META_GRAPH_VERSION . '/' . $path;

    $args = array(
        'timeout' => 30,
        'method' => strtoupper($method),
    );

    if ($token !== '') {
        $params['access_token'] = $token;
    }

    if ($args['method'] === 'GET') {
        $url = add_query_arg($params, $url);
    } else {
        $args['body'] = $params;
    }

    $response = wp_remote_request($url, $args);

    if (is_wp_error($response)) {
        return array(
            'ok' => false,
            'code' => 0,
            'body' => null,
            'error' => $response->get_error_message(),
        );
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $raw = wp_remote_retrieve_body($response);
    $body = json_decode($raw, true);

    if (!is_array($body)) {
        $body = array();
    }

    if ($code >= 200 && $code < 300 && !isset($body['error'])) {
        return array(
            'ok' => true,
            'code' => $code,
            'body' => $body,
            'error' => '',
        );
    }

    $error = isset($body['error']['message']) ? (string) $body['error']['message'] : __('Meta API request failed.', 'maca-njuvs');

    return array(
        'ok' => false,
        'code' => $code,
        'body' => $body,
        'error' => $error,
    );
}

/**
 * Log a social publish attempt.
 *
 * @param string $object_type news|event.
 * @param int    $object_id   Object ID.
 * @param string $channel     facebook|instagram.
 * @param string $status      Status.
 * @param string $external_id Remote ID.
 * @param string $message     Log message.
 * @return void
 */
function maca_njuvs_info_hub_social_log($object_type, $object_id, $channel, $status, $external_id = '', $message = '') {
    if (function_exists('maca_njuvs_db_insert_info_social_log')) {
        maca_njuvs_db_insert_info_social_log(
            array(
                'object_type' => $object_type,
                'object_id' => $object_id,
                'channel' => $channel,
                'status' => $status,
                'external_id' => $external_id,
                'message' => $message,
            )
        );
    }
}

/**
 * Update social status columns on news or event row.
 *
 * @param string $object_type news|event.
 * @param int    $object_id   Object ID.
 * @param string $channel     facebook|instagram.
 * @param string $status      Status key.
 * @param string $external_id Remote post ID.
 * @return void
 */
function maca_njuvs_info_hub_update_social_status($object_type, $object_id, $channel, $status, $external_id = '') {
    $object_id = intval($object_id);
    $status = sanitize_key($status);
    $field_status = $channel === 'instagram' ? 'social_ig_status' : 'social_fb_status';
    $field_id = $channel === 'instagram' ? 'social_ig_media_id' : 'social_fb_post_id';
    $row = array(
        $field_status => $status,
    );

    if ($external_id !== '') {
        $row[ $field_id ] = $external_id;
    }

    if ($object_type === 'news') {
        maca_njuvs_db_update_info_news($object_id, $row);
    } elseif ($object_type === 'event') {
        maca_njuvs_db_update_info_event($object_id, $row);
    }
}

/**
 * Build Facebook/Instagram caption for news.
 *
 * @param object $news News row.
 * @return string
 */
function maca_njuvs_info_hub_social_news_caption($news, $apply_channel_limits = true) {
    $title = trim(maca_njuvs_info_hub_get_news_title($news));
    $excerpt_plain = maca_njuvs_info_hub_rich_text_to_plain(
        maca_njuvs_get_object_bilingual_field($news, 'excerpt', 'excerpt_en')
    );
    $content_plain = maca_njuvs_info_hub_get_news_content_plain($news);
    $parts = array();

    if ($title !== '') {
        $parts[] = $title;
    }

    if ($excerpt_plain !== '' && $content_plain !== '' && $excerpt_plain === $content_plain) {
        $parts[] = $content_plain;
    } else {
        if ($excerpt_plain !== '' && $excerpt_plain !== $title) {
            $parts[] = $excerpt_plain;
        }

        if ($content_plain !== '' && $content_plain !== $title && $content_plain !== $excerpt_plain) {
            $parts[] = $content_plain;
        }
    }

    $caption = implode("\n\n", $parts);

    if (!$apply_channel_limits) {
        return $caption;
    }

    return maca_njuvs_info_hub_social_apply_instagram_caption_limit($caption);
}

/**
 * Build a news row object from raw admin fields (preview before save).
 *
 * @param array<string, string> $fields title, excerpt, content.
 * @return object
 */
function maca_njuvs_info_hub_social_news_row_from_fields($fields) {
    return (object) array(
        'title' => isset($fields['title']) ? (string) $fields['title'] : '',
        'title_en' => '',
        'excerpt' => isset($fields['excerpt']) ? (string) $fields['excerpt'] : '',
        'excerpt_en' => '',
        'content' => isset($fields['content']) ? (string) $fields['content'] : '',
        'content_en' => '',
    );
}

/**
 * Instagram caption character limit.
 *
 * @return int
 */
function maca_njuvs_info_hub_social_instagram_caption_limit() {
    return 2200;
}

/**
 * Measure caption length (multibyte-safe when available).
 *
 * @param string $caption Caption text.
 * @return int
 */
function maca_njuvs_info_hub_social_caption_length($caption) {
    return function_exists('mb_strlen')
        ? (int) mb_strlen($caption)
        : (int) strlen($caption);
}

/**
 * Caption length for news fields before save.
 *
 * @param array<string, string> $fields title, excerpt, content.
 * @return int
 */
function maca_njuvs_info_hub_social_news_caption_length($fields) {
    if (!function_exists('maca_njuvs_info_hub_social_news_row_from_fields')) {
        return 0;
    }

    $row = maca_njuvs_info_hub_social_news_row_from_fields($fields);
    $caption = maca_njuvs_info_hub_social_news_caption($row, false);

    return maca_njuvs_info_hub_social_caption_length($caption);
}

/**
 * Instagram caption limit helper.
 *
 * @param string $caption Caption text.
 * @return string
 */
function maca_njuvs_info_hub_social_apply_instagram_caption_limit($caption) {
    $limit = maca_njuvs_info_hub_social_instagram_caption_limit();

    if (function_exists('mb_strlen') && mb_strlen($caption) > $limit) {
        return mb_substr($caption, 0, $limit - 3) . '…';
    }

    if (strlen($caption) > $limit) {
        return substr($caption, 0, $limit - 3) . '…';
    }

    return $caption;
}

/**
 * Caption metadata for admin preview.
 *
 * @param string $caption Full caption.
 * @return array{caption: string, sent_caption: string, length: int, sent_length: int, instagram_limit: int, instagram_truncated: bool}
 */
function maca_njuvs_info_hub_social_caption_preview_meta($caption) {
    $sent = maca_njuvs_info_hub_social_apply_instagram_caption_limit($caption);
    $length = function_exists('mb_strlen') ? mb_strlen($caption) : strlen($caption);
    $sent_length = function_exists('mb_strlen') ? mb_strlen($sent) : strlen($sent);

    return array(
        'caption' => $caption,
        'sent_caption' => $sent,
        'length' => (int) $length,
        'sent_length' => (int) $sent_length,
        'instagram_limit' => maca_njuvs_info_hub_social_instagram_caption_limit(),
        'instagram_truncated' => $sent !== $caption,
    );
}

/**
 * Build Facebook/Instagram caption for event (first occurrence only).
 *
 * @param object $event Event row.
 * @return string
 */
function maca_njuvs_info_hub_social_event_caption($event) {
    $title = maca_njuvs_info_hub_get_event_title($event);
    $when = maca_njuvs_info_hub_format_event_datetime($event);
    $location = maca_njuvs_info_hub_get_event_location($event);
    $description = maca_njuvs_info_hub_get_event_description($event);
    $parts = array($title);

    if ($when !== '') {
        $parts[] = $when;
    }

    if ($location !== '') {
        $parts[] = $location;
    }

    if ($description !== '') {
        $parts[] = wp_strip_all_tags($description);
    }

    return implode("\n\n", array_filter($parts));
}

/**
 * @param object $row News or event row.
 * @return string
 */
function maca_njuvs_info_hub_social_image_url($row) {
    return !empty($row->image_url) ? esc_url_raw((string) $row->image_url) : '';
}

/**
 * Whether social publish should run for a channel.
 *
 * @param object $row     Row object.
 * @param string $channel facebook|instagram.
 * @return bool
 */
function maca_njuvs_info_hub_should_publish_social($row, $channel) {
    $share_key = $channel === 'instagram' ? 'share_instagram' : 'share_facebook';
    $status_key = $channel === 'instagram' ? 'social_ig_status' : 'social_fb_status';

    if (empty($row->{$share_key})) {
        return false;
    }

    $status = isset($row->{$status_key}) ? (string) $row->{$status_key} : 'skipped';

    return in_array($status, array('skipped', 'pending', 'failed'), true);
}

/**
 * Whether news is ready for web/social publish now.
 *
 * @param object $news News row.
 * @return bool
 */
function maca_njuvs_info_hub_news_ready_to_publish($news) {
    if (!$news) {
        return false;
    }

    $status = (string) $news->status;

    if ($status === 'published') {
        return true;
    }

    if ($status === 'scheduled') {
        $now = function_exists('maca_njuvs_wp_now_mysql') ? maca_njuvs_wp_now_mysql() : current_time('mysql');
        $publish_at = (string) ($news->publish_at ?? '');

        return $publish_at !== '' && $publish_at <= $now;
    }

    return false;
}

/**
 * Publish to Facebook page.
 *
 * @param string $caption  Post text.
 * @param string $image_url Optional image URL.
 * @return array{ok: bool, id: string, error: string}
 */
function maca_njuvs_info_hub_publish_facebook($caption, $image_url = '') {
    $page_id = (string) maca_njuvs_info_hub_meta_get('page_id', '');
    $token = maca_njuvs_info_hub_meta_get_page_token();

    if ($page_id === '' || $token === '') {
        return array('ok' => false, 'id' => '', 'error' => __('Facebook is not connected.', 'maca-njuvs'));
    }

    if ($image_url !== '') {
        $result = maca_njuvs_info_hub_meta_graph_request(
            $page_id . '/photos',
            array(
                'url' => $image_url,
                'caption' => $caption,
                'published' => 'true',
            ),
            'POST',
            $token
        );
    } else {
        $result = maca_njuvs_info_hub_meta_graph_request(
            $page_id . '/feed',
            array(
                'message' => $caption,
            ),
            'POST',
            $token
        );
    }

    if (!$result['ok']) {
        return array('ok' => false, 'id' => '', 'error' => $result['error']);
    }

    $id = isset($result['body']['id']) ? (string) $result['body']['id'] : '';
    if ($id === '' && isset($result['body']['post_id'])) {
        $id = (string) $result['body']['post_id'];
    }

    return array('ok' => true, 'id' => $id, 'error' => '');
}

/**
 * Publish to Instagram Business account.
 *
 * @param string $caption   Caption.
 * @param string $image_url Public image URL.
 * @return array{ok: bool, id: string, error: string}
 */
function maca_njuvs_info_hub_publish_instagram($caption, $image_url) {
    $ig_user_id = (string) maca_njuvs_info_hub_meta_get('ig_user_id', '');
    $token = maca_njuvs_info_hub_meta_get_page_token();

    if ($ig_user_id === '' || $token === '') {
        return array('ok' => false, 'id' => '', 'error' => __('Instagram is not connected to the selected Facebook page.', 'maca-njuvs'));
    }

    if ($image_url === '') {
        return array('ok' => false, 'id' => '', 'error' => __('Instagram requires an image.', 'maca-njuvs'));
    }

    $create = maca_njuvs_info_hub_meta_graph_request(
        $ig_user_id . '/media',
        array(
            'image_url' => $image_url,
            'caption' => $caption,
        ),
        'POST',
        $token
    );

    if (!$create['ok'] || empty($create['body']['id'])) {
        return array('ok' => false, 'id' => '', 'error' => $create['error']);
    }

    $creation_id = (string) $create['body']['id'];
    $publish = maca_njuvs_info_hub_meta_graph_request(
        $ig_user_id . '/media_publish',
        array(
            'creation_id' => $creation_id,
        ),
        'POST',
        $token
    );

    if (!$publish['ok']) {
        return array('ok' => false, 'id' => '', 'error' => $publish['error']);
    }

    $id = isset($publish['body']['id']) ? (string) $publish['body']['id'] : $creation_id;

    return array('ok' => true, 'id' => $id, 'error' => '');
}

/**
 * Load a news or event row for social publishing.
 *
 * @param string $object_type news|event.
 * @param int    $object_id   Object ID.
 * @return object|null
 */
function maca_njuvs_info_hub_get_social_publish_row($object_type, $object_id) {
    $object_id = (int) $object_id;

    if ($object_type === 'news') {
        $row = maca_njuvs_db_get_info_news($object_id);

        if (!$row || !maca_njuvs_info_hub_news_ready_to_publish($row)) {
            return null;
        }

        return $row;
    }

    if ($object_type === 'event') {
        $row = maca_njuvs_db_get_info_event($object_id);

        if (!$row || empty($row->is_active)) {
            return null;
        }

        return $row;
    }

    return null;
}

/**
 * Channels that should be published for an object right now.
 *
 * @param string $object_type news|event.
 * @param int    $object_id   Object ID.
 * @return array<int, string>
 */
function maca_njuvs_info_hub_get_social_publish_channels($object_type, $object_id) {
    $row = maca_njuvs_info_hub_get_social_publish_row($object_type, $object_id);

    if (!$row) {
        return array();
    }

    $channels = array();

    foreach (array('facebook', 'instagram') as $channel) {
        if (maca_njuvs_info_hub_should_publish_social($row, $channel)) {
            $channels[] = $channel;
        }
    }

    return $channels;
}

/**
 * Publish one social channel for a news or event item.
 *
 * @param string $object_type news|event.
 * @param int    $object_id   Object ID.
 * @param string $channel     facebook|instagram.
 * @return array{ok: bool, channel: string, status: string, message: string, external_id: string}
 */
function maca_njuvs_info_hub_publish_social_channel($object_type, $object_id, $channel) {
    $channel = sanitize_key($channel);

    if (!in_array($channel, array('facebook', 'instagram'), true)) {
        return array(
            'ok' => false,
            'channel' => $channel,
            'status' => 'failed',
            'message' => __('Unknown social channel.', 'maca-njuvs'),
            'external_id' => '',
        );
    }

    if (function_exists('maca_njuvs_user_can_manage_info_hub_social') && !maca_njuvs_user_can_manage_info_hub_social()) {
        return array(
            'ok' => false,
            'channel' => $channel,
            'status' => 'failed',
            'message' => __('Facebook and Instagram publishing is not available for demo users.', 'maca-njuvs'),
            'external_id' => '',
        );
    }

    if (!maca_njuvs_info_hub_meta_is_connected()) {
        return array(
            'ok' => false,
            'channel' => $channel,
            'status' => 'failed',
            'message' => __('Facebook is not connected.', 'maca-njuvs'),
            'external_id' => '',
        );
    }

    $object_id = (int) $object_id;
    $row = maca_njuvs_info_hub_get_social_publish_row($object_type, $object_id);

    if (!$row) {
        return array(
            'ok' => false,
            'channel' => $channel,
            'status' => 'skipped',
            'message' => __('Nothing to publish to social media.', 'maca-njuvs'),
            'external_id' => '',
        );
    }

    if (!maca_njuvs_info_hub_should_publish_social($row, $channel)) {
        return array(
            'ok' => true,
            'channel' => $channel,
            'status' => 'skipped',
            'message' => '',
            'external_id' => '',
        );
    }

    $caption = $object_type === 'news'
        ? maca_njuvs_info_hub_social_news_caption($row)
        : maca_njuvs_info_hub_social_event_caption($row);
    $image_url = maca_njuvs_info_hub_social_image_url($row);

    maca_njuvs_info_hub_update_social_status($object_type, $object_id, $channel, 'pending');

    if ($channel === 'facebook') {
        $result = maca_njuvs_info_hub_publish_facebook($caption, $image_url);
    } else {
        $result = maca_njuvs_info_hub_publish_instagram($caption, $image_url);
    }

    if (!empty($result['ok'])) {
        maca_njuvs_info_hub_update_social_status($object_type, $object_id, $channel, 'published', $result['id']);
        maca_njuvs_info_hub_social_log($object_type, $object_id, $channel, 'published', $result['id'], '');

        return array(
            'ok' => true,
            'channel' => $channel,
            'status' => 'published',
            'message' => '',
            'external_id' => (string) $result['id'],
        );
    }

    $error = isset($result['error']) ? (string) $result['error'] : __('Publish failed.', 'maca-njuvs');
    maca_njuvs_info_hub_update_social_status($object_type, $object_id, $channel, 'failed');
    maca_njuvs_info_hub_social_log($object_type, $object_id, $channel, 'failed', '', $error);

    return array(
        'ok' => false,
        'channel' => $channel,
        'status' => 'failed',
        'message' => $error,
        'external_id' => '',
    );
}

/**
 * Queue deferred social publish for the current user (AJAX after save).
 *
 * @param string             $object_type news|event.
 * @param int                $object_id   Object ID.
 * @param array<int, string> $channels    Channels to publish.
 * @return void
 */
function maca_njuvs_info_hub_queue_deferred_social_publish($object_type, $object_id, $channels) {
    $user_id = get_current_user_id();

    if ($user_id <= 0 || empty($channels)) {
        return;
    }

    set_transient(
        'maca_njuvs_social_pending_' . $user_id,
        array(
            'object_type' => sanitize_key($object_type),
            'object_id' => (int) $object_id,
            'channels' => array_values(array_unique(array_map('sanitize_key', $channels))),
        ),
        10 * MINUTE_IN_SECONDS
    );
}

/**
 * Read and clear pending deferred social publish for the current user.
 *
 * @return array<string, mixed>|null
 */
function maca_njuvs_info_hub_consume_pending_social_publish() {
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return null;
    }

    $key = 'maca_njuvs_social_pending_' . $user_id;
    $pending = get_transient($key);

    if (!is_array($pending)) {
        return null;
    }

    delete_transient($key);

    return $pending;
}

/**
 * Publish social channels for a news or event item.
 *
 * @param string $object_type news|event.
 * @param int    $object_id   Object ID.
 * @return void
 */
function maca_njuvs_info_hub_publish_social_for_object($object_type, $object_id) {
    if (function_exists('maca_njuvs_user_can_manage_info_hub_social') && !maca_njuvs_user_can_manage_info_hub_social()) {
        return;
    }

    if (!maca_njuvs_info_hub_meta_is_connected()) {
        return;
    }

    foreach (maca_njuvs_info_hub_get_social_publish_channels($object_type, (int) $object_id) as $channel) {
        maca_njuvs_info_hub_publish_social_channel($object_type, (int) $object_id, $channel);
    }
}

/**
 * After save hook for news/events.
 *
 * @param string $object_type news|event.
 * @param int    $object_id   Object ID.
 * @return void
 */
function maca_njuvs_info_hub_maybe_publish_social($object_type, $object_id) {
    if (!maca_njuvs_enabled()) {
        return;
    }

    maca_njuvs_info_hub_publish_social_for_object($object_type, $object_id);
}

/**
 * Process scheduled news waiting for social publish.
 *
 * @return void
 */
function maca_njuvs_info_hub_social_process_scheduled_news() {
    if (!maca_njuvs_enabled() || !maca_njuvs_info_hub_meta_is_connected()) {
        return;
    }

    global $wpdb;

    $table = maca_njuvs_db_info_news_table();
    $now = function_exists('maca_njuvs_wp_now_mysql') ? maca_njuvs_wp_now_mysql() : current_time('mysql');

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is plugin-controlled.
    $sql = 'SELECT * FROM `' . $table . '`
        WHERE status IN (\'published\', \'scheduled\')
        AND (publish_at IS NULL OR publish_at <= %s)
        AND (share_facebook = 1 OR share_instagram = 1)
        ORDER BY publish_at ASC
        LIMIT 20';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
    $rows = $wpdb->get_results($wpdb->prepare($sql, $now));

    if (empty($rows)) {
        return;
    }

    foreach ($rows as $row) {
        if (!maca_njuvs_info_hub_news_ready_to_publish($row)) {
            continue;
        }

        maca_njuvs_info_hub_publish_social_for_object('news', (int) $row->id);
    }
}

/**
 * Refresh long-lived user token if expiring soon.
 *
 * @return void
 */
function maca_njuvs_info_hub_meta_maybe_refresh_token() {
    if (!maca_njuvs_info_hub_meta_has_app_credentials()) {
        return;
    }

    $expires = (int) maca_njuvs_info_hub_meta_get('token_expires', 0);
    $user_token = maca_njuvs_info_hub_meta_get_user_token();

    if ($user_token === '') {
        return;
    }

    if ($expires > 0 && $expires > (time() + WEEK_IN_SECONDS)) {
        return;
    }

    $app_id = maca_njuvs_info_hub_meta_get_app_id();
    $app_secret = maca_njuvs_info_hub_meta_get_app_secret();
    $result = maca_njuvs_info_hub_meta_graph_request(
        'oauth/access_token',
        array(
            'grant_type' => 'fb_exchange_token',
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'fb_exchange_token' => $user_token,
        ),
        'GET'
    );

    if (!$result['ok'] || empty($result['body']['access_token'])) {
        return;
    }

    $new_token = (string) $result['body']['access_token'];
    $expires_in = isset($result['body']['expires_in']) ? (int) $result['body']['expires_in'] : 0;

    maca_njuvs_info_hub_meta_set('user_token', maca_njuvs_info_hub_encrypt_secret($new_token));

    if ($expires_in > 0) {
        maca_njuvs_info_hub_meta_set('token_expires', time() + $expires_in);
    }

    if (function_exists('maca_njuvs_info_hub_meta_refresh_page_token')) {
        maca_njuvs_info_hub_meta_refresh_page_token();
    }
}

/**
 * Schedule social cron.
 *
 * @return void
 */
function maca_njuvs_info_hub_social_maybe_schedule_cron() {
    if (!maca_njuvs_info_hub_feature_available()) {
        return;
    }

    if (!wp_next_scheduled(MACA_NJUVS_SOCIAL_CRON_HOOK)) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', MACA_NJUVS_SOCIAL_CRON_HOOK);
    }
}

/**
 * Cron callback.
 *
 * @return void
 */
function maca_njuvs_info_hub_social_run_cron() {
    maca_njuvs_info_hub_meta_maybe_refresh_token();
    maca_njuvs_info_hub_social_process_scheduled_news();
}

add_action(MACA_NJUVS_SOCIAL_CRON_HOOK, 'maca_njuvs_info_hub_social_run_cron');
add_action('init', 'maca_njuvs_info_hub_social_maybe_schedule_cron', 25);

/**
 * Disconnect Meta account data.
 *
 * @return void
 */
function maca_njuvs_info_hub_meta_disconnect() {
    foreach (array('page_id', 'page_name', 'page_token', 'user_token', 'token_expires', 'ig_user_id', 'ig_username') as $key) {
        delete_option(maca_njuvs_info_hub_meta_option_name($key));
    }
}

/**
 * Admin label for social status.
 *
 * @param string $status Status key.
 * @return string
 */
function maca_njuvs_info_hub_social_status_label($status) {
    $labels = array(
        'skipped' => __('Skipped', 'maca-njuvs'),
        'pending' => __('Pending', 'maca-njuvs'),
        'published' => __('Published', 'maca-njuvs'),
        'failed' => __('Failed', 'maca-njuvs'),
    );

    return isset($labels[ $status ]) ? $labels[ $status ] : $status;
}

/**
 * Path to the localized Info Hub social setup guide markdown file.
 *
 * @return string
 */
function maca_njuvs_info_hub_social_guide_file() {
    return maca_njuvs_get_localized_doc_file('INFO-HUB-SOCIAL-GUIDE', 'en');
}

/**
 * Render the Info Hub Facebook/Instagram setup guide HTML.
 *
 * @return string
 */
function maca_njuvs_info_hub_render_social_guide_html() {
    if (!function_exists('maca_njuvs_render_markdown_file')) {
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/markdown.php';
    }

    $guide_file = maca_njuvs_info_hub_social_guide_file();

    if (!file_exists($guide_file)) {
        return '';
    }

    $html = maca_njuvs_render_markdown_file($guide_file);

    if ($html === '') {
        return '';
    }

    $redirect_uri = function_exists('maca_njuvs_info_hub_meta_oauth_redirect_uri')
        ? maca_njuvs_info_hub_meta_oauth_redirect_uri()
        : '';
    $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
    $site_host = is_string($site_host) ? $site_host : '';

    return str_replace(
        array(
            '{{OAUTH_REDIRECT_URI}}',
            '{{SITE_DOMAIN}}',
        ),
        array(
            esc_html($redirect_uri),
            esc_html($site_host),
        ),
        $html
    );
}
