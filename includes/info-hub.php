<?php
/**
 * maca Njuvs — business logic and frontend rendering.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin page slug.
 *
 * @return string
 */
function maca_njuvs_info_hub_admin_page() {
    return 'maca-njuvs';
}

/**
 * Admin tabs.
 *
 * @return array<string, array{label: string}>
 */
function maca_njuvs_info_hub_admin_tabs() {
    $tabs = array(
        'news' => array(
            'label' => __('News', 'maca-njuvs'),
        ),
        'events' => array(
            'label' => __('Events', 'maca-njuvs'),
        ),
        'social' => array(
            'label' => __('Social media', 'maca-njuvs'),
        ),
        'settings' => array(
            'label' => __('Settings', 'maca-njuvs'),
        ),
        'import' => array(
            'label' => __('Import', 'maca-njuvs'),
        ),
        'guide' => array(
            'label' => __('Guide', 'maca-njuvs'),
        ),
    );

    if (function_exists('maca_njuvs_user_can_manage_info_hub_social') && !maca_njuvs_user_can_manage_info_hub_social()) {
        unset($tabs['social']);
    }

    return $tabs;
}

/**
 * Build admin URL.
 *
 * @param string $tab    Tab id.
 * @param array<string, string|int> $args Extra query args.
 * @return string
 */
function maca_njuvs_info_hub_admin_url($tab = 'news', $args = array()) {
    $params = array_merge(
        array(
            'page' => maca_njuvs_info_hub_admin_page(),
            'tab' => sanitize_key($tab),
        ),
        $args
    );

    return add_query_arg($params, admin_url('admin.php'));
}

/**
 * Path to the localized maca Njuvs user guide markdown file.
 *
 * @return string
 */
function maca_njuvs_info_hub_guide_file() {
    return maca_njuvs_get_localized_doc_file('INFO-HUB-GUIDE', 'en');
}

/**
 * Render the maca Njuvs user guide HTML.
 *
 * @return string
 */
function maca_njuvs_info_hub_render_guide_html() {
    if (!function_exists('maca_njuvs_render_markdown_file')) {
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/markdown.php';
    }

    $guide_file = maca_njuvs_info_hub_guide_file();

    if (!file_exists($guide_file)) {
        return '';
    }

    $html = maca_njuvs_render_markdown_file($guide_file);

    if ($html === '') {
        return '';
    }

    $ics_url = function_exists('maca_njuvs_get_info_events_ics_url')
        ? maca_njuvs_get_info_events_ics_url()
        : '';
    $webcal_url = function_exists('maca_njuvs_get_info_events_webcal_url')
        ? maca_njuvs_get_info_events_webcal_url()
        : '';

    return str_replace(
        array(
            '{{ICAL_URL}}',
            '{{WEBCAL_URL}}',
        ),
        array(
            esc_html($ics_url),
            esc_html($webcal_url),
        ),
        $html
    );
}

/**
 * Whether maca Njuvs is licensed for this site.
 *
 * @return bool
 */
function maca_njuvs_info_hub_feature_available() {
    return true;
}

/**
 * Whether maca Njuvs publishing is enabled on this site.
 *
 * @return bool
 */
function maca_njuvs_enabled() {
    if (!maca_njuvs_info_hub_feature_available()) {
        return false;
    }

    return get_option('maca_njuvs_enabled', '1') === '1';
}

/**
 * Whether any news item is ready for the public website.
 *
 * @return bool
 */
function maca_njuvs_info_hub_has_publishable_news() {
    if (!function_exists('maca_njuvs_db_get_info_news_items')) {
        return false;
    }

    maca_njuvs_db_ensure_info_hub_tables();

    foreach (maca_njuvs_db_get_info_news_items() as $item) {
        if (maca_njuvs_info_hub_get_news_visibility_blockers($item) === array()) {
            return true;
        }
    }

    return false;
}

/**
 * Add body class when a news banner is on the page.
 *
 * @return void
 */
function maca_njuvs_info_hub_flag_news_banner_page() {
    static $flagged = false;

    if ($flagged) {
        return;
    }

    $flagged = true;

    add_filter(
        'body_class',
        static function ($classes) {
            $classes[] = 'maca-has-info-news-banner';

            return $classes;
        }
    );
}

/**
 * Register shortcodes.
 *
 * @return void
 */
function maca_njuvs_info_hub_register_shortcodes() {
    add_shortcode('maca_njuvs_news', 'maca_njuvs_info_hub_shortcode_news');
    add_shortcode('maca_njuvs_events', 'maca_njuvs_info_hub_shortcode_events');
}

/**
 * @param array<string, string>|string $atts Shortcode attributes.
 * @return string
 */
function maca_njuvs_info_hub_shortcode_news($atts) {
    $atts = shortcode_atts(
        array(
            'limit' => '5',
            'show_image' => '1',
            'show_date' => '1',
            'show_excerpt' => '1',
            'layout' => 'list',
            'banner_scroll' => '1',
        ),
        is_array($atts) ? $atts : array(),
        'maca_njuvs_news'
    );

    return maca_njuvs_render_info_news_list(
        array(
            'limit' => max(1, min(50, intval($atts['limit']))),
            'showImage' => $atts['show_image'] === '1',
            'showDate' => $atts['show_date'] === '1',
            'showExcerpt' => $atts['show_excerpt'] === '1',
            'layout' => maca_njuvs_info_hub_sanitize_news_layout($atts['layout']),
            'bannerScroll' => $atts['banner_scroll'] === '1',
        )
    );
}

/**
 * @param array<string, string>|string $atts Shortcode attributes.
 * @return string
 */
function maca_njuvs_info_hub_shortcode_events($atts) {
    $atts = shortcode_atts(
        array(
            'limit' => '10',
            'show_image' => '1',
            'show_location' => '1',
            'view' => 'list',
            'show_subscribe' => '1',
        ),
        is_array($atts) ? $atts : array(),
        'maca_njuvs_events'
    );

    return maca_njuvs_render_info_events_list(
        array(
            'limit' => max(1, min(50, intval($atts['limit']))),
            'showImage' => $atts['show_image'] === '1',
            'showLocation' => $atts['show_location'] === '1',
            'view' => sanitize_key($atts['view']) === 'month' ? 'month' : 'list',
            'showSubscribe' => !isset($atts['show_subscribe']) || $atts['show_subscribe'] === '1',
        )
    );
}

/**
 * Enqueue frontend styles once per request.
 *
 * @return void
 */
function maca_njuvs_info_hub_enqueue_assets() {
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;

    wp_enqueue_style(
        'maca-njuvs-info-hub',
        MACA_NJUVS_PLUGIN_URL . 'assets/css/info-hub.css',
        array(),
        MACA_NJUVS_VERSION
    );

    wp_enqueue_script(
        'maca-njuvs-info-hub',
        MACA_NJUVS_PLUGIN_URL . 'assets/js/info-hub.js',
        array(),
        MACA_NJUVS_VERSION,
        true
    );

    wp_localize_script(
        'maca-njuvs-info-hub',
        'macaNjuvsInfoHub',
        array(
            'feedUrlCopied' => __('Feed URL copied.', 'maca-njuvs'),
        )
    );
}

