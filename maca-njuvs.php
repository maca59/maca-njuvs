<?php
/**
 * Plugin Name: maca Njuvs
 * Plugin URI: https://github.com/maca59/maca-njuvs
 * Description: Publicera nyheter och evenemang på webbplatsen med Gutenberg-block, iCal-kalender och valfri delning till Facebook och Instagram.
 * Version: 1.0.19
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Tested up to: 7.0
 * Author: Maca Development
 * Author URI: https://maca.se
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: maca-njuvs
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MACA_NJUVS_VERSION', '1.0.19');
define('MACA_NJUVS_PLUGIN_FILE', __FILE__);
define('MACA_NJUVS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MACA_NJUVS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MACA_NJUVS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Internal compatibility constants used by shared module code.
define('MACA_MENULIST_VERSION', MACA_NJUVS_VERSION);
define('MACA_MENULIST_PLUGIN_FILE', MACA_NJUVS_PLUGIN_FILE);
define('MACA_MENULIST_PLUGIN_DIR', MACA_NJUVS_PLUGIN_DIR);
define('MACA_MENULIST_PLUGIN_URL', MACA_NJUVS_PLUGIN_URL);

require_once MACA_NJUVS_PLUGIN_DIR . 'includes/bootstrap.php';

/**
 * Load plugin translations.
 */
function maca_njuvs_load_textdomain() {
    static $loaded = false;

    if ($loaded) {
        return;
    }

    // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Required for self-hosted installs outside WordPress.org.
    load_plugin_textdomain(
        'maca-njuvs',
        false,
        dirname(MACA_NJUVS_PLUGIN_BASENAME) . '/languages'
    );

    $loaded = true;
}

/**
 * Main plugin bootstrap.
 */
class Maca_Njuvs {

    /** @var bool */
    private static $initialized = false;

    /** @var self|null */
    private static $instance = null;

    /**
     * @return self
     */
    public static function boot() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'), 0);
        add_action('init', array($this, 'load_textdomain'), 0);
        add_action('init', array($this, 'init'), 5);
        add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
    }

    public function load_textdomain() {
        maca_njuvs_load_textdomain();
    }

    public function init() {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/compat.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/user-access.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/i18n.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/datetime.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/db-info-hub.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/info-hub-events.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/info-hub.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/rewrites.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/info-hub-ical.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/info-hub-social-crypto.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/db-info-hub-social.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/info-hub-social.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/info-hub-social-oauth.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/import-wp-posts.php';
        require_once MACA_NJUVS_PLUGIN_DIR . 'includes/blocks.php';

        if (is_admin()) {
            require_once MACA_NJUVS_PLUGIN_DIR . 'includes/admin-secrets.php';
            require_once MACA_NJUVS_PLUGIN_DIR . 'includes/admin-info-hub.php';

            new Maca_Njuvs_Admin_Info_Hub();

            add_action('enqueue_block_editor_assets', 'maca_njuvs_enqueue_block_editor_assets');
        }
    }

    public function admin_styles() {
        if (!is_admin()) {
            return;
        }

        wp_enqueue_style(
            'maca-njuvs-admin',
            MACA_NJUVS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MACA_NJUVS_VERSION
        );
    }
}

/**
 * Plugin activation.
 */
function maca_njuvs_on_activate() {
    require_once MACA_NJUVS_PLUGIN_DIR . 'includes/user-access.php';
    maca_menulist_register_menulist_user_access();

    require_once MACA_NJUVS_PLUGIN_DIR . 'includes/db-info-hub.php';
    maca_menulist_db_ensure_info_hub_tables();

    require_once MACA_NJUVS_PLUGIN_DIR . 'includes/db-info-hub-social.php';
    maca_menulist_db_ensure_info_social_log_table();

    update_option('maca_njuvs_enabled', '1', false);

    flush_rewrite_rules();
}

/**
 * Plugin deactivation.
 */
function maca_njuvs_on_deactivate() {
    wp_clear_scheduled_hook('maca_njuvs_social_cron');
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'maca_njuvs_on_activate');
register_deactivation_hook(__FILE__, 'maca_njuvs_on_deactivate');

add_action('plugins_loaded', array('Maca_Njuvs', 'boot'), 0);
