<?php
/**
 * Lightweight bootstrap helpers for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin menu icon (WordPress megaphone dashicon).
 *
 * @return string Dashicon class for add_menu_page().
 */
function maca_njuvs_get_admin_menu_icon() {
    return 'dashicons-megaphone';
}
