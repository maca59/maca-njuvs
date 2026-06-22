<?php
/**
 * Admin secret field helpers (masked display, unchanged detection on save).
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mask shown when a secret value is already stored.
 *
 * @return string
 */
function maca_njuvs_admin_secret_mask() {
    return '********';
}

/**
 * Input value for admin secret fields.
 *
 * @param bool $has_stored_value Whether a value exists in storage.
 * @return string
 */
function maca_njuvs_admin_secret_input_value($has_stored_value) {
    return $has_stored_value ? maca_njuvs_admin_secret_mask() : '';
}

/**
 * Whether a submitted secret should be treated as "keep existing".
 *
 * @param string $submitted Raw POST value.
 * @return bool
 */
function maca_njuvs_admin_secret_submission_is_unchanged($submitted) {
    $submitted = trim((string) $submitted);

    return $submitted === '' || $submitted === maca_njuvs_admin_secret_mask();
}

/**
 * Whether a submitted secret value should be written to storage.
 *
 * @param string $submitted Raw POST value.
 * @return bool
 */
function maca_njuvs_admin_secret_should_update($submitted) {
    if (!function_exists('maca_njuvs_user_can_manage_secrets') || !maca_njuvs_user_can_manage_secrets()) {
        return false;
    }

    return !maca_njuvs_admin_secret_submission_is_unchanged($submitted);
}

/**
 * Build HTML attributes for a masked admin secret input.
 *
 * @param bool                 $has_stored_value Whether a value exists in storage.
 * @param array<string, mixed> $extra            id, name, class, inputmode, etc.
 * @return array<string, mixed>
 */
function maca_njuvs_admin_secret_input_attrs($has_stored_value, $extra = array()) {
    $class = 'maca-admin-secret-field';

    if (isset($extra['class']) && is_string($extra['class']) && $extra['class'] !== '') {
        $class .= ' ' . $extra['class'];
        unset($extra['class']);
    } else {
        $class .= ' regular-text';
    }

    $attrs = array_merge(
        array(
            'type' => 'password',
            'class' => $class,
            'value' => maca_njuvs_admin_secret_input_value($has_stored_value),
            'autocomplete' => 'new-password',
        ),
        $extra
    );

    if ($has_stored_value) {
        $attrs['data-maca-secret-mask'] = '1';
    }

    if (function_exists('maca_njuvs_user_can_manage_secrets') && !maca_njuvs_user_can_manage_secrets()) {
        $attrs['readonly'] = true;
        $attrs['aria-disabled'] = 'true';
        $attrs['class'] .= ' maca-admin-secret-field--locked';
    }

    return $attrs;
}

/**
 * Render an attribute string for maca_njuvs_admin_secret_input_attrs().
 *
 * @param array<string, mixed> $attrs Attributes.
 * @return string
 */
function maca_njuvs_admin_secret_field_attr_string($attrs) {
    $parts = array();

    foreach ($attrs as $key => $value) {
        if ($value === null || $value === false) {
            continue;
        }

        if ($value === true) {
            $parts[] = esc_attr((string) $key);
            continue;
        }

        $parts[] = esc_attr((string) $key) . '="' . esc_attr((string) $value) . '"';
    }

    return implode(' ', $parts);
}

/**
 * Enqueue JS that clears the mask placeholder on focus.
 *
 * @param string $hook Admin page hook.
 * @return void
 */
function maca_njuvs_admin_enqueue_secret_fields_script($hook) {
    if (!is_string($hook) || $hook === '' || strpos($hook, 'maca-njuvs') === false) {
        return;
    }

    wp_enqueue_script(
        'maca-njuvs-admin-secret-fields',
        MACA_NJUVS_PLUGIN_URL . 'assets/js/admin-secret-fields.js',
        array(),
        MACA_NJUVS_VERSION,
        true
    );
}

add_action('admin_enqueue_scripts', 'maca_njuvs_admin_enqueue_secret_fields_script', 20);
