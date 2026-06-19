<?php
/**
 * Encrypt Meta credentials for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param string $plain Plain text.
 * @return string
 */
function maca_menulist_info_hub_encrypt_secret($plain) {
    $plain = (string) $plain;

    if ($plain === '') {
        return '';
    }

    if (!function_exists('openssl_encrypt')) {
        return base64_encode($plain);
    }

    $key = hash('sha256', wp_salt('maca_njuvs_meta'), true);
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        return '';
    }

    return base64_encode($iv . $encrypted);
}

/**
 * @param string $stored Stored value.
 * @return string
 */
function maca_menulist_info_hub_decrypt_secret($stored) {
    $stored = (string) $stored;

    if ($stored === '') {
        return '';
    }

    if (!function_exists('openssl_decrypt')) {
        $decoded = base64_decode($stored, true);

        return is_string($decoded) ? $decoded : '';
    }

    $raw = base64_decode($stored, true);

    if ($raw === false || strlen($raw) < 17) {
        return '';
    }

    $iv = substr($raw, 0, 16);
    $encrypted = substr($raw, 16);
    $key = hash('sha256', wp_salt('maca_njuvs_meta'), true);
    $plain = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return is_string($plain) ? $plain : '';
}
