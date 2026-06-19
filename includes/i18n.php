<?php
/**
 * Bilingual content helpers for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return string
 */
function maca_menulist_get_site_locale() {
    if (defined('WPLANG')) {
        $constant_locale = WPLANG;

        if (is_string($constant_locale) && $constant_locale !== '') {
            return $constant_locale;
        }
    }

    $locale = get_option('WPLANG', '');

    if (!is_string($locale)) {
        $locale = '';
    }

    $locale = trim($locale);

    if ($locale === '' || $locale === 'en' || $locale === 'en_US') {
        return 'en_US';
    }

    return $locale;
}

/**
 * @return bool
 */
function maca_menulist_site_locale_is_english() {
    $locale = strtolower(str_replace('-', '_', maca_menulist_get_site_locale()));

    if (in_array($locale, array('en', 'en_us', 'en_gb', 'en_au', 'en_ca', 'en_nz', 'en_za'), true)) {
        return true;
    }

    return strpos($locale, 'en_') === 0;
}

/**
 * @return string
 */
function maca_menulist_get_local_language_code() {
    $locale = strtolower(maca_menulist_get_site_locale());
    $code = sanitize_key(substr($locale, 0, 2));

    return $code !== '' ? $code : 'en';
}

/**
 * Supported guide document language codes.
 *
 * @return string[]
 */
function maca_menulist_get_guide_language_codes() {
    return array('sv', 'en', 'de', 'es', 'fr', 'da', 'no', 'fi');
}

/**
 * Resolve the current admin locale to a guide language code.
 *
 * @return string
 */
function maca_menulist_get_admin_guide_language_code() {
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    $locale = strtolower(str_replace('-', '_', (string) $locale));
    $code = sanitize_key(substr($locale, 0, 2));

    if (in_array($code, array('nb', 'nn'), true)) {
        $code = 'no';
    }

    if (in_array($code, maca_menulist_get_guide_language_codes(), true)) {
        return $code;
    }

    return 'en';
}

/**
 * Path to a localized markdown document in docs/.
 *
 * @param string $basename File basename without language suffix (e.g. INFO-HUB-GUIDE).
 * @param string $fallback Fallback language code.
 * @return string
 */
function maca_menulist_get_localized_doc_file($basename, $fallback = 'en') {
    $basename = preg_replace('/[^A-Z0-9_-]/i', '', (string) $basename);
    $lang = maca_menulist_get_admin_guide_language_code();
    $file = MACA_MENULIST_PLUGIN_DIR . 'docs/' . $basename . '-' . $lang . '.md';

    if (!file_exists($file)) {
        $fallback = in_array($fallback, maca_menulist_get_guide_language_codes(), true) ? $fallback : 'en';
        $file = MACA_MENULIST_PLUGIN_DIR . 'docs/' . $basename . '-' . $fallback . '.md';
    }

    return $file;
}

/**
 * @return array<string, string>
 */
function maca_menulist_get_local_language_options() {
    return array(
        'sv' => __('Swedish', 'maca-njuvs'),
        'no' => __('Norwegian', 'maca-njuvs'),
        'nb' => __('Norwegian', 'maca-njuvs'),
        'nn' => __('Norwegian', 'maca-njuvs'),
        'da' => __('Danish', 'maca-njuvs'),
        'fi' => __('Finnish', 'maca-njuvs'),
        'de' => __('German', 'maca-njuvs'),
        'fr' => __('French', 'maca-njuvs'),
        'es' => __('Spanish', 'maca-njuvs'),
        'it' => __('Italian', 'maca-njuvs'),
        'nl' => __('Dutch', 'maca-njuvs'),
        'pl' => __('Polish', 'maca-njuvs'),
        'pt' => __('Portuguese', 'maca-njuvs'),
        'cs' => __('Czech', 'maca-njuvs'),
        'en' => __('English', 'maca-njuvs'),
    );
}

/**
 * @return string
 */
