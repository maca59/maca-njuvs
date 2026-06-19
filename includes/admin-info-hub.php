<?php
/**
 * Admin UI for maca Njuvs (news and events).
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin maca Njuvs controller.
 */
class Maca_Njuvs_Admin_Info_Hub {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 16);
        add_action('admin_init', array($this, 'handle_form_submission'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_maca_njuvs_publish_social_channel', array($this, 'ajax_publish_social_channel'));
        add_action('wp_ajax_maca_njuvs_preview_social_caption', array($this, 'ajax_preview_social_caption'));
    }

    /**
     * Register submenu.
     *
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            __('maca Njuvs', 'maca-njuvs'),
            __('maca Njuvs', 'maca-njuvs'),
            maca_menulist_admin_required_cap(),
            maca_menulist_info_hub_admin_page(),
            array($this, 'render_page'),
            maca_njuvs_get_admin_menu_icon(),
            58
        );
    }

    /**
     * Media uploader on edit screens.
     *
     * @param string $hook Admin hook suffix.
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        if ($page !== maca_menulist_info_hub_admin_page()) {
            return;
        }

        wp_enqueue_media();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'news';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

        if (in_array($tab, array('news', 'events'), true) && in_array($action, array('add', 'edit'), true)) {
            wp_enqueue_editor();
        }

        if (
            ($tab === 'social' && $this->user_can_manage_social())
            || (in_array($tab, array('news', 'events'), true) && in_array($action, array('add', 'edit'), true))
        ) {
            wp_enqueue_style(
                'maca-menulist-admin-info-hub-social-progress',
                MACA_MENULIST_PLUGIN_URL . 'assets/css/admin-info-hub-social-progress.css',
                array(),
                MACA_MENULIST_VERSION
            );

            wp_enqueue_script(
                'maca-menulist-admin-info-hub-social-progress',
                MACA_MENULIST_PLUGIN_URL . 'assets/js/admin-info-hub-social-progress.js',
                array('jquery'),
                MACA_MENULIST_VERSION,
                true
            );

            wp_localize_script(
                'maca-menulist-admin-info-hub-social-progress',
                'macaInfoHubSocialProgress',
                array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('maca_njuvs_social_progress'),
                    'pending' => function_exists('maca_menulist_info_hub_consume_pending_social_publish')
                        ? maca_menulist_info_hub_consume_pending_social_publish()
                        : null,
                    'labels' => array(
                        'title' => __('Publishing to social media', 'maca-njuvs'),
                        'previewTitle' => __('Preview social post', 'maca-njuvs'),
                        'previewIntro' => __('This is the text that will be sent to the selected channels.', 'maca-njuvs'),
                        'previewChannels' => __('Channels', 'maca-njuvs'),
                        'previewCaption' => __('Caption', 'maca-njuvs'),
                        'previewImage' => __('Image', 'maca-njuvs'),
                        'previewNoImage' => __('No image selected. Instagram requires an image.', 'maca-njuvs'),
                        'previewTruncated' => __('Instagram limits captions to 2,200 characters. The text below is truncated as it will be sent.', 'maca-njuvs'),
                        'previewChars' => __('Characters', 'maca-njuvs'),
                        'previewCancel' => __('Cancel', 'maca-njuvs'),
                        'previewConfirm' => __('Save and publish', 'maca-njuvs'),
                        'previewOnly' => __('Preview social post', 'maca-njuvs'),
                        'previewLoading' => __('Building preview…', 'maca-njuvs'),
                        'facebook' => __('Facebook', 'maca-njuvs'),
                        'instagram' => __('Instagram', 'maca-njuvs'),
                        'saving' => __('Saving…', 'maca-njuvs'),
                        'publishingFacebook' => __('Publishing to Facebook…', 'maca-njuvs'),
                        'publishingInstagram' => __('Publishing to Instagram…', 'maca-njuvs'),
                        'testFacebook' => __('Sending test post to Facebook…', 'maca-njuvs'),
                        'testInstagram' => __('Sending test post to Instagram…', 'maca-njuvs'),
                        'done' => __('Done!', 'maca-njuvs'),
                        'failed' => __('Something went wrong.', 'maca-njuvs'),
                        'wait' => __('This can take a little while — please keep this tab open.', 'maca-njuvs'),
                        'captionLimit' => function_exists('maca_menulist_info_hub_social_instagram_caption_limit')
                            ? maca_menulist_info_hub_social_instagram_caption_limit()
                            : 2200,
                        /* translators: 1: current caption length, 2: maximum caption length */
                        'captionCounter' => __('%1$d / %2$d characters', 'maca-njuvs'),
                        /* translators: %d: remaining characters */
                        'captionRemaining' => __('%d characters left', 'maca-njuvs'),
                        'captionOverLimit' => __('The social post text may not exceed 2,200 characters (Instagram limit).', 'maca-njuvs'),
                    ),
                )
            );
        }
    }

    /**
     * AJAX: publish one social channel after deferred save.
     *
     * @return void
     */
    public function ajax_publish_social_channel() {
        check_ajax_referer('maca_njuvs_social_progress', 'nonce');

        if (!maca_menulist_user_can_manage_plugin() || !$this->user_can_manage_social()) {
            wp_send_json_error(array('message' => __('Forbidden', 'maca-njuvs')), 403);
        }

        $object_type = isset($_POST['object_type']) ? sanitize_key(wp_unslash($_POST['object_type'])) : '';
        $object_id = isset($_POST['object_id']) ? absint(wp_unslash($_POST['object_id'])) : 0;
        $channel = isset($_POST['channel']) ? sanitize_key(wp_unslash($_POST['channel'])) : '';

        if (!in_array($object_type, array('news', 'event'), true) || $object_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid request.', 'maca-njuvs')), 400);
        }

        if (!function_exists('maca_menulist_info_hub_publish_social_channel')) {
            wp_send_json_error(array('message' => __('Social publishing is not available.', 'maca-njuvs')), 500);
        }

        $result = maca_menulist_info_hub_publish_social_channel($object_type, $object_id, $channel);

        if (!empty($result['ok'])) {
            wp_send_json_success($result);
        }

        wp_send_json_error($result);
    }

    /**
     * AJAX: build social caption preview from current form values.
     *
     * @return void
     */
    public function ajax_preview_social_caption() {
        check_ajax_referer('maca_njuvs_social_progress', 'nonce');

        if (!maca_menulist_user_can_manage_plugin() || !$this->user_can_manage_social()) {
            wp_send_json_error(array('message' => __('Forbidden', 'maca-njuvs')), 403);
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $object_type = isset($_POST['object_type']) ? sanitize_key(wp_unslash($_POST['object_type'])) : 'news';
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $excerpt = isset($_POST['excerpt'])
            ? maca_menulist_info_hub_sanitize_rich_text(wp_unslash($_POST['excerpt']))
            : '';
        $content = isset($_POST['content'])
            ? maca_menulist_info_hub_sanitize_rich_text(wp_unslash($_POST['content']))
            : '';
        $image_url = isset($_POST['image_url'])
            ? esc_url_raw(wp_unslash($_POST['image_url']))
            : '';
        $share_facebook = !empty($_POST['share_facebook']);
        $share_instagram = !empty($_POST['share_instagram']);
        $republish_facebook = !empty($_POST['republish_facebook']);
        $republish_instagram = !empty($_POST['republish_instagram']);
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ($object_type !== 'news') {
            wp_send_json_error(array('message' => __('Preview is only available for news.', 'maca-njuvs')), 400);
        }

        if (!function_exists('maca_menulist_info_hub_social_news_row_from_fields')) {
            wp_send_json_error(array('message' => __('Social preview is not available.', 'maca-njuvs')), 500);
        }

        $row = maca_menulist_info_hub_social_news_row_from_fields(
            array(
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
            )
        );
        $full_caption = maca_menulist_info_hub_social_news_caption($row, false);
        $meta = maca_menulist_info_hub_social_caption_preview_meta($full_caption);

        $channels = array();
        if ($share_facebook || $republish_facebook) {
            $channels[] = 'facebook';
        }
        if ($share_instagram || $republish_instagram) {
            $channels[] = 'instagram';
        }

        wp_send_json_success(
            array_merge(
                $meta,
                array(
                    'channels' => $channels,
                    'image_url' => $image_url,
                    'instagram_requires_image' => in_array('instagram', $channels, true) && $image_url === '',
                )
            )
        );
    }

    /**
     * Whether the save request defers social publish to AJAX.
     *
     * @return bool
     */
    private function should_defer_social_publish() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        return isset($_POST['maca_defer_social']) && sanitize_text_field(wp_unslash($_POST['maca_defer_social'])) === '1';
    }

    /**
     * Queue or run social publish after save.
     *
     * @param string $object_type news|event.
     * @param int    $object_id   Saved object ID.
     * @return void
     */
    private function handle_social_publish_after_save($object_type, $object_id) {
        if ($object_id <= 0 || !$this->user_can_manage_social() || !function_exists('maca_menulist_info_hub_get_social_publish_channels')) {
            return;
        }

        $channels = maca_menulist_info_hub_get_social_publish_channels($object_type, $object_id);

        if ($channels === array()) {
            return;
        }

        if ($this->should_defer_social_publish()) {
            maca_menulist_info_hub_queue_deferred_social_publish($object_type, $object_id, $channels);
            return;
        }

        if (function_exists('maca_menulist_info_hub_maybe_publish_social')) {
            maca_menulist_info_hub_maybe_publish_social($object_type, $object_id);
        }
    }

    /**
     * Handle POST actions.
     *
     * @return void
     */
    public function handle_form_submission() {
        if (!isset($_POST['maca_njuvs_action'])) {
            return;
        }

        if (!isset($_POST['maca_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['maca_nonce'])), 'maca_njuvs_save')) {
            return;
        }

        if (!maca_menulist_user_can_manage_plugin() || !maca_menulist_info_hub_feature_available()) {
            return;
        }

        $action = sanitize_key(wp_unslash($_POST['maca_njuvs_action']));

        $social_actions = array('save_meta_settings', 'disconnect_meta', 'select_meta_page', 'test_meta_publish');
        if (in_array($action, $social_actions, true) && !$this->user_can_manage_social()) {
            add_settings_error(
                'maca_njuvs',
                'social_denied',
                __('Facebook and Instagram publishing is not available for demo users.', 'maca-njuvs')
            );
            return;
        }

        switch ($action) {
            case 'save_settings':
                $this->save_settings();
                break;
            case 'save_meta_settings':
                $this->save_meta_settings();
                break;
            case 'disconnect_meta':
                $this->disconnect_meta();
                break;
            case 'select_meta_page':
                $this->select_meta_page();
                break;
            case 'test_meta_publish':
                $this->test_meta_publish();
                break;
            case 'save_news':
                $this->save_news();
                break;
            case 'delete_news':
                $this->delete_news();
                break;
            case 'save_event':
                $this->save_event();
                break;
            case 'delete_event':
                $this->delete_event();
                break;
            case 'save_event_exception':
                $this->save_event_exception();
                break;
            case 'delete_event_exception':
                $this->delete_event_exception();
                break;
            case 'import_wp_posts':
                $this->import_wp_posts();
                break;
        }
    }

    /**
     * Can the current user manage Meta / social publishing?
     *
     * @return bool
     */
    private function user_can_manage_social() {
        return function_exists('maca_menulist_user_can_manage_info_hub_social')
            && maca_menulist_user_can_manage_info_hub_social();
    }

    /**
     * Save module settings.
     *
     * @return void
     */
    private function save_settings() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $enabled = isset($_POST['info_hub_enabled']) ? '1' : '0';
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        update_option('maca_njuvs_enabled', $enabled, false);

        if ($enabled === '1' && function_exists('maca_menulist_schedule_rewrite_flush')) {
            maca_menulist_schedule_rewrite_flush();
        }

        add_settings_error(
            'maca_njuvs',
            'settings_saved',
            __('Settings saved.', 'maca-njuvs'),
            'updated'
        );
    }

    /**
     * Import WordPress posts into maca Njuvs news.
     *
     * @return void
     */
    private function import_wp_posts() {
        if (!function_exists('maca_njuvs_import_wp_posts_batch')) {
            add_settings_error('maca_njuvs', 'import_error', __('Import is not available.', 'maca-njuvs'));
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $post_type = isset($_POST['import_post_type']) ? sanitize_key(wp_unslash($_POST['import_post_type'])) : 'post';
        $category_id = isset($_POST['import_category_id']) ? absint(wp_unslash($_POST['import_category_id'])) : 0;
        $skip_imported = !isset($_POST['import_skip_imported']) || $_POST['import_skip_imported'] === '1';
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        $result = maca_njuvs_import_wp_posts_batch(
            array(
                'post_type' => $post_type,
                'category_id' => $category_id,
                'skip_imported' => $skip_imported,
                'limit' => 200,
            )
        );

        $message = sprintf(
            /* translators: 1: imported count, 2: skipped count, 3: failed count */
            __('Import finished: %1$d imported, %2$d skipped, %3$d failed.', 'maca-njuvs'),
            (int) $result['imported'],
            (int) $result['skipped'],
            (int) $result['failed']
        );

        add_settings_error(
            'maca_njuvs',
            'import_done',
            $message,
            ((int) $result['failed']) > 0 ? 'error' : 'updated'
        );

        if (!empty($result['errors'])) {
            add_settings_error(
                'maca_njuvs',
                'import_errors',
                implode(' ', array_slice($result['errors'], 0, 3)),
                'error'
            );
        }
    }

    /**
     * Save Meta app credentials.
     *
     * @return void
     */
    private function save_meta_settings() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $app_id = sanitize_text_field(wp_unslash($_POST['meta_app_id'] ?? ''));
        $app_secret = sanitize_text_field(wp_unslash($_POST['meta_app_secret'] ?? ''));
        $test_image = esc_url_raw(wp_unslash($_POST['meta_test_image_url'] ?? ''));
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        maca_menulist_info_hub_meta_set('app_id', $app_id);

        if (maca_menulist_admin_secret_should_update($app_secret)) {
            maca_menulist_info_hub_meta_set('app_secret', maca_menulist_info_hub_encrypt_secret($app_secret));
        }

        maca_menulist_info_hub_meta_set('test_image_url', $test_image);

        add_settings_error('maca_njuvs', 'meta_saved', __('Meta app settings saved.', 'maca-njuvs'), 'updated');
    }

    /**
     * Disconnect Meta account.
     *
     * @return void
     */
    private function disconnect_meta() {
        maca_menulist_info_hub_meta_disconnect();
        add_settings_error('maca_njuvs', 'meta_disconnected', __('Facebook and Instagram were disconnected.', 'maca-njuvs'), 'updated');
    }

    /**
     * Save selected Facebook page after OAuth.
     *
     * @return void
     */
    private function select_meta_page() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $page_id = sanitize_text_field(wp_unslash($_POST['meta_page_id'] ?? ''));
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ($page_id === '' || !maca_menulist_info_hub_meta_select_page($page_id)) {
            add_settings_error('maca_njuvs', 'meta_page_error', __('Could not connect the selected page.', 'maca-njuvs'));
            return;
        }

        add_settings_error('maca_njuvs', 'meta_connected', __('Facebook page connected.', 'maca-njuvs'), 'updated');
        wp_safe_redirect(maca_menulist_info_hub_admin_url('social'));
        exit;
    }

    /**
     * Send test posts to connected channels.
     *
     * @return void
     */
    private function test_meta_publish() {
        if (!maca_menulist_info_hub_meta_is_connected()) {
            add_settings_error('maca_njuvs', 'meta_test_error', __('Connect Facebook first.', 'maca-njuvs'));
            return;
        }

        $results = maca_menulist_info_hub_meta_test_publish();
        $message = trim($results['facebook'] . ' ' . $results['instagram']);
        add_settings_error('maca_njuvs', 'meta_test', $message, 'updated');
    }

    /**
     * Resolve social status when saving content.
     *
     * @param object|null $existing Existing row.
     * @param bool        $share    Share enabled.
     * @param string      $channel  facebook|instagram.
     * @return string
     */
    private function resolve_social_status_on_save($existing, $share, $channel, $republish = false) {
        if (!$share) {
            return 'skipped';
        }

        $status_key = $channel === 'instagram' ? 'social_ig_status' : 'social_fb_status';

        if ($republish) {
            return 'pending';
        }

        if ($existing && isset($existing->{$status_key}) && (string) $existing->{$status_key} === 'published') {
            return 'published';
        }

        return 'pending';
    }

    /**
     * Prefer primary content; fall back to legacy English column when editing old rows.
     *
     * @param string $primary Primary field value.
     * @param string $english Legacy English field value.
     * @return string
     */
    private function primary_content_field($primary, $english) {
        $primary = trim((string) $primary);

        if ($primary !== '') {
            return $primary;
        }

        return trim((string) $english);
    }

    /**
     * Render a rich text field with link support (WordPress editor).
     *
     * @param string $editor_id  Unique editor DOM id.
     * @param string $field_name POST field name.
     * @param string $content    Current HTML content.
     * @param int    $rows       Approximate textarea height.
     * @return void
     */
    private function render_rich_text_editor($editor_id, $field_name, $content, $rows = 10) {
        wp_editor(
            $content,
            $editor_id,
            array(
                'textarea_name' => $field_name,
                'textarea_rows' => $rows,
                'media_buttons' => false,
                'teeny' => false,
                'quicktags' => array(
                    'buttons' => 'strong,em,link,ul,ol,li,close',
                ),
                'tinymce' => array(
                    'toolbar1' => 'bold,italic,link,bullist,numlist,undo,redo',
                    'toolbar2' => '',
                    'wp_autoresize_on' => true,
                ),
            )
        );
        echo '<p class="description">';
        esc_html_e('Use the link button to add hyperlinks. Basic formatting is supported.', 'maca-njuvs');
        echo '</p>';
    }

    /**
     * Save news item.
     *
     * @return void
     */
    private function save_news() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $news_id = isset($_POST['news_id']) ? absint(wp_unslash($_POST['news_id'])) : 0;
        $title = sanitize_text_field(wp_unslash($_POST['news_title'] ?? ''));
        $excerpt = maca_menulist_info_hub_sanitize_rich_text(wp_unslash($_POST['news_excerpt'] ?? ''));
        $content = maca_menulist_info_hub_sanitize_rich_text(wp_unslash($_POST['news_content'] ?? ''));
        $image_url = function_exists('maca_menulist_normalize_url')
            ? maca_menulist_normalize_url(esc_url_raw(wp_unslash($_POST['news_image_url'] ?? '')))
            : esc_url_raw(wp_unslash($_POST['news_image_url'] ?? ''));
        $status = sanitize_key(wp_unslash($_POST['news_status'] ?? 'draft'));
        $publish_at = maca_menulist_info_hub_parse_datetime_input(
            sanitize_text_field(wp_unslash($_POST['news_publish_at'] ?? ''))
        );
        $expires_at = maca_menulist_info_hub_parse_datetime_input(
            sanitize_text_field(wp_unslash($_POST['news_expires_at'] ?? ''))
        );
        $share_web = isset($_POST['news_share_web']) ? 1 : 0;
        $share_facebook = isset($_POST['news_share_facebook']) ? 1 : 0;
        $share_instagram = isset($_POST['news_share_instagram']) ? 1 : 0;
        $republish_facebook = isset($_POST['news_republish_facebook']) ? 1 : 0;
        $republish_instagram = isset($_POST['news_republish_instagram']) ? 1 : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if (!$this->user_can_manage_social()) {
            $share_facebook = 0;
            $share_instagram = 0;
            $republish_facebook = 0;
            $republish_instagram = 0;
        }

        if (trim($title) === '') {
            add_settings_error(
                'maca_njuvs',
                'news_error',
                __('Title is required.', 'maca-njuvs')
            );
            return;
        }

        $publishing_social = $share_facebook || $share_instagram || $republish_facebook || $republish_instagram;
        if (
            $publishing_social
            && function_exists('maca_menulist_info_hub_social_news_caption_length')
            && function_exists('maca_menulist_info_hub_social_instagram_caption_limit')
        ) {
            $caption_length = maca_menulist_info_hub_social_news_caption_length(
                array(
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $content,
                )
            );
            $caption_limit = maca_menulist_info_hub_social_instagram_caption_limit();

            if ($caption_length > $caption_limit) {
                add_settings_error(
                    'maca_njuvs',
                    'news_error',
                    __('The social post text may not exceed 2,200 characters (Instagram limit).', 'maca-njuvs')
                );
                return;
            }
        }

        $existing_news = $news_id > 0 ? maca_menulist_db_get_info_news($news_id) : null;
        $resolved_status = maca_menulist_info_hub_resolve_news_status($status, $publish_at ?: '');

        if (in_array($resolved_status, array('published', 'scheduled'), true)) {
            $share_web = 1;
        }

        $row = array(
            'title' => $title,
            'title_en' => '',
            'excerpt' => $excerpt,
            'excerpt_en' => '',
            'content' => $content,
            'content_en' => '',
            'image_url' => $image_url !== '' ? $image_url : null,
            'status' => $resolved_status,
            'publish_at' => $publish_at,
            'expires_at' => $expires_at,
            'share_web' => $share_web,
            'share_facebook' => $share_facebook,
            'share_instagram' => $share_instagram,
            'social_fb_status' => $this->resolve_social_status_on_save($existing_news, (bool) $share_facebook, 'facebook', (bool) $republish_facebook),
            'social_ig_status' => $this->resolve_social_status_on_save($existing_news, (bool) $share_instagram, 'instagram', (bool) $republish_instagram),
        );

        if ($news_id > 0) {
            $result = maca_menulist_db_update_info_news($news_id, $row);
            $saved_news_id = $news_id;
        } else {
            $result = maca_menulist_db_insert_info_news($row);
            global $wpdb;
            $saved_news_id = (int) $wpdb->insert_id;
        }

        if ($result === false) {
            add_settings_error('maca_njuvs', 'news_error', __('Could not save news item.', 'maca-njuvs'));
            return;
        }

        if ($saved_news_id > 0) {
            $this->handle_social_publish_after_save('news', $saved_news_id);
        }

        if (in_array($resolved_status, array('published', 'scheduled'), true) && $share_web) {
            update_option('maca_njuvs_enabled', '1', false);
        }

        add_settings_error('maca_njuvs', 'news_saved', __('News saved.', 'maca-njuvs'), 'updated');
        do_action('maca_menulist_site_chat_content_changed');
    }

    /**
     * Delete news item.
     *
     * @return void
     */
    private function delete_news() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $news_id = isset($_POST['news_id']) ? absint(wp_unslash($_POST['news_id'])) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ($news_id <= 0) {
            return;
        }

        maca_menulist_db_delete_info_news($news_id);
        add_settings_error('maca_njuvs', 'news_deleted', __('News deleted.', 'maca-njuvs'), 'updated');
        do_action('maca_menulist_site_chat_content_changed');
    }

    /**
     * Save event.
     *
     * @return void
     */
    private function save_event() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $event_id = isset($_POST['event_id']) ? absint(wp_unslash($_POST['event_id'])) : 0;
        $title = sanitize_text_field(wp_unslash($_POST['event_title'] ?? ''));
        $description = maca_menulist_info_hub_sanitize_rich_text(wp_unslash($_POST['event_description'] ?? ''));
        $location = sanitize_text_field(wp_unslash($_POST['event_location'] ?? ''));
        $image_url = function_exists('maca_menulist_normalize_url')
            ? maca_menulist_normalize_url(esc_url_raw(wp_unslash($_POST['event_image_url'] ?? '')))
            : esc_url_raw(wp_unslash($_POST['event_image_url'] ?? ''));
        $is_all_day = isset($_POST['event_is_all_day']) ? 1 : 0;
        $start_at = maca_menulist_info_hub_parse_datetime_input(
            sanitize_text_field(wp_unslash($_POST['event_start_at'] ?? '')),
            (bool) $is_all_day
        );
        $end_at = maca_menulist_info_hub_parse_datetime_input(
            sanitize_text_field(wp_unslash($_POST['event_end_at'] ?? '')),
            (bool) $is_all_day
        );
        $price_raw = isset($_POST['event_price'])
            ? sanitize_text_field(wp_unslash((string) $_POST['event_price']))
            : '';
        $price_raw = str_replace(',', '.', trim($price_raw));
        $price = $price_raw !== '' ? max(0, floatval($price_raw)) : null;
        if ($price !== null && $price <= 0) {
            $price = null;
        }
        $show_booking_button = isset($_POST['event_show_booking_button']) ? 1 : 0;
        $is_active = isset($_POST['event_is_active']) ? 1 : 0;
        $share_web = isset($_POST['event_share_web']) ? 1 : 0;
        $share_facebook = isset($_POST['event_share_facebook']) ? 1 : 0;
        $share_instagram = isset($_POST['event_share_instagram']) ? 1 : 0;
        $recurrence_type = sanitize_key(wp_unslash($_POST['event_recurrence_type'] ?? 'none'));
        $recurrence_interval = isset($_POST['event_recurrence_interval']) ? max(1, absint(wp_unslash($_POST['event_recurrence_interval']))) : 1;
        $days_raw = isset($_POST['event_days_of_week']) && is_array($_POST['event_days_of_week'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['event_days_of_week']))
            : array();
        $recurrence_until_raw = sanitize_text_field(wp_unslash($_POST['event_recurrence_until'] ?? ''));
        $recurrence_count_raw = isset($_POST['event_recurrence_count'])
            ? sanitize_text_field(wp_unslash((string) $_POST['event_recurrence_count']))
            : '';
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if (!$this->user_can_manage_social()) {
            $share_facebook = 0;
            $share_instagram = 0;
        }

        if (!in_array($recurrence_type, array('none', 'daily', 'weekly', 'monthly'), true)) {
            $recurrence_type = 'none';
        }

        $days_of_week = maca_menulist_format_days_of_week(is_array($days_raw) ? $days_raw : array());
        $recurrence_until = preg_match('/^\d{4}-\d{2}-\d{2}$/', $recurrence_until_raw) ? $recurrence_until_raw : null;
        $recurrence_count = $recurrence_count_raw !== '' ? max(1, absint($recurrence_count_raw)) : null;

        if ($recurrence_type === 'none') {
            $recurrence_interval = 1;
            $days_of_week = null;
            $recurrence_until = null;
            $recurrence_count = null;
        }

        if (trim($title) === '') {
            add_settings_error(
                'maca_njuvs',
                'event_error',
                __('Title is required.', 'maca-njuvs')
            );
            return;
        }

        if ($start_at === null || $end_at === null) {
            add_settings_error('maca_njuvs', 'event_error', __('Start and end date/time are required.', 'maca-njuvs'));
            return;
        }

        if ($end_at < $start_at) {
            add_settings_error('maca_njuvs', 'event_error', __('End must be after start.', 'maca-njuvs'));
            return;
        }

        $existing_event = $event_id > 0 ? maca_menulist_db_get_info_event($event_id) : null;

        $row = array(
            'title' => $title,
            'title_en' => '',
            'description' => $description,
            'description_en' => '',
            'location' => $location,
            'location_en' => '',
            'image_url' => $image_url !== '' ? $image_url : null,
            'price' => $price,
            'start_at' => $start_at,
            'end_at' => $end_at,
            'is_all_day' => $is_all_day,
            'timezone' => wp_timezone_string(),
            'recurrence_type' => $recurrence_type,
            'recurrence_interval' => $recurrence_interval,
            'days_of_week' => $days_of_week,
            'recurrence_until' => $recurrence_until,
            'recurrence_count' => $recurrence_count,
            'is_active' => $is_active,
            'show_booking_button' => $show_booking_button,
            'share_web' => $share_web,
            'share_facebook' => $share_facebook,
            'share_instagram' => $share_instagram,
            'social_fb_status' => $this->resolve_social_status_on_save($existing_event, (bool) $share_facebook, 'facebook'),
            'social_ig_status' => $this->resolve_social_status_on_save($existing_event, (bool) $share_instagram, 'instagram'),
        );

        if ($event_id > 0) {
            $result = maca_menulist_db_update_info_event($event_id, $row);
            $saved_event_id = $event_id;
        } else {
            $result = maca_menulist_db_insert_info_event($row);
            global $wpdb;
            $saved_event_id = (int) $wpdb->insert_id;
        }

        if ($result === false) {
            add_settings_error('maca_njuvs', 'event_error', __('Could not save event.', 'maca-njuvs'));
            return;
        }

        if ($saved_event_id > 0) {
            $this->handle_social_publish_after_save('event', $saved_event_id);
        }

        add_settings_error('maca_njuvs', 'event_saved', __('Event saved.', 'maca-njuvs'), 'updated');
        do_action('maca_menulist_site_chat_content_changed');

        if ($saved_event_id > 0) {
            wp_safe_redirect(
                maca_menulist_info_hub_admin_url(
                    'events',
                    array(
                        'action' => 'edit',
                        'id' => $saved_event_id,
                    )
                )
            );
            exit;
        }
    }

    /**
     * Delete event.
     *
     * @return void
     */
    private function delete_event() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $event_id = isset($_POST['event_id']) ? absint(wp_unslash($_POST['event_id'])) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ($event_id <= 0) {
            return;
        }

        maca_menulist_db_delete_info_event($event_id);
        add_settings_error('maca_njuvs', 'event_deleted', __('Event deleted.', 'maca-njuvs'), 'updated');
        do_action('maca_menulist_site_chat_content_changed');
    }

    /**
     * Save occurrence exception for a recurring event.
     *
     * @return void
     */
    private function save_event_exception() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $event_id = isset($_POST['event_id']) ? absint(wp_unslash($_POST['event_id'])) : 0;
        $occurrence_date = sanitize_text_field(wp_unslash($_POST['exception_occurrence_date'] ?? ''));
        $exception_type = sanitize_key(wp_unslash($_POST['exception_type'] ?? 'cancelled'));
        $new_start_at = maca_menulist_info_hub_parse_datetime_input(
            sanitize_text_field(wp_unslash($_POST['exception_new_start_at'] ?? ''))
        );
        $new_end_at = maca_menulist_info_hub_parse_datetime_input(
            sanitize_text_field(wp_unslash($_POST['exception_new_end_at'] ?? ''))
        );
        $note = sanitize_text_field(wp_unslash($_POST['exception_note'] ?? ''));
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ($event_id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $occurrence_date)) {
            add_settings_error('maca_njuvs', 'exception_error', __('Invalid exception date.', 'maca-njuvs'));
            return;
        }

        if (!in_array($exception_type, array('cancelled', 'modified'), true)) {
            $exception_type = 'cancelled';
        }

        if ($exception_type === 'modified' && ($new_start_at === null || $new_end_at === null)) {
            add_settings_error('maca_njuvs', 'exception_error', __('Modified exceptions need new start and end.', 'maca-njuvs'));
            return;
        }

        $result = maca_menulist_db_insert_info_event_exception(
            array(
                'event_id' => $event_id,
                'occurrence_date' => $occurrence_date,
                'exception_type' => $exception_type,
                'new_start_at' => $exception_type === 'modified' ? $new_start_at : null,
                'new_end_at' => $exception_type === 'modified' ? $new_end_at : null,
                'note' => $note,
            )
        );

        if ($result === false) {
            add_settings_error('maca_njuvs', 'exception_error', __('Could not save exception.', 'maca-njuvs'));
            return;
        }

        add_settings_error('maca_njuvs', 'exception_saved', __('Exception saved.', 'maca-njuvs'), 'updated');
        do_action('maca_menulist_site_chat_content_changed');
    }

    /**
     * Delete occurrence exception.
     *
     * @return void
     */
    private function delete_event_exception() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $exception_id = isset($_POST['exception_id']) ? absint(wp_unslash($_POST['exception_id'])) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ($exception_id <= 0) {
            return;
        }

        maca_menulist_db_delete_info_event_exception($exception_id);
        add_settings_error('maca_njuvs', 'exception_deleted', __('Exception removed.', 'maca-njuvs'), 'updated');
        do_action('maca_menulist_site_chat_content_changed');
    }

    /**
     * Render admin page.
     *
     * @return void
     */
    public function render_page() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'news';
        $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : 'list';
        $item_id = isset($_GET['id']) ? absint(wp_unslash($_GET['id'])) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $tabs = maca_menulist_info_hub_admin_tabs();
        $allowed_tabs = array_merge(array_keys($tabs), array('social-guide'));
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'news';
        }

        if (in_array($tab, array('social', 'social-guide'), true) && !$this->user_can_manage_social()) {
            $tab = 'news';
        }

        ?>
        <div class="wrap maca-info-hub-admin">
            <h1><?php esc_html_e('maca Njuvs', 'maca-njuvs'); ?></h1>
            <p class="description"><?php esc_html_e('Publish news and events on your website with Gutenberg blocks. Guests can subscribe to the event calendar via iCal.', 'maca-njuvs'); ?></p>

            <?php settings_errors('maca_njuvs'); ?>

            <nav class="nav-tab-wrapper">
                <?php foreach ($tabs as $tab_id => $tab_def) : ?>
                    <a href="<?php echo esc_url(maca_menulist_info_hub_admin_url($tab_id)); ?>"
                        class="nav-tab <?php echo $tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($tab_def['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="maca-info-hub-admin-panel" style="margin-top: 16px;">
                <?php
                if ($tab === 'settings') {
                    $this->render_settings_tab();
                } elseif ($tab === 'import') {
                    $this->render_import_tab();
                } elseif ($tab === 'guide') {
                    $this->render_guide_tab();
                } elseif ($tab === 'social-guide') {
                    $this->render_social_guide_tab();
                } elseif ($tab === 'social') {
                    $this->render_social_tab();
                } elseif ($tab === 'events') {
                    if ($action === 'add' || $action === 'edit') {
                        $this->render_event_form($item_id);
                    } else {
                        $this->render_events_list();
                    }
                } else {
                    if ($action === 'add' || $action === 'edit') {
                        $this->render_news_form($item_id);
                    } else {
                        $this->render_news_list();
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Social media / Meta tab.
     *
     * @return void
     */
    private function render_social_tab() {
        if (!$this->user_can_manage_social()) {
            echo '<div class="notice notice-warning inline"><p>';
            esc_html_e('Facebook and Instagram publishing is not available for demo users.', 'maca-njuvs');
            echo '</p></div>';
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $oauth_step = isset($_GET['maca_meta_oauth']) ? sanitize_key(wp_unslash($_GET['maca_meta_oauth'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $pages = get_transient('maca_njuvs_meta_pages_' . get_current_user_id());
        $app_id = maca_menulist_info_hub_meta_get_app_id();
        $connected = maca_menulist_info_hub_meta_is_connected();
        $redirect_uri = maca_menulist_info_hub_meta_oauth_redirect_uri();
        $test_image = (string) maca_menulist_info_hub_meta_get('test_image_url', '');
        $has_app_secret = (string) maca_menulist_info_hub_meta_get('app_secret', '') !== '';

        if ($oauth_step === 'select_page' && is_array($pages) && !empty($pages)) :
            ?>
            <h2><?php esc_html_e('Select Facebook page', 'maca-njuvs'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
                <input type="hidden" name="maca_njuvs_action" value="select_meta_page">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Page', 'maca-njuvs'); ?></th>
                        <td>
                            <select name="meta_page_id" required>
                                <option value=""><?php esc_html_e('Choose page…', 'maca-njuvs'); ?></option>
                                <?php foreach ($pages as $page) : ?>
                                    <?php if (!is_array($page)) { continue; } ?>
                                    <option value="<?php echo esc_attr((string) ($page['id'] ?? '')); ?>">
                                        <?php
                                        echo esc_html((string) ($page['name'] ?? ''));
                                        if (!empty($page['instagram_business_account']['username'])) {
                                            echo ' — @' . esc_html((string) $page['instagram_business_account']['username']);
                                        }
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Connect page', 'maca-njuvs')); ?>
            </form>
            <?php
            return;
        endif;
        ?>
        <p>
            <a href="<?php echo esc_url(maca_menulist_info_hub_admin_url('social-guide')); ?>"><?php esc_html_e('Setup guide: Facebook & Instagram', 'maca-njuvs'); ?></a>
        </p>
        <p class="description"><?php esc_html_e('Use your own Meta Developer app. Maca does not host OAuth or tokens for you.', 'maca-njuvs'); ?></p>

        <h2><?php esc_html_e('Meta app credentials', 'maca-njuvs'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
            <input type="hidden" name="maca_njuvs_action" value="save_meta_settings">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="meta_app_id"><?php esc_html_e('App ID', 'maca-njuvs'); ?></label></th>
                    <td><input type="text" id="meta_app_id" name="meta_app_id" class="regular-text" value="<?php echo esc_attr($app_id); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="meta_app_secret"><?php esc_html_e('App Secret', 'maca-njuvs'); ?></label></th>
                    <td>
                        <?php
                        $meta_secret_attrs = maca_menulist_admin_secret_input_attrs(
                            $has_app_secret,
                            array(
                                'id' => 'meta_app_secret',
                                'name' => 'meta_app_secret',
                            )
                        );
                        ?>
                        <input <?php echo maca_menulist_admin_secret_field_attr_string($meta_secret_attrs); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                        <?php if ($has_app_secret) : ?>
                            <p class="description"><?php esc_html_e('Leave blank to keep the current secret.', 'maca-njuvs'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('OAuth redirect URI', 'maca-njuvs'); ?></th>
                    <td>
                        <code><?php echo esc_html($redirect_uri); ?></code>
                        <p class="description"><?php esc_html_e('Add this exact URL under Facebook Login → Valid OAuth Redirect URIs in your Meta app.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="meta_test_image_url"><?php esc_html_e('Test image URL', 'maca-njuvs'); ?></label></th>
                    <td>
                        <input type="url" id="meta_test_image_url" name="meta_test_image_url" class="regular-text" value="<?php echo esc_attr($test_image); ?>">
                        <p class="description"><?php esc_html_e('Public HTTPS image used for Instagram test posts.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save Meta settings', 'maca-njuvs')); ?>
        </form>

        <h2><?php esc_html_e('Connection', 'maca-njuvs'); ?></h2>
        <?php if ($connected) : ?>
            <p>
                <strong><?php esc_html_e('Facebook page:', 'maca-njuvs'); ?></strong>
                <?php echo esc_html((string) maca_menulist_info_hub_meta_get('page_name', '')); ?>
            </p>
            <?php if (maca_menulist_info_hub_meta_has_instagram()) : ?>
                <p>
                    <strong><?php esc_html_e('Instagram:', 'maca-njuvs'); ?></strong>
                    @<?php echo esc_html((string) maca_menulist_info_hub_meta_get('ig_username', '')); ?>
                </p>
            <?php else : ?>
                <p class="description"><?php esc_html_e('No Instagram Business account is linked to this page.', 'maca-njuvs'); ?></p>
            <?php endif; ?>
            <?php
            $expires = (int) maca_menulist_info_hub_meta_get('token_expires', 0);
            if ($expires > 0) :
                ?>
                <p class="description">
                    <?php
                    printf(
                        /* translators: %s: date/time */
                        esc_html__('User token expires around %s (refreshed automatically when possible).', 'maca-njuvs'),
                        esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $expires))
                    );
                    ?>
                </p>
            <?php endif; ?>
            <p>
                <a class="button button-primary" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('social', array('maca_meta_oauth' => 'start'))); ?>">
                    <?php esc_html_e('Reconnect', 'maca-njuvs'); ?>
                </a>
            </p>
            <form method="post" action="" class="maca-info-hub-social-test-form" style="display:inline;">
                <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
                <input type="hidden" name="maca_njuvs_action" value="test_meta_publish">
                <?php submit_button(__('Send test post', 'maca-njuvs'), 'secondary', 'submit', false); ?>
            </form>
            <form method="post" action="" style="display:inline;margin-left:8px;" onsubmit="return confirm('<?php echo esc_js(__('Disconnect Facebook and Instagram?', 'maca-njuvs')); ?>');">
                <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
                <input type="hidden" name="maca_njuvs_action" value="disconnect_meta">
                <?php submit_button(__('Disconnect', 'maca-njuvs'), 'delete', 'submit', false); ?>
            </form>
        <?php elseif (maca_menulist_info_hub_meta_has_app_credentials()) : ?>
            <p><a class="button button-primary" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('social', array('maca_meta_oauth' => 'start'))); ?>"><?php esc_html_e('Connect Facebook & Instagram', 'maca-njuvs'); ?></a></p>
        <?php else : ?>
            <p class="description"><?php esc_html_e('Save App ID and App Secret first, then connect.', 'maca-njuvs'); ?></p>
        <?php endif; ?>

        <h2><?php esc_html_e('Publish log', 'maca-njuvs'); ?></h2>
        <?php
        $logs = function_exists('maca_menulist_db_get_info_social_log') ? maca_menulist_db_get_info_social_log(30) : array();
        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Time', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Type', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Channel', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Status', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Message', 'maca-njuvs'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) : ?>
                    <tr><td colspan="5"><?php esc_html_e('No social posts yet.', 'maca-njuvs'); ?></td></tr>
                <?php else : ?>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html((string) $log->created_at); ?></td>
                            <td><?php echo esc_html((string) $log->object_type . ' #' . (int) $log->object_id); ?></td>
                            <td><?php echo esc_html((string) $log->channel); ?></td>
                            <td><?php echo esc_html(maca_menulist_info_hub_social_status_label((string) $log->status)); ?></td>
                            <td><?php echo esc_html($log->message ?: ($log->external_id ?: '—')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Publishing checkboxes for news/event forms.
     *
     * @param string      $prefix         Field prefix (news|event).
     * @param bool        $share_web      Web checked.
     * @param bool        $share_facebook Facebook checked.
     * @param bool        $share_instagram Instagram checked.
     * @param object|null $row            Existing row.
     * @return void
     */
    private function render_social_publish_fields($prefix, $share_web, $share_facebook, $share_instagram, $row) {
        $can_social = $this->user_can_manage_social();
        $connected = maca_menulist_info_hub_meta_is_connected();
        $has_ig = maca_menulist_info_hub_meta_has_instagram();
        ?>
        <label><input type="checkbox" name="<?php echo esc_attr($prefix); ?>_share_web" value="1" <?php checked($share_web); ?>> <?php esc_html_e('Website', 'maca-njuvs'); ?></label><br>
        <?php if ($can_social && $connected) : ?>
            <label><input type="checkbox" name="<?php echo esc_attr($prefix); ?>_share_facebook" value="1" <?php checked($share_facebook); ?>> <?php esc_html_e('Facebook', 'maca-njuvs'); ?></label><br>
            <label>
                <input type="checkbox" name="<?php echo esc_attr($prefix); ?>_share_instagram" value="1" <?php checked($share_instagram); ?> <?php disabled(!$has_ig); ?>>
                <?php esc_html_e('Instagram', 'maca-njuvs'); ?>
            </label>
            <?php if (!$has_ig) : ?>
                <p class="description"><?php esc_html_e('Connect an Instagram Business account to the Facebook page first.', 'maca-njuvs'); ?></p>
            <?php else : ?>
                <p class="description"><?php esc_html_e('Instagram requires an image on the item.', 'maca-njuvs'); ?></p>
            <?php endif; ?>
        <?php elseif ($can_social) : ?>
            <p class="description">
                <?php
                printf(
                    /* translators: %s: admin URL */
                    esc_html__('Connect Facebook under %s to enable social publishing.', 'maca-njuvs'),
                    esc_html__('Social media', 'maca-njuvs')
                );
                ?>
                <a href="<?php echo esc_url(maca_menulist_info_hub_admin_url('social')); ?>"><?php esc_html_e('Open Social media', 'maca-njuvs'); ?></a>
            </p>
        <?php else : ?>
            <p class="description"><?php esc_html_e('Facebook and Instagram publishing is not available for demo users.', 'maca-njuvs'); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Settings tab.
     *
     * @return void
     */
    private function render_settings_tab() {
        $enabled = get_option('maca_njuvs_enabled', '1') === '1';
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
            <input type="hidden" name="maca_njuvs_action" value="save_settings">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable maca Njuvs', 'maca-njuvs'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="info_hub_enabled" value="1" <?php checked($enabled); ?>>
                            <?php esc_html_e('Show news and events on the website and in blocks', 'maca-njuvs'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save settings', 'maca-njuvs')); ?>
        </form>

        <?php if ($this->user_can_manage_social()) : ?>
            <h2><?php esc_html_e('Facebook & Instagram', 'maca-njuvs'); ?></h2>
            <p><?php esc_html_e('Connect your Meta app to publish news and events to your Facebook Page and Instagram account.', 'maca-njuvs'); ?></p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('social-guide')); ?>">
                    <?php esc_html_e('Setup guide: Facebook & Instagram', 'maca-njuvs'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('social')); ?>">
                    <?php esc_html_e('Open Social media', 'maca-njuvs'); ?>
                </a>
            </p>
        <?php else : ?>
            <p class="description"><?php esc_html_e('Facebook and Instagram publishing is not available for demo users.', 'maca-njuvs'); ?></p>
        <?php endif; ?>

        <h2><?php esc_html_e('Blocks', 'maca-njuvs'); ?></h2>
        <p><?php esc_html_e('Add the blocks “maca News” and “maca Events” from the maca Njuvs category in the block editor.', 'maca-njuvs'); ?></p>

        <h2><?php esc_html_e('Calendar feed', 'maca-njuvs'); ?></h2>
        <?php if ($enabled && function_exists('maca_menulist_get_info_events_ics_url')) : ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('iCal feed URL', 'maca-njuvs'); ?></th>
                    <td>
                        <code><?php echo esc_html(maca_menulist_get_info_events_ics_url()); ?></code>
                        <p class="description"><?php esc_html_e('Public feed for calendar apps. Updates when events change.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Subscribe URL', 'maca-njuvs'); ?></th>
                    <td>
                        <code><?php echo esc_html(maca_menulist_get_info_events_webcal_url()); ?></code>
                        <p class="description"><?php esc_html_e('Use webcal:// in Apple Calendar and many other apps.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
            </table>
            <p class="description"><?php esc_html_e('If the feed returns 404, save Permalink settings once under Settings → Permalinks.', 'maca-njuvs'); ?></p>
        <?php else : ?>
            <p class="description"><?php esc_html_e('Enable maca Njuvs to publish the calendar feed.', 'maca-njuvs'); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Import tab.
     *
     * @return void
     */
    private function render_import_tab() {
        if (!function_exists('maca_njuvs_import_wp_post_types')) {
            echo '<p>' . esc_html__('Import is not available.', 'maca-njuvs') . '</p>';
            return;
        }

        $import_stats = function_exists('maca_njuvs_import_wp_posts_stats')
            ? maca_njuvs_import_wp_posts_stats(array('post_type' => 'post'))
            : array('total' => 0, 'imported' => 0, 'pending' => 0);
        $categories = get_categories(array('hide_empty' => false));
        ?>
        <h2><?php esc_html_e('Import from WordPress', 'maca-njuvs'); ?></h2>
        <p><?php esc_html_e('Copy existing WordPress posts (Inlägg) into maca Njuvs as news items. Original posts are kept — nothing is deleted.', 'maca-njuvs'); ?></p>
        <p>
            <?php
            printf(
                /* translators: 1: total posts, 2: already imported, 3: pending import */
                esc_html__('Posts found: %1$d. Already imported: %2$d. Remaining: %3$d.', 'maca-njuvs'),
                (int) $import_stats['total'],
                (int) $import_stats['imported'],
                (int) $import_stats['pending']
            );
            ?>
        </p>
        <form method="post" action="<?php echo esc_url(maca_menulist_info_hub_admin_url('import')); ?>" onsubmit="return confirm('<?php echo esc_js(__('Import selected WordPress posts as maca Njuvs news?', 'maca-njuvs')); ?>');">
            <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
            <input type="hidden" name="maca_njuvs_action" value="import_wp_posts">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="import_post_type"><?php esc_html_e('Content type', 'maca-njuvs'); ?></label></th>
                    <td>
                        <select name="import_post_type" id="import_post_type">
                            <?php foreach (maca_njuvs_import_wp_post_types() as $type => $label) : ?>
                                <option value="<?php echo esc_attr($type); ?>" <?php selected($type, 'post'); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="import_category_id"><?php esc_html_e('Category', 'maca-njuvs'); ?></label></th>
                    <td>
                        <select name="import_category_id" id="import_category_id">
                            <option value="0"><?php esc_html_e('All categories', 'maca-njuvs'); ?></option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo esc_attr((string) $category->term_id); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Optional. Only applies to posts, not pages.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Already imported', 'maca-njuvs'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="import_skip_imported" value="1" checked>
                            <?php esc_html_e('Skip posts that were imported before', 'maca-njuvs'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Import as news', 'maca-njuvs'), 'primary'); ?>
        </form>
        <?php
    }

    /**
     * User guide tab (blocks, settings, features).
     *
     * @return void
     */
    private function render_guide_tab() {
        $guide_html = function_exists('maca_menulist_info_hub_render_guide_html')
            ? maca_menulist_info_hub_render_guide_html()
            : '';
        ?>
        <div class="maca-info-hub-guide maca-guide-content">
            <?php
            if ($guide_html === '') {
                echo '<p>' . esc_html__('Guide file not found or could not be read.', 'maca-njuvs') . '</p>';
            } else {
                echo $guide_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized in maca_menulist_info_hub_render_guide_html().
            }
            ?>
        </div>
        <?php
    }

    /**
     * Facebook / Instagram setup guide tab.
     *
     * @return void
     */
    private function render_social_guide_tab() {
        $guide_html = function_exists('maca_menulist_info_hub_render_social_guide_html')
            ? maca_menulist_info_hub_render_social_guide_html()
            : '';
        ?>
        <p>
            <a class="button" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('settings')); ?>">&larr; <?php esc_html_e('Back to settings', 'maca-njuvs'); ?></a>
            <a class="button" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('social')); ?>"><?php esc_html_e('Open Social media', 'maca-njuvs'); ?></a>
        </p>
        <div class="maca-info-hub-guide maca-guide-content">
            <?php
            if ($guide_html === '') {
                echo '<p>' . esc_html__('Guide file not found or could not be read.', 'maca-njuvs') . '</p>';
            } else {
                echo $guide_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized in maca_menulist_info_hub_render_social_guide_html().
            }
            ?>
        </div>
        <?php
    }

    /**
     * News list.
     *
     * @return void
     */
    private function render_news_list() {
        $items = maca_menulist_db_get_info_news_items();
        $hub_enabled = maca_njuvs_enabled();
        ?>
        <?php if (!$hub_enabled) : ?>
            <div class="notice notice-warning inline">
                <p>
                    <?php esc_html_e('maca Njuvs is not enabled for the website. News will not appear in blocks until you enable it under Settings.', 'maca-njuvs'); ?>
                    <a href="<?php echo esc_url(maca_menulist_info_hub_admin_url('settings')); ?>"><?php esc_html_e('Open settings', 'maca-njuvs'); ?></a>
                </p>
            </div>
        <?php endif; ?>
        <p>
            <a href="<?php echo esc_url(maca_menulist_info_hub_admin_url('news', array('action' => 'add'))); ?>" class="button button-primary">
                <?php esc_html_e('Add news', 'maca-njuvs'); ?>
            </a>
        </p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Status', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Publish', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Web', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Visible on site', 'maca-njuvs'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)) : ?>
                    <tr><td colspan="6"><?php esc_html_e('No news yet.', 'maca-njuvs'); ?></td></tr>
                <?php else : ?>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td><?php echo esc_html(maca_menulist_info_hub_get_news_title($item)); ?></td>
                            <td><?php echo esc_html(maca_menulist_info_hub_news_status_label((string) $item->status)); ?></td>
                            <td><?php echo esc_html($item->publish_at ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime((string) $item->publish_at)) : '—'); ?></td>
                            <td><?php echo !empty($item->share_web) ? '✓' : '—'; ?></td>
                            <td>
                                <?php
                                $blockers = maca_menulist_info_hub_get_news_visibility_blockers($item);
                                if ($blockers === array()) {
                                    echo esc_html__('Yes', 'maca-njuvs');
                                } else {
                                    echo esc_html__('No', 'maca-njuvs');
                                    echo '<br><span class="description">' . esc_html(implode(' ', $blockers)) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a class="button button-small" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('news', array('action' => 'edit', 'id' => (int) $item->id))); ?>">
                                    <?php esc_html_e('Edit', 'maca-njuvs'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * News form.
     *
     * @param int $news_id News ID.
     * @return void
     */
    private function render_news_form($news_id) {
        $news = $news_id > 0 ? maca_menulist_db_get_info_news($news_id) : null;

        $title = $news ? $this->primary_content_field($news->title, $news->title_en) : '';
        $excerpt = $news ? $this->primary_content_field($news->excerpt, $news->excerpt_en) : '';
        $content = $news ? $this->primary_content_field($news->content, $news->content_en) : '';
        $image_url = $news ? (string) $news->image_url : '';
        $status = $news ? (string) $news->status : 'published';
        $publish_at = $news && $news->publish_at ? substr((string) $news->publish_at, 0, 16) : '';
        $expires_at = $news && $news->expires_at ? substr((string) $news->expires_at, 0, 16) : '';
        $share_web = !$news || !empty($news->share_web);
        $share_facebook = $news && !empty($news->share_facebook);
        $share_instagram = $news && !empty($news->share_instagram);

        if ($publish_at !== '') {
            $publish_at = str_replace(' ', 'T', $publish_at);
        }
        if ($expires_at !== '') {
            $expires_at = str_replace(' ', 'T', $expires_at);
        }
        ?>
        <p><a href="<?php echo esc_url(maca_menulist_info_hub_admin_url('news')); ?>">&larr; <?php esc_html_e('Back to list', 'maca-njuvs'); ?></a></p>

        <form method="post" action="" class="maca-info-hub-social-save-form" data-object-type="news">
            <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
            <input type="hidden" name="maca_njuvs_action" value="save_news">
            <input type="hidden" name="news_id" value="<?php echo esc_attr((string) $news_id); ?>">

            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="news_title"><?php esc_html_e('Title', 'maca-njuvs'); ?></label></th>
                    <td>
                        <input type="text" id="news_title" name="news_title" class="regular-text" value="<?php echo esc_attr($title); ?>" required>
                        <p class="description"><?php esc_html_e('Main heading — shown on the website, in the banner, and first in the social post caption.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="news_excerpt"><?php esc_html_e('Excerpt', 'maca-njuvs'); ?></label></th>
                    <td>
                        <?php $this->render_rich_text_editor('news_excerpt', 'news_excerpt', $excerpt, 3); ?>
                        <p class="description"><?php esc_html_e('Short summary for the banner and list. Included in the social post caption after the title.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="news_content"><?php esc_html_e('Content', 'maca-njuvs'); ?></label></th>
                    <td>
                        <?php $this->render_rich_text_editor('news_content', 'news_content', $content, 10); ?>
                    </td>
                </tr>
                <?php if ($this->user_can_manage_social() && maca_menulist_info_hub_meta_is_connected()) : ?>
                <tr class="maca-info-hub-social-caption-limit-row">
                    <th><?php esc_html_e('Social text', 'maca-njuvs'); ?></th>
                    <td>
                        <p
                            id="maca-info-hub-social-caption-counter"
                            class="maca-info-hub-social-caption-counter"
                            aria-live="polite"
                            data-limit="<?php echo esc_attr((string) (function_exists('maca_menulist_info_hub_social_instagram_caption_limit') ? maca_menulist_info_hub_social_instagram_caption_limit() : 2200)); ?>"
                        ></p>
                        <p class="description maca-info-hub-social-caption-limit-note">
                            <?php esc_html_e('Title, excerpt, and content are combined for social posts. Maximum 2,200 characters (Instagram limit).', 'maca-njuvs'); ?>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><label for="news_image_url"><?php esc_html_e('Image', 'maca-njuvs'); ?></label></th>
                    <td>
                        <input type="text" id="news_image_url" name="news_image_url" class="regular-text" value="<?php echo esc_attr($image_url); ?>">
                        <button type="button" class="button maca-info-hub-upload" data-target="#news_image_url"><?php esc_html_e('Select image', 'maca-njuvs'); ?></button>
                        <?php if ($image_url !== '') : ?>
                            <p><img src="<?php echo esc_url($image_url); ?>" alt="" style="max-width:160px;margin-top:8px;"></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="news_status"><?php esc_html_e('Status', 'maca-njuvs'); ?></label></th>
                    <td>
                        <select id="news_status" name="news_status">
                            <?php foreach (array('draft', 'scheduled', 'published', 'archived') as $status_key) : ?>
                                <option value="<?php echo esc_attr($status_key); ?>" <?php selected($status, $status_key); ?>>
                                    <?php echo esc_html(maca_menulist_info_hub_news_status_label($status_key)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Choose Published to show on the website. Draft and scheduled items are hidden until published.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="news_publish_at"><?php esc_html_e('Publish at', 'maca-njuvs'); ?></label></th>
                    <td>
                        <input type="datetime-local" id="news_publish_at" name="news_publish_at" value="<?php echo esc_attr($publish_at); ?>">
                        <p class="description"><?php esc_html_e('Optional. Leave empty to publish immediately. A future date with status Published becomes Scheduled until then.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="news_expires_at"><?php esc_html_e('Expires at', 'maca-njuvs'); ?></label></th>
                    <td><input type="datetime-local" id="news_expires_at" name="news_expires_at" value="<?php echo esc_attr($expires_at); ?>"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Publishing', 'maca-njuvs'); ?></th>
                    <td>
                        <?php $this->render_social_publish_fields('news', $share_web, $share_facebook, $share_instagram, $news); ?>
                    </td>
                </tr>
                <?php if ($this->user_can_manage_social() && $news_id > 0 && ($share_facebook || $share_instagram)) : ?>
                <tr>
                    <th><?php esc_html_e('Social status', 'maca-njuvs'); ?></th>
                    <td>
                        <?php if ($share_facebook) : ?>
                            <p><?php echo esc_html__('Facebook:', 'maca-njuvs') . ' ' . esc_html(maca_menulist_info_hub_social_status_label((string) ($news->social_fb_status ?? 'skipped'))); ?></p>
                            <?php if (in_array((string) ($news->social_fb_status ?? ''), array('published', 'failed'), true)) : ?>
                                <p><label><input type="checkbox" name="news_republish_facebook" value="1"> <?php esc_html_e('Publish again to Facebook', 'maca-njuvs'); ?></label></p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($share_instagram) : ?>
                            <p><?php echo esc_html__('Instagram:', 'maca-njuvs') . ' ' . esc_html(maca_menulist_info_hub_social_status_label((string) ($news->social_ig_status ?? 'skipped'))); ?></p>
                            <?php if (in_array((string) ($news->social_ig_status ?? ''), array('published', 'failed'), true)) : ?>
                                <p><label><input type="checkbox" name="news_republish_instagram" value="1"> <?php esc_html_e('Publish again to Instagram', 'maca-njuvs'); ?></label></p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e('Republish creates a new post on the channel. Title, excerpt, and content are sent as the caption.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <p class="submit" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <?php
                submit_button($news_id > 0 ? __('Update news', 'maca-njuvs') : __('Add news', 'maca-njuvs'), 'primary', 'submit', false);
                if ($this->user_can_manage_social() && maca_menulist_info_hub_meta_is_connected()) {
                    echo '<button type="button" class="button maca-info-hub-social-preview-btn" id="maca-info-hub-social-preview-btn">';
                    esc_html_e('Preview social post', 'maca-njuvs');
                    echo '</button>';
                }
                ?>
            </p>
        </form>

        <?php if ($news_id > 0) : ?>
            <form method="post" action="" onsubmit="return confirm('<?php echo esc_js(__('Delete this news item?', 'maca-njuvs')); ?>');">
                <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
                <input type="hidden" name="maca_njuvs_action" value="delete_news">
                <input type="hidden" name="news_id" value="<?php echo esc_attr((string) $news_id); ?>">
                <?php submit_button(__('Delete', 'maca-njuvs'), 'delete'); ?>
            </form>
        <?php endif; ?>

        <?php $this->render_media_uploader_script(); ?>
        <?php
    }

    /**
     * Events list.
     *
     * @return void
     */
    private function render_events_list() {
        $items = maca_menulist_db_get_info_events();
        ?>
        <p>
            <a href="<?php echo esc_url(maca_menulist_info_hub_admin_url('events', array('action' => 'add'))); ?>" class="button button-primary">
                <?php esc_html_e('Add event', 'maca-njuvs'); ?>
            </a>
        </p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('When', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Price', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Recurrence', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Active', 'maca-njuvs'); ?></th>
                    <th><?php esc_html_e('Web', 'maca-njuvs'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)) : ?>
                    <tr><td colspan="7"><?php esc_html_e('No events yet.', 'maca-njuvs'); ?></td></tr>
                <?php else : ?>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td><?php echo esc_html(maca_menulist_info_hub_get_event_title($item)); ?></td>
                            <td><?php echo esc_html(maca_menulist_info_hub_format_event_datetime($item)); ?></td>
                            <td><?php echo esc_html(maca_menulist_info_hub_get_event_price_label($item) ?: '—'); ?></td>
                            <td><?php echo esc_html(maca_menulist_info_hub_recurrence_summary($item)); ?></td>
                            <td><?php echo !empty($item->is_active) ? '✓' : '—'; ?></td>
                            <td><?php echo !empty($item->share_web) ? '✓' : '—'; ?></td>
                            <td>
                                <a class="button button-small" href="<?php echo esc_url(maca_menulist_info_hub_admin_url('events', array('action' => 'edit', 'id' => (int) $item->id))); ?>">
                                    <?php esc_html_e('Edit', 'maca-njuvs'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Event form.
     *
     * @param int $event_id Event ID.
     * @return void
     */
    private function render_event_form($event_id) {
        $event = $event_id > 0 ? maca_menulist_db_get_info_event($event_id) : null;

        $title = $event ? $this->primary_content_field($event->title, $event->title_en) : '';
        $description = $event ? $this->primary_content_field($event->description, $event->description_en) : '';
        $location = $event ? $this->primary_content_field($event->location, $event->location_en) : '';
        $image_url = $event ? (string) $event->image_url : '';
        $price = ($event && isset($event->price) && $event->price !== null && $event->price !== '')
            ? (string) $event->price
            : '';
        $is_all_day = $event && !empty($event->is_all_day);
        $start_at = ($event && $event->start_at && function_exists('maca_menulist_wp_mysql_to_datetime_local'))
            ? maca_menulist_wp_mysql_to_datetime_local((string) $event->start_at)
            : ($event && $event->start_at ? str_replace(' ', 'T', substr((string) $event->start_at, 0, 16)) : '');
        $end_at = ($event && $event->end_at && function_exists('maca_menulist_wp_mysql_to_datetime_local'))
            ? maca_menulist_wp_mysql_to_datetime_local((string) $event->end_at)
            : ($event && $event->end_at ? str_replace(' ', 'T', substr((string) $event->end_at, 0, 16)) : '');
        $is_active = !$event || !empty($event->is_active);
        $show_booking_button = $event && !empty($event->show_booking_button);
        $share_web = !$event || !empty($event->share_web);
        $share_facebook = $event && !empty($event->share_facebook);
        $share_instagram = $event && !empty($event->share_instagram);
        $recurrence_type = $event ? (string) $event->recurrence_type : 'none';
        $recurrence_interval = $event ? max(1, (int) $event->recurrence_interval) : 1;
        $recurrence_days = $event ? maca_menulist_parse_days_of_week($event->days_of_week ?? '') : array();
        $recurrence_until = $event && !empty($event->recurrence_until) ? (string) $event->recurrence_until : '';
        $recurrence_count = $event && !empty($event->recurrence_count) ? (int) $event->recurrence_count : '';
        $exceptions = $event_id > 0 ? maca_menulist_db_get_info_event_exceptions($event_id) : array();
        $can_show_booking = function_exists('maca_menulist_info_hub_can_show_booking_buttons')
            && maca_menulist_info_hub_can_show_booking_buttons();
        ?>
        <p><a href="<?php echo esc_url(maca_menulist_info_hub_admin_url('events')); ?>">&larr; <?php esc_html_e('Back to list', 'maca-njuvs'); ?></a></p>

        <form method="post" action="" class="maca-info-hub-social-save-form" data-object-type="event">
            <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
            <input type="hidden" name="maca_njuvs_action" value="save_event">
            <input type="hidden" name="event_id" value="<?php echo esc_attr((string) $event_id); ?>">

            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="event_title"><?php esc_html_e('Title', 'maca-njuvs'); ?></label></th>
                    <td><input type="text" id="event_title" name="event_title" class="regular-text" value="<?php echo esc_attr($title); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="event_description"><?php esc_html_e('Description', 'maca-njuvs'); ?></label></th>
                    <td>
                        <?php $this->render_rich_text_editor('event_description', 'event_description', $description, 8); ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="event_location"><?php esc_html_e('Location', 'maca-njuvs'); ?></label></th>
                    <td><input type="text" id="event_location" name="event_location" class="regular-text" value="<?php echo esc_attr($location); ?>"></td>
                </tr>
                <tr>
                    <th><label for="event_image_url"><?php esc_html_e('Image', 'maca-njuvs'); ?></label></th>
                    <td>
                        <input type="text" id="event_image_url" name="event_image_url" class="regular-text" value="<?php echo esc_attr($image_url); ?>">
                        <button type="button" class="button maca-info-hub-upload" data-target="#event_image_url"><?php esc_html_e('Select image', 'maca-njuvs'); ?></button>
                    </td>
                </tr>
                <tr>
                    <th><label for="event_price"><?php esc_html_e('Price', 'maca-njuvs'); ?></label></th>
                    <td>
                        <input type="text" id="event_price" name="event_price" class="small-text" value="<?php echo esc_attr($price); ?>" inputmode="decimal">
                        <p class="description"><?php esc_html_e('Optional. Shown on the website when set.', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('When', 'maca-njuvs'); ?></th>
                    <td>
                        <label><input type="checkbox" name="event_is_all_day" value="1" <?php checked($is_all_day); ?>> <?php esc_html_e('All day', 'maca-njuvs'); ?></label>
                        <p>
                            <label for="event_start_at"><?php esc_html_e('Start', 'maca-njuvs'); ?></label>
                            <input type="datetime-local" id="event_start_at" name="event_start_at" value="<?php echo esc_attr($start_at); ?>" required>
                        </p>
                        <p>
                            <label for="event_end_at"><?php esc_html_e('End', 'maca-njuvs'); ?></label>
                            <input type="datetime-local" id="event_end_at" name="event_end_at" value="<?php echo esc_attr($end_at); ?>" required>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="event_recurrence_type"><?php esc_html_e('Recurrence', 'maca-njuvs'); ?></label></th>
                    <td>
                        <select id="event_recurrence_type" name="event_recurrence_type">
                            <?php foreach (array('none', 'daily', 'weekly', 'monthly') as $type_key) : ?>
                                <option value="<?php echo esc_attr($type_key); ?>" <?php selected($recurrence_type, $type_key); ?>>
                                    <?php echo esc_html(maca_menulist_info_hub_recurrence_label($type_key)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="maca-info-recurrence-fields" style="margin-top:12px;">
                            <p>
                                <label for="event_recurrence_interval"><?php esc_html_e('Every', 'maca-njuvs'); ?></label>
                                <input type="number" id="event_recurrence_interval" name="event_recurrence_interval" min="1" max="52" value="<?php echo esc_attr((string) $recurrence_interval); ?>" style="width:4em;">
                                <span id="maca-info-recurrence-unit"><?php esc_html_e('week(s)', 'maca-njuvs'); ?></span>
                            </p>
                            <fieldset id="maca-info-recurrence-weekdays" style="border:0;padding:0;margin:0 0 12px;">
                                <legend class="screen-reader-text"><?php esc_html_e('Weekdays', 'maca-njuvs'); ?></legend>
                                <?php
                                $weekday_labels = maca_menulist_weekday_short_labels_ordered();
                                foreach ($weekday_labels as $day => $label) :
                                    ?>
                                    <label style="margin-right:10px;">
                                        <input type="checkbox" name="event_days_of_week[]" value="<?php echo esc_attr((string) $day); ?>" <?php checked(in_array($day, $recurrence_days, true)); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                            <p>
                                <label for="event_recurrence_until"><?php esc_html_e('Ends on (optional)', 'maca-njuvs'); ?></label>
                                <input type="date" id="event_recurrence_until" name="event_recurrence_until" value="<?php echo esc_attr($recurrence_until); ?>">
                            </p>
                            <p>
                                <label for="event_recurrence_count"><?php esc_html_e('Or after number of occurrences (optional)', 'maca-njuvs'); ?></label>
                                <input type="number" id="event_recurrence_count" name="event_recurrence_count" min="1" value="<?php echo esc_attr($recurrence_count !== '' ? (string) $recurrence_count : ''); ?>" style="width:6em;">
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Active', 'maca-njuvs'); ?></th>
                    <td><label><input type="checkbox" name="event_is_active" value="1" <?php checked($is_active); ?>> <?php esc_html_e('Show on website', 'maca-njuvs'); ?></label></td>
                </tr>
                <?php if ($can_show_booking) : ?>
                <tr>
                    <th><?php esc_html_e('Table booking', 'maca-njuvs'); ?></th>
                    <td>
                        <label><input type="checkbox" name="event_show_booking_button" value="1" <?php checked($show_booking_button); ?>> <?php esc_html_e('Show book-a-table button on this event', 'maca-njuvs'); ?></label>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php esc_html_e('Publishing', 'maca-njuvs'); ?></th>
                    <td>
                        <?php $this->render_social_publish_fields('event', $share_web, $share_facebook, $share_instagram, $event); ?>
                        <p class="description"><?php esc_html_e('For recurring events, social posts are sent once for the series (first occurrence only).', 'maca-njuvs'); ?></p>
                    </td>
                </tr>
                <?php if ($this->user_can_manage_social() && $event_id > 0 && ($share_facebook || $share_instagram)) : ?>
                <tr>
                    <th><?php esc_html_e('Social status', 'maca-njuvs'); ?></th>
                    <td>
                        <?php if ($share_facebook) : ?>
                            <p><?php echo esc_html__('Facebook:', 'maca-njuvs') . ' ' . esc_html(maca_menulist_info_hub_social_status_label((string) ($event->social_fb_status ?? 'skipped'))); ?></p>
                        <?php endif; ?>
                        <?php if ($share_instagram) : ?>
                            <p><?php echo esc_html__('Instagram:', 'maca-njuvs') . ' ' . esc_html(maca_menulist_info_hub_social_status_label((string) ($event->social_ig_status ?? 'skipped'))); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <?php submit_button($event_id > 0 ? __('Update event', 'maca-njuvs') : __('Add event', 'maca-njuvs')); ?>
        </form>

        <?php if ($event_id > 0) : ?>
            <?php if ($recurrence_type !== 'none') : ?>
                <h2><?php esc_html_e('Occurrence exceptions', 'maca-njuvs'); ?></h2>
                <p class="description"><?php esc_html_e('Cancel or reschedule a single date in a recurring series.', 'maca-njuvs'); ?></p>

                <?php if (!empty($exceptions)) : ?>
                    <table class="widefat striped" style="max-width:960px;margin-bottom:16px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Date', 'maca-njuvs'); ?></th>
                                <th><?php esc_html_e('Type', 'maca-njuvs'); ?></th>
                                <th><?php esc_html_e('New time', 'maca-njuvs'); ?></th>
                                <th><?php esc_html_e('Note', 'maca-njuvs'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exceptions as $exception) : ?>
                                <tr>
                                    <td><?php echo esc_html((string) $exception->occurrence_date); ?></td>
                                    <td><?php echo esc_html((string) $exception->exception_type); ?></td>
                                    <td>
                                        <?php
                                        if ($exception->exception_type === 'modified') {
                                            echo esc_html(trim((string) $exception->new_start_at . ' – ' . (string) $exception->new_end_at));
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($exception->note ?: '—'); ?></td>
                                    <td>
                                        <form method="post" action="" style="display:inline;" onsubmit="return confirm('<?php echo esc_js(__('Remove this exception?', 'maca-njuvs')); ?>');">
                                            <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
                                            <input type="hidden" name="maca_njuvs_action" value="delete_event_exception">
                                            <input type="hidden" name="exception_id" value="<?php echo esc_attr((string) $exception->id); ?>">
                                            <button type="submit" class="button button-small"><?php esc_html_e('Remove', 'maca-njuvs'); ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <form method="post" action="" style="max-width:960px;">
                    <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
                    <input type="hidden" name="maca_njuvs_action" value="save_event_exception">
                    <input type="hidden" name="event_id" value="<?php echo esc_attr((string) $event_id); ?>">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th><label for="exception_occurrence_date"><?php esc_html_e('Occurrence date', 'maca-njuvs'); ?></label></th>
                            <td><input type="date" id="exception_occurrence_date" name="exception_occurrence_date" required></td>
                        </tr>
                        <tr>
                            <th><label for="exception_type"><?php esc_html_e('Type', 'maca-njuvs'); ?></label></th>
                            <td>
                                <select id="exception_type" name="exception_type">
                                    <option value="cancelled"><?php esc_html_e('Cancelled', 'maca-njuvs'); ?></option>
                                    <option value="modified"><?php esc_html_e('Modified time', 'maca-njuvs'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="maca-info-exception-modified">
                            <th><?php esc_html_e('New start / end', 'maca-njuvs'); ?></th>
                            <td>
                                <input type="datetime-local" name="exception_new_start_at">
                                <input type="datetime-local" name="exception_new_end_at">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="exception_note"><?php esc_html_e('Note', 'maca-njuvs'); ?></label></th>
                            <td><input type="text" id="exception_note" name="exception_note" class="regular-text"></td>
                        </tr>
                    </table>
                    <?php submit_button(__('Add exception', 'maca-njuvs'), 'secondary'); ?>
                </form>
            <?php endif; ?>

            <form method="post" action="" onsubmit="return confirm('<?php echo esc_js(__('Delete this event?', 'maca-njuvs')); ?>');">
                <?php wp_nonce_field('maca_njuvs_save', 'maca_nonce'); ?>
                <input type="hidden" name="maca_njuvs_action" value="delete_event">
                <input type="hidden" name="event_id" value="<?php echo esc_attr((string) $event_id); ?>">
                <?php submit_button(__('Delete', 'maca-njuvs'), 'delete'); ?>
            </form>
        <?php endif; ?>

        <?php $this->render_media_uploader_script(); ?>
        <?php $this->render_recurrence_form_script(); ?>
        <?php
    }

    /**
     * Toggle recurrence admin fields.
     *
     * @return void
     */
    private function render_recurrence_form_script() {
        ?>
        <script>
        jQuery(function($) {
            function syncRecurrenceFields() {
                var type = $('#event_recurrence_type').val() || 'none';
                var $wrap = $('#maca-info-recurrence-fields');
                var $weekdays = $('#maca-info-recurrence-weekdays');
                var $unit = $('#maca-info-recurrence-unit');

                if (type === 'none') {
                    $wrap.hide();
                    return;
                }

                $wrap.show();
                $weekdays.toggle(type === 'weekly');

                if (type === 'daily') {
                    $unit.text(<?php echo wp_json_encode(__('day(s)', 'maca-njuvs')); ?>);
                } else if (type === 'weekly') {
                    $unit.text(<?php echo wp_json_encode(__('week(s)', 'maca-njuvs')); ?>);
                } else {
                    $unit.text(<?php echo wp_json_encode(__('month(s)', 'maca-njuvs')); ?>);
                }
            }

            $('#event_recurrence_type').on('change', syncRecurrenceFields);
            syncRecurrenceFields();

            function syncExceptionFields() {
                var type = $('#exception_type').val() || 'cancelled';
                $('.maca-info-exception-modified').toggle(type === 'modified');
            }

            $('#exception_type').on('change', syncExceptionFields);
            syncExceptionFields();
        });
        </script>
        <?php
    }

    /**
     * Inline media uploader script.
     *
     * @return void
     */
    private function render_media_uploader_script() {
        ?>
        <script>
        jQuery(function($) {
            $('.maca-info-hub-upload').on('click', function(e) {
                e.preventDefault();
                var target = $(this).data('target');
                var frame = wp.media({
                    title: <?php echo wp_json_encode(__('Select image', 'maca-njuvs')); ?>,
                    button: { text: <?php echo wp_json_encode(__('Use image', 'maca-njuvs')); ?> },
                    multiple: false
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $(target).val(attachment.url);
                });
                frame.open();
            });
        });
        </script>
        <?php
    }
}