/**
 * Treat empty / zero MySQL datetimes as unset.
 *
 * @param mixed $value Raw DB value.
 * @return string Empty string when unset, otherwise trimmed datetime.
 */
function maca_njuvs_info_hub_normalize_stored_datetime($value) {
    $value = trim((string) $value);

    if ($value === '' || $value === '0000-00-00 00:00:00' || $value === '0000-00-00') {
        return '';
    }

    return $value;
}

/**
 * Resolve news status for display (includes due scheduled items).
 *
 * @param object $news News row.
 * @return bool
 */
function maca_njuvs_info_hub_news_is_public($news) {
    return maca_njuvs_info_hub_get_news_visibility_blockers($news) === array();
}

/**
 * Why a news item is hidden on the website (empty array = visible).
 *
 * @param object|null $news News row.
 * @return array<int, string>
 */
function maca_njuvs_info_hub_get_news_visibility_blockers($news) {
    if (!$news) {
        return array(__('News item is missing.', 'maca-njuvs'));
    }

    $reasons = array();

    $status = isset($news->status) ? (string) $news->status : '';
    $now = function_exists('maca_njuvs_wp_now_mysql')
        ? maca_njuvs_wp_now_mysql()
        : current_time('mysql');
    $publish_at = maca_njuvs_info_hub_normalize_stored_datetime($news->publish_at ?? '');
    $expires_at = maca_njuvs_info_hub_normalize_stored_datetime($news->expires_at ?? '');
    $is_live_status = in_array($status, array('published', 'scheduled'), true);

    if ((int) ($news->share_web ?? 0) !== 1 && !$is_live_status) {
        $reasons[] = __('“Website” is not checked under Publishing.', 'maca-njuvs');
    }

    if ($status === 'draft') {
        $reasons[] = __('Status is Draft — set to Published and save.', 'maca-njuvs');
    } elseif ($status === 'archived') {
        $reasons[] = __('Status is Archived.', 'maca-njuvs');
    } elseif ($status === 'scheduled') {
        if ($publish_at === '') {
            $reasons[] = __('Scheduled without a publish date.', 'maca-njuvs');
        } elseif ($publish_at > $now) {
            $reasons[] = sprintf(
                /* translators: %s: formatted datetime */
                __('Scheduled for %s.', 'maca-njuvs'),
                maca_njuvs_format_wp_datetime($publish_at)
            );
        }
    } elseif ($status !== 'published' && $status !== 'scheduled') {
        $reasons[] = __('Status is not published.', 'maca-njuvs');
    }

    if ($status === 'published' && $publish_at !== '' && $publish_at > $now) {
        $reasons[] = sprintf(
            /* translators: %s: formatted datetime */
            __('Publish date is in the future (%s).', 'maca-njuvs'),
            maca_njuvs_format_wp_datetime($publish_at)
        );
    }

    if ($expires_at !== '' && $expires_at <= $now) {
        $reasons[] = __('Expiry date has passed.', 'maca-njuvs');
    }

    return $reasons;
}

/**
 * Promote scheduled news whose publish time has passed.
 *
 * @return void
 */
function maca_njuvs_info_hub_promote_due_scheduled_news() {
    if (!function_exists('maca_njuvs_db_ensure_info_hub_tables')) {
        return;
    }

    maca_njuvs_db_ensure_info_hub_tables();

    global $wpdb;

    $table = maca_njuvs_db_info_news_table();
    $now = function_exists('maca_njuvs_wp_now_mysql')
        ? maca_njuvs_wp_now_mysql()
        : current_time('mysql');

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE `{$table}` SET status = 'published' WHERE status = 'scheduled' AND publish_at IS NOT NULL AND publish_at <= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is plugin-controlled.
            $now
        )
    );
}

/**
 * Fetch published news for the website.
 *
 * @param array<string, mixed> $args Query args.
 * @return array<int, object>
 */