function maca_menulist_get_local_language_label() {
    $locale = maca_menulist_get_site_locale();

    if (function_exists('locale_get_display_name')) {
        $display = locale_get_display_name($locale, $locale);

        if (is_string($display) && $display !== '') {
            return $display;
        }
    }

    $options = maca_menulist_get_local_language_options();
    $code = maca_menulist_get_local_language_code();

    if (isset($options[ $code ])) {
        return $options[ $code ];
    }

    return __('Local language', 'maca-njuvs');
}

/**
 * @return string
 */
function maca_menulist_get_local_language_name() {
    return maca_menulist_get_local_language_label();
}

/**
 * @return bool
 */
function maca_menulist_bilingual_menus_available() {
    return false;
}

/**
 * @return bool
 */
function maca_menulist_dual_language_menu_enabled() {
    return false;
}

/**
 * @return bool
 */
function maca_menulist_should_use_local_content() {
    if (maca_menulist_site_locale_is_english()) {
        return true;
    }

    $local_code = maca_menulist_get_local_language_code();
    $visitor = function_exists('determine_locale') ? determine_locale() : get_locale();

    return strpos(strtolower((string) $visitor), $local_code) === 0;
}

/**
 * @param string $local   Local-language value.
 * @param string $english English value.
 * @return string
 */
function maca_menulist_get_bilingual_field($local, $english) {
    $local = trim((string) $local);
    $english = trim((string) $english);

    if (maca_menulist_should_use_local_content()) {
        return $local !== '' ? $local : $english;
    }

    return $english !== '' ? $english : $local;
}

/**
 * @param object|null $object    Database row object.
 * @param string      $local_key Local field property name.
 * @param string      $en_key    English field property name.
 * @return string
 */
function maca_menulist_get_object_bilingual_field($object, $local_key, $en_key) {
    if (!is_object($object)) {
        return '';
    }

    $local = isset($object->{$local_key}) ? (string) $object->{$local_key} : '';
    $english = isset($object->{$en_key}) ? (string) $object->{$en_key} : '';

    return maca_menulist_get_bilingual_field($local, $english);
}

/**
 * @return bool
 */
function maca_menulist_admin_show_local_content_fields() {
    return !maca_menulist_site_locale_is_english();
}

/**
 * @param string $field_label Base field name, already translated.
 * @return string
 */
function maca_menulist_admin_primary_field_label($field_label) {
    if (maca_menulist_admin_show_local_content_fields()) {
        return maca_menulist_bilingual_field_label($field_label, false);
    }

    return $field_label;
}

/**
 * @param string $field_label Base field name, already translated.
 * @param bool   $english     True for English field, false for local.
 * @return string
 */
function maca_menulist_bilingual_field_label($field_label, $english = false) {
    if ($english) {
        /* translators: %s: field label */
        return sprintf(__('%s (English)', 'maca-njuvs'), $field_label);
    }

    return sprintf(
        /* translators: 1: field label, 2: local language name */
        __('%1$s (%2$s)', 'maca-njuvs'),
        $field_label,
        maca_menulist_get_local_language_name()
    );
}

/**
 * @param string $local   Local-language value.
 * @param string $english English value.
 * @return string
 */
function maca_menulist_get_admin_bilingual_label($local, $english) {
    $local = trim((string) $local);
    $english = trim((string) $english);

    if ($local !== '' && $english !== '' && $local !== $english) {
        return $local . ' / ' . $english;
    }

    if ($local !== '') {
        return $local;
    }

    return $english;
}

/**
 * @param string $local   Local-language value.
 * @param string $english English value.
 * @return string
 */
function maca_menulist_get_admin_content_label($local, $english) {
    if (maca_menulist_admin_show_local_content_fields()) {
        return maca_menulist_get_admin_bilingual_label($local, $english);
    }

    $local = trim((string) $local);

    if ($local !== '') {
        return $local;
    }

    return trim((string) $english);
}

/**
 * @param string $local   Local-language value.
 * @param string $english English value.
 * @return bool
 */
function maca_menulist_admin_content_field_missing($local, $english) {
    if (maca_menulist_admin_show_local_content_fields()) {
        return trim((string) $local) === '' && trim((string) $english) === '';
    }

    return trim((string) $local) === '';
}
