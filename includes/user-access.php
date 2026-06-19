<?php
/**
 * maca Njuvs admin capabilities.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('MACA_NJUVS_CAP_MANAGE')) {
    define('MACA_NJUVS_CAP_MANAGE', 'maca_manage_njuvs');
}

if (!defined('MACA_NJUVS_CAP_MANAGE_SECRETS')) {
    define('MACA_NJUVS_CAP_MANAGE_SECRETS', 'maca_manage_njuvs_secrets');
}

// Internal capability aliases used by module code.
if (!defined('MACA_MENULIST_CAP_MANAGE')) {
    define('MACA_MENULIST_CAP_MANAGE', MACA_NJUVS_CAP_MANAGE);
}

if (!defined('MACA_MENULIST_CAP_MANAGE_SECRETS')) {
    define('MACA_MENULIST_CAP_MANAGE_SECRETS', MACA_NJUVS_CAP_MANAGE_SECRETS);
}

/**
 * @return string
 */
function maca_menulist_admin_required_cap() {
    return MACA_NJUVS_CAP_MANAGE;
}

/**
 * @return void
 */
function maca_menulist_register_menulist_user_access() {
    $admin = get_role('administrator');

    if ($admin) {
        $admin->add_cap(MACA_NJUVS_CAP_MANAGE, true);
        $admin->add_cap(MACA_NJUVS_CAP_MANAGE_SECRETS, true);
    }
}

add_action('init', 'maca_menulist_register_menulist_user_access', 11);

/**
 * @return bool
 */
function maca_menulist_user_can_manage_plugin() {
    return current_user_can('manage_options') || current_user_can(MACA_NJUVS_CAP_MANAGE);
}

/**
 * @return bool
 */
function maca_menulist_user_can_manage_secrets() {
    return current_user_can('manage_options') || current_user_can(MACA_NJUVS_CAP_MANAGE_SECRETS);
}

/**
 * @return bool
 */
function maca_menulist_user_can_manage_info_hub_social() {
    return maca_menulist_user_can_manage_secrets();
}

/**
 * @return bool
 */
function maca_menulist_user_is_demo_manager() {
    return false;
}

/**
 * @return string
 */
function maca_menulist_admin_demo_readonly_wrap_class() {
    return '';
}

/**
 * @return void
 */
function maca_menulist_render_admin_demo_readonly_notice() {
    // No demo mode in maca Njuvs.
}