function maca_njuvs_info_hub_get_public_news($args = array()) {
    $defaults = array(
        'limit' => 10,
        'offset' => 0,
        'ignore_module_toggle' => false,
    );
    $args = wp_parse_args($args, $defaults);

    if (!maca_njuvs_info_hub_feature_available()) {
        return array();
    }

    if (!maca_njuvs_enabled() && empty($args['ignore_module_toggle'])) {
        return array();
    }

    maca_njuvs_db_ensure_info_hub_tables();
    maca_njuvs_info_hub_promote_due_scheduled_news();

    global $wpdb;

    $table = maca_njuvs_db_info_news_table();
    $limit = max(1, min(100, intval($args['limit'])));
    $offset = max(0, intval($args['offset']));

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is plugin-controlled.
    $sql = 'SELECT * FROM `' . $table . '`
        WHERE (share_web = 1 OR status IN (\'published\', \'scheduled\'))
        ORDER BY COALESCE(NULLIF(publish_at, \'0000-00-00 00:00:00\'), NULLIF(publish_at, \'\'), updated_at) DESC, id DESC
        LIMIT %d OFFSET %d';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
    $rows = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));

    if (empty($rows)) {
        return array();
    }

    $public = array();
    foreach ($rows as $row) {
        if (maca_njuvs_info_hub_news_is_public($row)) {
            $public[] = $row;
        }
    }

    return $public;
}

/**
 * Fetch upcoming event occurrences for the website (includes recurring).
 *
 * @param array<string, mixed> $args Query args.
 * @return array<int, object>
 */
function maca_njuvs_info_hub_get_public_events($args = array()) {
    $defaults = array(
        'limit' => 20,
        'offset' => 0,
    );
    $args = wp_parse_args($args, $defaults);

    return maca_njuvs_info_hub_get_occurrences(
        array(
            'range_start' => wp_date('Y-m-d'),
            'range_end' => function_exists('maca_njuvs_wp_now_modify')
                ? maca_njuvs_wp_now_modify('+6 months', 'Y-m-d')
                : wp_date('Y-m-d', strtotime('+6 months')),
            'limit' => (int) $args['limit'],
            'offset' => (int) $args['offset'],
            'upcoming_only' => true,
        )
    );
}

/**
 * Anchor id for list view occurrence items.
 *
 * @param object $occurrence Occurrence object.
 * @return string
 */
function maca_njuvs_info_hub_occurrence_list_anchor_id($occurrence) {
    $item_id = (int) $occurrence->event_id;
    $occ_date = (string) $occurrence->occurrence_date;

    return 'maca-info-event-' . $item_id . '-' . str_replace('-', '', $occ_date);
}

/**
 * Anchor id for calendar detail panels (separate from list ids to avoid duplicates).
 *
 * @param object $occurrence Occurrence object.
 * @return string
 */
function maca_njuvs_info_hub_occurrence_detail_anchor_id($occurrence) {
    $item_id = (int) $occurrence->event_id;
    $occ_date = (string) $occurrence->occurrence_date;

    return 'maca-info-event-detail-' . $item_id . '-' . str_replace('-', '', $occ_date);
}

/**
 * Render one event occurrence (list item or calendar detail panel).
 *
 * @param object               $occurrence Occurrence object.
 * @param array<string, mixed> $args       Render args.
 * @return void
 */
function maca_njuvs_info_hub_render_occurrence_detail($occurrence, $args = array()) {
    $args = wp_parse_args(
        is_array($args) ? $args : array(),
        array(
            'show_image' => true,
            'show_location' => true,
            'wrapper' => 'article',
            'anchor_id' => '',
            'collapsible' => false,
        )
    );

    $event = $occurrence->event;
    $title = maca_njuvs_info_hub_get_event_title($event);
    $description = maca_njuvs_info_hub_get_event_description($event);
    $location = maca_njuvs_info_hub_get_event_location($event);
    $price_label = maca_njuvs_info_hub_get_event_price_label($event);
    $anchor_id = (string) $args['anchor_id'];

    if ($anchor_id === '') {
        $anchor_id = maca_njuvs_info_hub_occurrence_list_anchor_id($occurrence);
    }

    $classes = array('maca-info-event-detail');

    if ($args['collapsible']) {
        $classes[] = 'maca-info-event-detail--collapsible';
    }

    if ($args['wrapper'] === 'li') {
        $classes[] = 'maca-info-events-item';
    }

    $tag = sanitize_key((string) $args['wrapper']);
    $allowed_wrappers = array('li', 'article', 'div');

    if (!in_array($tag, $allowed_wrappers, true)) {
        $tag = 'article';
    }

    $open = '<' . $tag . ' class="' . esc_attr(implode(' ', $classes)) . '" id="' . esc_attr($anchor_id) . '">';
    $close = '</' . $tag . '>';

    echo wp_kses($open, array('article' => array('class' => true, 'id' => true), 'div' => array('class' => true, 'id' => true), 'li' => array('class' => true, 'id' => true)));
    ?>
        <?php if (!empty($args['show_image']) && !empty($event->image_url)) : ?>
            <div class="maca-info-events-image">
                <img src="<?php echo esc_url((string) $event->image_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
            </div>
        <?php endif; ?>
        <div class="maca-info-events-body">
            <time class="maca-info-events-datetime" datetime="<?php echo esc_attr((string) $occurrence->start_at); ?>">
                <?php echo esc_html(maca_njuvs_info_hub_format_occurrence_datetime($occurrence)); ?>
            </time>
            <h3 class="maca-info-events-title"><?php echo esc_html($title); ?></h3>
            <?php if ($price_label !== '') : ?>
                <p class="maca-info-events-price"><?php echo esc_html($price_label); ?></p>
            <?php endif; ?>
            <?php if (!empty($args['show_location']) && $location !== '') : ?>
                <p class="maca-info-events-location"><?php echo esc_html($location); ?></p>
            <?php endif; ?>
            <?php if ($description !== '') : ?>
                <div class="maca-info-events-description"><?php echo wp_kses_post(maca_njuvs_info_hub_format_rich_text($description)); ?></div>
            <?php endif; ?>
        </div>
    <?php
    echo wp_kses($close, array('article' => array(), 'div' => array(), 'li' => array()));
}

/**
 * Render one event occurrence list item.
 *
 * @param object $occurrence   Occurrence object.
 * @param bool   $show_image   Show image.
 * @param bool   $show_location Show location.
 * @return void
 */
function maca_njuvs_info_hub_render_occurrence_item($occurrence, $show_image, $show_location) {
    maca_njuvs_info_hub_render_occurrence_detail(
        $occurrence,
        array(
            'show_image' => $show_image,
            'show_location' => $show_location,
            'wrapper' => 'li',
        )
    );
}

/**
 * Bilingual news title.
 *
 * @param object $news News row.
 * @return string
 */
function maca_njuvs_info_hub_get_news_title($news) {
    return maca_njuvs_get_object_bilingual_field($news, 'title', 'title_en');
}

/**
 * Bilingual news excerpt (falls back to trimmed content).
 *
 * @param object $news News row.
 * @return string
 */
function maca_njuvs_info_hub_get_news_excerpt($news) {
    $excerpt = maca_njuvs_get_object_bilingual_field($news, 'excerpt', 'excerpt_en');

    if ($excerpt !== '') {
        return $excerpt;
    }

    $content = maca_njuvs_get_object_bilingual_field($news, 'content', 'content_en');
    $plain = wp_strip_all_tags($content);

    if ($plain === '') {
        return '';
    }

    return wp_trim_words($plain, 30, '…');
}

/**
 * Bilingual news body HTML.
 *
 * @param object $news News row.
 * @return string
 */
function maca_njuvs_info_hub_get_news_content($news) {
    return maca_njuvs_get_object_bilingual_field($news, 'content', 'content_en');
}

/**
 * Sanitize rich text from admin (links and basic HTML).
 *
 * @param string $content Raw HTML.
 * @return string
 */
function maca_njuvs_info_hub_sanitize_rich_text($content) {
    $content = (string) wp_unslash($content);

    // Embedded data-URI images (paste) can exceed PHP post_max_size on save.
    $content = preg_replace('/<img\b[^>]*\bsrc=["\']data:[^"\']*["\'][^>]*>/i', '', $content);
    $content = preg_replace('/\bsrc=["\']data:[^"\']*["\']/i', '', $content);

    return wp_kses_post($content);
}

/**
 * Format stored rich text for frontend output.
 *
 * @param string $content Stored HTML.
 * @return string
 */
function maca_njuvs_info_hub_format_rich_text($content) {
    $content = trim((string) $content);

    if ($content === '') {
        return '';
    }

    return wp_kses_post(wpautop($content));
}

/**
 * Convert stored rich text HTML to plain text (paragraphs preserved).
 *
 * @param string $html Stored HTML.
 * @return string
 */
function maca_njuvs_info_hub_rich_text_to_plain($html) {
    $html = trim((string) $html);

    if ($html === '') {
        return '';
    }

    $html = preg_replace('/<\/p>\s*<p>/i', "\n\n", $html);
    $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $html = preg_replace('/<\/p>/i', "\n\n", $html);
    $html = preg_replace('/<\/li>/i', "\n", $html);
    $html = preg_replace('/<\/h[1-6]>/i', "\n\n", $html);

    $plain = wp_strip_all_tags($html);
    $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $lines = preg_split('/\r\n|\r|\n/', $plain);
    $lines = array_map('trim', is_array($lines) ? $lines : array());
    $plain = implode("\n", $lines);
    $plain = preg_replace("/\n{3,}/", "\n\n", $plain);

    return trim($plain);
}

/**
 * Plain-text news body for social captions and similar exports.
 *
 * @param object $news News row.
 * @return string
 */
function maca_njuvs_info_hub_get_news_content_plain($news) {
    return maca_njuvs_info_hub_rich_text_to_plain(maca_njuvs_info_hub_get_news_content($news));
}

/**
 * Bilingual event title.
 *
 * @param object $event Event row.
 * @return string
 */
function maca_njuvs_info_hub_get_event_title($event) {
    return maca_njuvs_get_object_bilingual_field($event, 'title', 'title_en');
}

/**
 * Bilingual event description.
 *
 * @param object $event Event row.
 * @return string
 */
function maca_njuvs_info_hub_get_event_description($event) {
    return maca_njuvs_get_object_bilingual_field($event, 'description', 'description_en');
}

/**
 * Bilingual event location.
 *
 * @param object $event Event row.
 * @return string
 */
function maca_njuvs_info_hub_get_event_location($event) {
    return maca_njuvs_get_object_bilingual_field($event, 'location', 'location_en');
}

/**
 * Formatted event price for guests (empty when not set).
 *
 * @param object|null $event Event row.
 * @return string
 */
function maca_njuvs_info_hub_get_event_price_label($event) {
    if (!$event || !isset($event->price) || $event->price === null || $event->price === '') {
        return '';
    }

    $amount = (float) $event->price;
    if ($amount <= 0) {
        return '';
    }

    return maca_njuvs_format_price($amount);
}

/**
 * Format a news publish date for guests.
 *
 * @param object $news News row.
 * @return string
 */
function maca_njuvs_info_hub_format_news_date($news) {
    $date = '';

    if (!empty($news->publish_at)) {
        $date = (string) $news->publish_at;
    } elseif (!empty($news->created_at)) {
        $date = (string) $news->created_at;
    }

    if ($date === '') {
        return '';
    }

    $timestamp = maca_njuvs_wp_mysql_to_timestamp($date);

    if ($timestamp === false) {
        return '';
    }

    return wp_date(get_option('date_format'), $timestamp);
}

/**
 * Format event start/end for guests.
 *
 * @param object $event Event row.
 * @return string
 */
function maca_njuvs_info_hub_format_event_datetime($event) {
    if (!$event || empty($event->start_at)) {
        return '';
    }

    $start_ts = maca_njuvs_wp_mysql_to_timestamp((string) $event->start_at);
    $end_ts = !empty($event->end_at) ? maca_njuvs_wp_mysql_to_timestamp((string) $event->end_at) : false;

    if ($start_ts === false) {
        return '';
    }

    if (!empty($event->is_all_day)) {
        $start_label = wp_date(get_option('date_format'), $start_ts);

        if ($end_ts !== false && wp_date('Y-m-d', $end_ts) !== wp_date('Y-m-d', $start_ts)) {
            return $start_label . ' – ' . wp_date(get_option('date_format'), $end_ts);
        }

        return $start_label;
    }

    $date_format = get_option('date_format');
    $time_format = get_option('time_format');
    $start_label = wp_date($date_format . ' ' . $time_format, $start_ts);

    if ($end_ts === false) {
        return $start_label;
    }

    if (wp_date('Y-m-d', $start_ts) === wp_date('Y-m-d', $end_ts)) {
        return $start_label . ' – ' . wp_date($time_format, $end_ts);
    }

    return $start_label . ' – ' . wp_date($date_format . ' ' . $time_format, $end_ts);
}

/**
 * Admin label for news status.
 *
 * @param string $status Status key.
 * @return string
 */
function maca_njuvs_info_hub_news_status_label($status) {
    $labels = array(
        'draft' => __('Draft', 'maca-njuvs'),
        'scheduled' => __('Scheduled', 'maca-njuvs'),
        'published' => __('Published', 'maca-njuvs'),
        'archived' => __('Archived', 'maca-njuvs'),
    );

    return isset($labels[ $status ]) ? $labels[ $status ] : $status;
}

/**
 * Determine stored status from admin form.
 *
 * @param string $requested_status Requested status.
 * @param string $publish_at       Publish datetime (mysql) or empty.
 * @return string
 */
function maca_njuvs_info_hub_resolve_news_status($requested_status, $publish_at) {
    $requested_status = sanitize_key($requested_status);
    $allowed = array('draft', 'scheduled', 'published', 'archived');

    if (!in_array($requested_status, $allowed, true)) {
        $requested_status = 'draft';
    }

    if ($requested_status === 'published' && $publish_at !== '') {
        $now = function_exists('maca_njuvs_wp_now_mysql')
            ? maca_njuvs_wp_now_mysql()
            : current_time('mysql');

        if ($publish_at > $now) {
            return 'scheduled';
        }
    }

    if ($requested_status === 'scheduled' && $publish_at === '') {
        return 'draft';
    }

    return $requested_status;
}

/**
 * Parse datetime-local or date input to MySQL datetime.
 *
 * @param string $value Raw input.
 * @param bool   $all_day Treat as date only.
 * @return string|null
 */
function maca_njuvs_info_hub_parse_datetime_input($value, $all_day = false) {
    if (function_exists('maca_njuvs_datetime_local_to_mysql')) {
        return maca_njuvs_datetime_local_to_mysql($value, $all_day);
    }

    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    if ($all_day && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value . ' 00:00:00';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
        return str_replace('T', ' ', $value) . ':00';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
        return $value;
    }

    return null;
}

/**
 * Allowed news block layouts.
 *
 * @param string $layout Requested layout.
 * @return string
 */
function maca_njuvs_info_hub_sanitize_news_layout($layout) {
    if ($layout === 'table') {
        $layout = 'embedded';
    }

    $layout = sanitize_key((string) $layout);
    $allowed = array('list', 'sidebar-left', 'sidebar-right', 'banner', 'embedded');

    return in_array($layout, $allowed, true) ? $layout : 'list';
}

/**
 * Render <ul> of news items.
 *
 * @param array<int, object>   $items     News rows.
 * @param array<string, mixed> $item_args Item render args.
 * @param array<string, mixed> $args      List args.
 * @return void
 */
function maca_njuvs_info_hub_render_news_items_list($items, $item_args, $args = array()) {
    $args = wp_parse_args(
        is_array($args) ? $args : array(),
        array(
            'list_class' => '',
            'aria_hidden' => false,
            'duplicate_for_ticker' => false,
        )
    );

    $classes = array('maca-info-news-items');

    if ($args['list_class'] !== '') {
        $classes[] = $args['list_class'];
    }

    $attr = 'class="' . esc_attr(implode(' ', $classes)) . '"';

    if (!empty($args['aria_hidden'])) {
        $attr .= ' aria-hidden="true"';
    }

    echo wp_kses('<ul ' . $attr . '>', array('ul' => array('class' => true, 'aria-hidden' => true)));
    foreach ($items as $item) {
        maca_njuvs_info_hub_render_news_item($item, $item_args);
    }

    if (!empty($args['duplicate_for_ticker']) && count($items) > 1) {
        foreach ($items as $item) {
            $clone_item_args = $item_args;
            $clone_item_args['omit_id'] = true;
            $clone_item_args['ticker_clone'] = true;
            maca_njuvs_info_hub_render_news_item($item, $clone_item_args);
        }
    }

    echo '</ul>';
}

/**
 * Render one news list item.
 *
 * @param object               $item News row.
 * @param array<string, mixed> $args Display args.
 * @return void
 */
function maca_njuvs_info_hub_render_news_item($item, $args = array()) {
    $args = wp_parse_args(
        is_array($args) ? $args : array(),
        array(
            'show_image' => true,
            'show_date' => true,
            'show_excerpt' => true,
            'show_content' => true,
            'opens_modal' => false,
            'compact_line' => false,
            'preview_mode' => false,
            'omit_id' => false,
            'ticker_clone' => false,
        )
    );

    $title = maca_njuvs_info_hub_get_news_title($item);
    $excerpt = maca_njuvs_info_hub_get_news_excerpt($item);
    $content = maca_njuvs_info_hub_get_news_content($item);
    $item_id = (int) $item->id;
    $modal_id = 'maca-info-news-modal-' . $item_id;
    $opens_modal = !empty($args['opens_modal']) && ($content !== '' || $excerpt !== '' || $title !== '');
    $compact_line = !empty($args['compact_line']);
    $item_classes = array('maca-info-news-item');
    $preview_notices = array();

    if (!empty($args['ticker_clone'])) {
        $item_classes[] = 'maca-info-news-item--ticker-clone';
    }

    if (!empty($args['preview_mode'])) {
        $blockers = maca_njuvs_info_hub_get_news_visibility_blockers($item);

        if ($blockers !== array()) {
            $preview_notices = $blockers;
            $item_classes[] = 'maca-info-news-item--preview-unpublished';
        } elseif (!maca_njuvs_enabled()) {
            $preview_notices = array(
                __('maca Njuvs is disabled under Settings — visitors will not see this until it is enabled.', 'maca-njuvs'),
            );
            $item_classes[] = 'maca-info-news-item--preview-unpublished';
        }
    }
    ?>
    <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>"<?php echo empty($args['omit_id']) ? ' id="maca-info-news-' . esc_attr((string) $item_id) . '"' : ''; ?><?php echo !empty($args['ticker_clone']) ? ' aria-hidden="true"' : ''; ?>>
        <?php if (!empty($preview_notices)) : ?>
            <p class="maca-info-news-preview-badge"><?php echo esc_html(implode(' ', $preview_notices)); ?></p>
        <?php endif; ?>
        <?php if ($opens_modal) : ?>
            <button
                type="button"
                class="maca-info-news-modal-trigger"
                data-news-modal="<?php echo esc_attr($modal_id); ?>"
                aria-haspopup="dialog"
                aria-controls="<?php echo esc_attr($modal_id); ?>"
                aria-label="<?php echo esc_attr(sprintf(/* translators: %s: news title */ __('Read full news: %s', 'maca-njuvs'), $title)); ?>"
            >
        <?php endif; ?>
        <?php if (!empty($args['show_image']) && !empty($item->image_url)) : ?>
            <div class="maca-info-news-image">
                <img src="<?php echo esc_url((string) $item->image_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
            </div>
        <?php endif; ?>
        <div class="maca-info-news-body">
            <?php if ($compact_line) : ?>
                <div class="maca-info-news-banner-line">
                    <?php if (!empty($args['show_date'])) : ?>
                        <time class="maca-info-news-date" datetime="<?php echo esc_attr((string) ($item->publish_at ?: $item->created_at)); ?>">
                            <?php echo esc_html(maca_njuvs_info_hub_format_news_date($item)); ?>
                        </time>
                        <span class="maca-info-news-banner-sep" aria-hidden="true">&middot;</span>
                    <?php endif; ?>
                    <span class="maca-info-news-title"><?php echo esc_html($title); ?></span>
                    <?php if (!empty($args['show_excerpt']) && $excerpt !== '') : ?>
                        <span class="maca-info-news-banner-sep" aria-hidden="true">&mdash;</span>
                        <span class="maca-info-news-excerpt"><?php echo esc_html(wp_strip_all_tags($excerpt)); ?></span>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <?php if (!empty($args['show_date'])) : ?>
                    <time class="maca-info-news-date" datetime="<?php echo esc_attr((string) ($item->publish_at ?: $item->created_at)); ?>">
                        <?php echo esc_html(maca_njuvs_info_hub_format_news_date($item)); ?>
                    </time>
                <?php endif; ?>
                <h3 class="maca-info-news-title"><?php echo esc_html($title); ?></h3>
                <?php if (!empty($args['show_excerpt']) && $excerpt !== '') : ?>
                    <div class="maca-info-news-excerpt"><?php echo wp_kses_post($excerpt); ?></div>
                <?php endif; ?>
                <?php if (!empty($args['show_content']) && $content !== '') : ?>
                    <div class="maca-info-news-content"><?php echo wp_kses_post(maca_njuvs_info_hub_format_rich_text($content)); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if ($opens_modal) : ?>
            </button>
        <?php endif; ?>
    </li>
    <?php
}

/**
 * Render popup dialog for one news item (banner layout).
 *
 * @param object $item      News row.
 * @param bool   $show_date Show date in modal.
 * @return void
 */
function maca_njuvs_info_hub_render_news_modal($item, $show_date = true) {
    $item_id = (int) $item->id;
    $modal_id = 'maca-info-news-modal-' . $item_id;
    $title = maca_njuvs_info_hub_get_news_title($item);
    $excerpt = maca_njuvs_info_hub_get_news_excerpt($item);
    $content = maca_njuvs_info_hub_get_news_content($item);
    ?>
    <dialog class="maca-info-news-modal" id="<?php echo esc_attr($modal_id); ?>" aria-labelledby="<?php echo esc_attr($modal_id); ?>-title">
        <form method="dialog">
            <button type="submit" class="maca-info-news-modal-close" aria-label="<?php esc_attr_e('Close', 'maca-njuvs'); ?>">&times;</button>
        </form>
        <div class="maca-info-news-modal-body">
            <?php if ($show_date) : ?>
                <time class="maca-info-news-date" datetime="<?php echo esc_attr((string) ($item->publish_at ?: $item->created_at)); ?>">
                    <?php echo esc_html(maca_njuvs_info_hub_format_news_date($item)); ?>
                </time>
            <?php endif; ?>
            <h2 class="maca-info-news-modal-title" id="<?php echo esc_attr($modal_id); ?>-title"><?php echo esc_html($title); ?></h2>
            <?php if (!empty($item->image_url)) : ?>
                <div class="maca-info-news-modal-image">
                    <img src="<?php echo esc_url((string) $item->image_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                </div>
            <?php endif; ?>
            <?php if ($content !== '') : ?>
                <div class="maca-info-news-modal-content"><?php echo wp_kses_post(maca_njuvs_info_hub_format_rich_text($content)); ?></div>
            <?php elseif ($excerpt !== '') : ?>
                <div class="maca-info-news-modal-excerpt"><?php echo wp_kses_post(maca_njuvs_info_hub_format_rich_text($excerpt)); ?></div>
            <?php endif; ?>
        </div>
    </dialog>
    <?php
}

/**
 * Admin-only help when no news is visible on the website.
 *
 * @return string
 */
function maca_njuvs_info_hub_render_news_admin_empty_state() {
    if (!maca_njuvs_user_can_manage_plugin()) {
        return '';
    }

    if (!maca_njuvs_enabled()) {
        return '<div class="maca-info-admin-notice">'
            . esc_html__('maca Njuvs is disabled. Enable it under maca Njuvs → Settings.', 'maca-njuvs')
            . '</div>';
    }

    $items = function_exists('maca_njuvs_db_get_info_news_items')
        ? maca_njuvs_db_get_info_news_items()
        : array();

    if (empty($items)) {
        return '<div class="maca-info-admin-notice">'
            . esc_html__('No news items exist yet. Add one under maca Njuvs → Nyheter.', 'maca-njuvs')
            . '</div>';
    }

    ob_start();
    ?>
    <div class="maca-info-admin-notice">
        <p><strong><?php esc_html_e('Nothing is visible to visitors yet.', 'maca-njuvs'); ?></strong></p>
        <ul class="maca-info-admin-notice-list">
            <?php foreach ($items as $item) : ?>
                <?php
                $blockers = maca_njuvs_info_hub_get_news_visibility_blockers($item);
                if ($blockers === array()) {
                    continue;
                }
                ?>
                <li>
                    <strong><?php echo esc_html(maca_njuvs_info_hub_get_news_title($item)); ?></strong>
                    <ul>
                        <?php foreach ($blockers as $reason) : ?>
                            <li><?php echo esc_html($reason); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo esc_url(maca_njuvs_info_hub_admin_url('news', array('action' => 'edit', 'id' => (int) $item->id))); ?>">
                        <?php esc_html_e('Edit news item', 'maca-njuvs'); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return (string) ob_get_clean();
}

/**
 * Render news list for block/shortcode.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param array<string, mixed> $args       Extra args.
 * @return string
 */
function maca_njuvs_render_info_news_list($attributes = array(), $args = array()) {
    static $front_banner_rendered = false;

    $args = is_array($args) ? $args : array();
    $preview = !empty($args['preview']);

    if (!$preview && !maca_njuvs_enabled()) {
        return '';
    }

    if ($preview && !maca_njuvs_enabled()) {
        maca_njuvs_info_hub_enqueue_assets();

        return '<div class="maca-info-hub"><p class="maca-info-empty">'
            . esc_html__('Enable maca Njuvs under Settings.', 'maca-njuvs')
            . '</p></div>';
    }

    $attributes = is_array($attributes) ? $attributes : array();
    $limit = isset($attributes['limit']) ? max(1, min(50, intval($attributes['limit']))) : 5;
    $show_image = !array_key_exists('showImage', $attributes) || !empty($attributes['showImage']);
    $show_date = !array_key_exists('showDate', $attributes) || !empty($attributes['showDate']);
    $show_excerpt = !array_key_exists('showExcerpt', $attributes) || !empty($attributes['showExcerpt']);
    $layout = maca_njuvs_info_hub_sanitize_news_layout($attributes['layout'] ?? 'list');
    $is_compact_layout = in_array($layout, array('sidebar-left', 'sidebar-right', 'banner', 'embedded'), true);
    $banner_scroll_requested = $layout === 'banner' && !empty($attributes['bannerScroll']);

    $fetch_args = array('limit' => $limit);

    if ($preview) {
        $fetch_args['ignore_module_toggle'] = true;
    }

    $items = maca_njuvs_info_hub_get_public_news($fetch_args);

    if (empty($items) && !$preview && maca_njuvs_enabled() && function_exists('maca_njuvs_db_get_info_news_items')) {
        foreach (maca_njuvs_db_get_info_news_items() as $row) {
            if (maca_njuvs_info_hub_news_is_public($row)) {
                $items[] = $row;
            }

            if (count($items) >= $limit) {
                break;
            }
        }
    }

    if (empty($items) && $preview && maca_njuvs_user_can_manage_plugin() && function_exists('maca_njuvs_db_get_info_news_items')) {
        $items = array_slice(maca_njuvs_db_get_info_news_items(), 0, $limit);
    }

    if ($layout === 'banner' && empty($items) && !$preview) {
        maca_njuvs_info_hub_enqueue_assets();

        if (maca_njuvs_user_can_manage_plugin()) {
            return '<div class="maca-info-hub maca-info-admin-notice">'
                . maca_njuvs_info_hub_render_news_admin_empty_state()
                . '</div>';
        }

        return '';
    }

    if ($layout === 'banner' && !empty($items) && !$preview && $front_banner_rendered) {
        return '';
    }

    // Ticker duplicates items for a seamless loop — only when 2+ news (one item would show twice side by side).
    $banner_scroll = $banner_scroll_requested && count($items) > 1;

    maca_njuvs_info_hub_enqueue_assets();

    $wrapper_classes = array('maca-info-hub', 'maca-info-news-list');

    if ($layout !== 'list') {
        $wrapper_classes[] = 'maca-info-news-list--' . $layout;
    }

    if ($banner_scroll) {
        $wrapper_classes[] = 'maca-info-news-list--scroll';
    }

    if ($layout === 'banner' && count($items) > 1) {
        $wrapper_classes[] = 'maca-info-news-list--banner-multi';
    }

    if ($preview) {
        $wrapper_classes[] = 'maca-info-news-list--preview';
    }

    $item_args = array(
        'show_image' => $is_compact_layout ? false : $show_image,
        'show_date' => $show_date,
        'show_excerpt' => $show_excerpt,
        'show_content' => !$is_compact_layout,
        'opens_modal' => $is_compact_layout,
        'compact_line' => $layout === 'banner',
        'preview_mode' => $preview,
    );

    ob_start();
    ?>
    <div
        class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>"
        data-layout="<?php echo esc_attr($layout); ?>"
        <?php if ($banner_scroll && !empty($items)) : ?>
            style="--maca-info-ticker-items: <?php echo esc_attr((string) count($items)); ?>;"
        <?php endif; ?>
    >
        <?php if ($preview && !maca_njuvs_enabled()) : ?>
            <div class="maca-info-admin-notice">
                <?php esc_html_e('maca Njuvs is disabled. Enable it under Settings for visitors to see news on the site.', 'maca-njuvs'); ?>
            </div>
        <?php endif; ?>
        <?php if ($layout === 'banner') : ?>
            <p class="maca-info-news-banner-label screen-reader-text"><?php esc_html_e('News', 'maca-njuvs'); ?></p>
        <?php elseif ($layout === 'embedded') : ?>
            <p class="maca-info-news-embedded-label"><?php esc_html_e('News', 'maca-njuvs'); ?></p>
        <?php elseif ($is_compact_layout) : ?>
            <p class="maca-info-news-sidebar-label"><?php esc_html_e('News', 'maca-njuvs'); ?></p>
        <?php endif; ?>
        <?php if (empty($items)) : ?>
            <p class="maca-info-empty"><?php esc_html_e('No news to show right now.', 'maca-njuvs'); ?></p>
            <?php
            if (!$preview && maca_njuvs_user_can_manage_plugin()) {
                echo wp_kses_post(maca_njuvs_info_hub_render_news_admin_empty_state());
            } elseif ($preview && maca_njuvs_user_can_manage_plugin()) {
                ?>
                <p class="maca-info-empty maca-info-empty--admin-hint"><?php esc_html_e('Published news with “Website” checked appears here. Publish date is optional.', 'maca-njuvs'); ?></p>
                <?php
            }
            ?>
        <?php else : ?>
            <?php if ($layout === 'banner' && $banner_scroll) : ?>
                <div class="maca-info-news-banner-track">
                    <div class="maca-info-news-banner-track-inner">
                        <?php
                        maca_njuvs_info_hub_render_news_items_list(
                            $items,
                            $item_args,
                            array('duplicate_for_ticker' => true)
                        );
                        ?>
                    </div>
                </div>
            <?php else : ?>
                <?php maca_njuvs_info_hub_render_news_items_list($items, $item_args); ?>
            <?php endif; ?>
            <?php if ($is_compact_layout) : ?>
                <div class="maca-info-news-modals">
                    <?php foreach ($items as $item) : ?>
                        <?php maca_njuvs_info_hub_render_news_modal($item, $show_date); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    $html = (string) ob_get_clean();

    if ($layout === 'banner' && !empty($items) && !$preview) {
        $front_banner_rendered = true;
        maca_njuvs_info_hub_flag_news_banner_page();
    }

    return $html;
}

/**
 * Render events list for block/shortcode.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param array<string, mixed> $args       Extra args.
 * @return string
 */
function maca_njuvs_render_info_events_list($attributes = array(), $args = array()) {
    $args = is_array($args) ? $args : array();
    $preview = !empty($args['preview']);

    if (!$preview && !maca_njuvs_enabled()) {
        return '';
    }

    if ($preview && !maca_njuvs_enabled()) {
        maca_njuvs_info_hub_enqueue_assets();

        return '<div class="maca-info-hub"><p class="maca-info-empty">'
            . esc_html__('Enable maca Njuvs under Settings.', 'maca-njuvs')
            . '</p></div>';
    }

    $attributes = is_array($attributes) ? $attributes : array();
    $view = isset($attributes['view']) && sanitize_key((string) $attributes['view']) === 'month' ? 'month' : 'list';

    if ($view === 'month') {
        return maca_njuvs_render_info_events_calendar($attributes, $args);
    }

    $limit = isset($attributes['limit']) ? max(1, min(50, intval($attributes['limit']))) : 10;
    $show_image = !array_key_exists('showImage', $attributes) || !empty($attributes['showImage']);
    $show_location = !array_key_exists('showLocation', $attributes) || !empty($attributes['showLocation']);
    $show_subscribe = !array_key_exists('showSubscribe', $attributes) || !empty($attributes['showSubscribe']);

    $items = maca_njuvs_info_hub_get_public_events(array('limit' => $limit));

    maca_njuvs_info_hub_enqueue_assets();

    ob_start();
    ?>
    <div class="maca-info-hub maca-info-events-list">
        <?php if ($show_subscribe) : ?>
            <?php echo wp_kses(maca_njuvs_render_info_calendar_subscribe(array('preview' => $preview, 'compact' => true)), maca_njuvs_info_hub_get_calendar_subscribe_allowed_html()); ?>
        <?php endif; ?>
        <?php if (empty($items)) : ?>
            <p class="maca-info-empty"><?php esc_html_e('No upcoming events.', 'maca-njuvs'); ?></p>
        <?php else : ?>
            <ul class="maca-info-events-items">
                <?php foreach ($items as $item) : ?>
                    <?php maca_njuvs_info_hub_render_occurrence_item($item, $show_image, $show_location); ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

/**
 * Render month calendar view for events.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param array<string, mixed> $args       Extra args.
 * @return string
 */
function maca_njuvs_render_info_events_calendar($attributes = array(), $args = array()) {
    $attributes = is_array($attributes) ? $attributes : array();
    $preview = !empty($args['preview']);
    $monday_first = true;
    $show_subscribe = !array_key_exists('showSubscribe', $attributes) || !empty($attributes['showSubscribe']);
    $show_image = !array_key_exists('showImage', $attributes) || !empty($attributes['showImage']);
    $show_location = !array_key_exists('showLocation', $attributes) || !empty($attributes['showLocation']);

    $month = $preview
        ? wp_date('Y-m')
        : maca_njuvs_info_hub_resolve_calendar_month(
            isset($attributes['month']) ? (string) $attributes['month'] : ''
        );

    $bounds = maca_njuvs_info_hub_month_bounds($month);

    if ($bounds === null) {
        $month = wp_date('Y-m');
        $bounds = maca_njuvs_info_hub_month_bounds($month);
    }

    if ($bounds === null) {
        return '';
    }

    list($range_start, $range_end) = $bounds;

    $occurrences = maca_njuvs_info_hub_get_occurrences(
        array(
            'range_start' => $range_start,
            'range_end' => $range_end,
            'limit' => 500,
            'offset' => 0,
            'upcoming_only' => false,
        )
    );

    $by_date = array();

    foreach ($occurrences as $occurrence) {
        $date = (string) $occurrence->occurrence_date;

        if (!isset($by_date[ $date ])) {
            $by_date[ $date ] = array();
        }

        $by_date[ $date ][] = $occurrence;
    }

    maca_njuvs_info_hub_enqueue_assets();

    $month_ts = strtotime($range_start . ' 12:00:00');
    $month_label = $month_ts !== false ? wp_date('F Y', $month_ts) : $month;
    $prev_month = wp_date('Y-m', strtotime($range_start . ' -1 day'));
    $next_month = wp_date('Y-m', strtotime($range_end . ' +1 day'));

    $weekday_labels = array_values(maca_njuvs_weekday_short_labels_ordered());

    $first_weekday = (int) wp_date('w', strtotime($range_start . ' 12:00:00'));
    $leading = $monday_first ? (($first_weekday + 6) % 7) : $first_weekday;
    $days_in_month = (int) wp_date('t', strtotime($range_start . ' 12:00:00'));
    $today = wp_date('Y-m-d');

    ob_start();
    ?>
    <div class="maca-info-hub maca-info-events-calendar">
        <?php if (!$preview) : ?>
            <nav class="maca-info-calendar-nav" aria-label="<?php esc_attr_e('Calendar navigation', 'maca-njuvs'); ?>">
                <a class="maca-info-calendar-prev" href="<?php echo esc_url(maca_njuvs_info_hub_calendar_month_url($prev_month)); ?>">&larr; <?php esc_html_e('Previous', 'maca-njuvs'); ?></a>
                <strong class="maca-info-calendar-title"><?php echo esc_html($month_label); ?></strong>
                <a class="maca-info-calendar-next" href="<?php echo esc_url(maca_njuvs_info_hub_calendar_month_url($next_month)); ?>"><?php esc_html_e('Next', 'maca-njuvs'); ?> &rarr;</a>
            </nav>
        <?php else : ?>
            <p class="maca-info-calendar-title"><strong><?php echo esc_html($month_label); ?></strong></p>
        <?php endif; ?>

        <table class="maca-info-calendar-grid">
            <thead>
                <tr>
                    <?php foreach ($weekday_labels as $label) : ?>
                        <th scope="col"><?php echo esc_html($label); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $day = 1;
                $cell = 0;
                $total_cells = (int) (ceil(($leading + $days_in_month) / 7) * 7);

                for ($cell = 0; $cell < $total_cells; ++$cell) {
                    if ($cell % 7 === 0) {
                        if ($cell > 0) {
                            echo '</tr>';
                        }
                        echo '<tr>';
                    }

                    if ($cell < $leading || $day > $days_in_month) {
                        echo '<td class="maca-info-cal-day maca-info-cal-day--empty" aria-hidden="true"></td>';
                        continue;
                    }

                    $date = sprintf('%s-%02d', $month, $day);
                    $classes = array('maca-info-cal-day');

                    if ($date === $today) {
                        $classes[] = 'maca-info-cal-day--today';
                    }

                    if (!empty($by_date[ $date ])) {
                        $classes[] = 'maca-info-cal-day--has-events';
                    }

                    echo '<td class="' . esc_attr(implode(' ', $classes)) . '">';
                    echo '<span class="maca-info-cal-day-num">' . esc_html((string) $day) . '</span>';

                    if (!empty($by_date[ $date ])) {
                        echo '<ul class="maca-info-cal-events">';
                        foreach ($by_date[ $date ] as $occurrence) {
                            $title = maca_njuvs_info_hub_get_event_title($occurrence->event);
                            $time_short = maca_njuvs_info_hub_format_occurrence_time_short($occurrence);
                            $detail_anchor = maca_njuvs_info_hub_occurrence_detail_anchor_id($occurrence);
                            echo '<li>';
                            echo '<a class="maca-info-cal-event-link" href="#' . esc_attr($detail_anchor) . '">';
                            if ($time_short !== '') {
                                echo '<span class="maca-info-cal-event-time">' . esc_html($time_short) . '</span> ';
                            }
                            echo '<span class="maca-info-cal-event-title">' . esc_html($title) . '</span>';
                            echo '</a></li>';
                        }
                        echo '</ul>';
                    }

                    echo '</td>';
                    ++$day;
                }
                echo '</tr>';
                ?>
            </tbody>
        </table>

        <?php if (!empty($occurrences)) : ?>
            <section class="maca-info-event-details" aria-label="<?php esc_attr_e('Event details', 'maca-njuvs'); ?>">
                <p class="maca-info-event-details-hint"><?php esc_html_e('Click an event in the calendar to see the full description, price and booking.', 'maca-njuvs'); ?></p>
                <?php foreach ($occurrences as $occurrence) : ?>
                    <?php
                    maca_njuvs_info_hub_render_occurrence_detail(
                        $occurrence,
                        array(
                            'show_image' => $show_image,
                            'show_location' => $show_location,
                            'collapsible' => true,
                            'anchor_id' => maca_njuvs_info_hub_occurrence_detail_anchor_id($occurrence),
                        )
                    );
                    ?>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if ($show_subscribe) : ?>
            <?php echo wp_kses(maca_njuvs_render_info_calendar_subscribe(array('preview' => $preview, 'compact' => true)), maca_njuvs_info_hub_get_calendar_subscribe_allowed_html()); ?>
        <?php endif; ?>
    </div>
    <?php
    return (string) ob_get_clean();
}

add_action('init', 'maca_njuvs_info_hub_register_shortcodes');

/**
 * Whether HTML/content contains a news banner block or shortcode.
 *
 * @param string $content Post or template content.
 * @return bool
 */
function maca_njuvs_info_hub_content_has_banner_layout($content) {
    $content = (string) $content;

    if ($content === '') {
        return false;
    }

    if (strpos($content, '"layout":"banner"') !== false || strpos($content, '"layout": "banner"') !== false) {
        return true;
    }

    if (preg_match('/\[maca_njuvs_news[^\]]*layout=["\']banner["\']/', $content)) {
        return true;
    }

    if (!function_exists('parse_blocks')) {
        return false;
    }

    foreach (parse_blocks($content) as $block) {
        if (maca_njuvs_info_hub_block_tree_has_banner_layout($block)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array<string, mixed> $block Parsed block.
 * @return bool
 */
function maca_njuvs_info_hub_block_tree_has_banner_layout($block) {
    if (!is_array($block)) {
        return false;
    }

    if (($block['blockName'] ?? '') === 'maca-njuvs/maca-info-news') {
        $layout = isset($block['attrs']['layout']) ? sanitize_key((string) $block['attrs']['layout']) : 'list';

        return $layout === 'banner';
    }

    foreach ($block['innerBlocks'] ?? array() as $inner_block) {
        if (maca_njuvs_info_hub_block_tree_has_banner_layout($inner_block)) {
            return true;
        }
    }

    return false;
}

/**
 * Detect news banner blocks before body_class runs (FSE templates + post content).
 *
 * @return void
 */
function maca_njuvs_info_hub_maybe_flag_banner_from_content() {
    if (is_admin() || !maca_njuvs_info_hub_feature_available() || !maca_njuvs_enabled()) {
        return;
    }

    if (!maca_njuvs_info_hub_has_publishable_news()) {
        return;
    }

    $sources = array();

    if (is_singular()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post) {
            $sources[] = (string) $post->post_content;
        }
    }

    $front_page_id = (int) get_option('page_on_front');
    if ($front_page_id > 0) {
        $front_post = get_post($front_page_id);
        if ($front_post instanceof WP_Post) {
            $sources[] = (string) $front_post->post_content;
        }
    }

    if (function_exists('get_block_template')) {
        foreach (array('header', 'footer') as $part_slug) {
            $part = get_block_template(get_stylesheet() . '//' . $part_slug, 'wp_template_part');
            if ($part && !empty($part->content)) {
                $sources[] = (string) $part->content;
            }
        }
    }

    if (function_exists('wp_is_block_theme') && wp_is_block_theme() && function_exists('resolve_block_template')) {
        $template_types = array('front-page', 'home', 'index', 'page', 'single');
        foreach ($template_types as $type) {
            $template = resolve_block_template("{$type}", array(), '');
            if ($template && !empty($template->content)) {
                $sources[] = (string) $template->content;
            }
        }
    }

    foreach ($sources as $content) {
        if (maca_njuvs_info_hub_content_has_banner_layout($content)) {
            maca_njuvs_info_hub_flag_news_banner_page();
            break;
        }
    }
}

add_action('wp', 'maca_njuvs_info_hub_maybe_flag_banner_from_content', 20);
