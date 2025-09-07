<?php
/**
 * Plugin Name: DNIWOO - WooCommerce DNI/NIF Field
 * Plugin URI: https://github.com/replantadev/dniwoo
 * Description: Professional DNI/NIE/CIF (Spain) and NIF/NIPC (Portugal) validation field for WooCommerce checkout with real-time validation and auto-update system.
 * Version: 1.0.0
 * Author: Replanta
 * Author URI: https://replanta.net
 * Text Domain: dniwoo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Network: false
 * 
 * @package DNIWOO
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DNIWOO_VERSION', '1.0.0');
define('DNIWOO_PLUGIN_FILE', __FILE__);
define('DNIWOO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DNIWOO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DNIWOO_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DNIWOO_TEXT_DOMAIN', 'dniwoo');

/**
 * Main DNIWOO class
 *
 * @class DNIWOO
 * @since 1.0.0
 */
final class DNIWOO {

    /**
     * Plugin instance.
     *
     * @var DNIWOO
     * @since 1.0.0
     */
    private static $instance = null;

    /**
     * Plugin version.
     *
     * @var string
     */
    public $version = DNIWOO_VERSION;

    /**
     * Get DNIWOO instance.
     *
     * @return DNIWOO
     * @since 1.0.0
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * DNIWOO constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Hook into actions and filters.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'), 0);
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        
        // Plugin activation/deactivation hooks
        register_activation_hook(DNIWOO_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(DNIWOO_PLUGIN_FILE, array($this, 'deactivate'));
    }

    /**
     * Include required core files.
     *
     * @since 1.0.0
     */
    public function includes() {
        /**
         * Core classes.
         */
        include_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-install.php';
        include_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-checkout.php';
        include_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-validation.php';
        include_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-admin.php';
        include_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-assets.php';
        
        // Auto-updater
        if (!class_exists('Puc_v4_Factory')) {
            include_once DNIWOO_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
        }
        include_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-updater.php';
    }

    /**
     * Init DNIWOO when WordPress initializes.
     *
     * @since 1.0.0
     */
    public function init() {
        // Before init action.
        do_action('dniwoo_before_init');

        // Set up localization.
        $this->load_plugin_textdomain();

        // Initialize classes
        $this->checkout = new DNIWOO_Checkout();
        $this->validation = new DNIWOO_Validation();
        $this->admin = new DNIWOO_Admin();
        $this->assets = new DNIWOO_Assets();
        $this->updater = new DNIWOO_Updater();

        // Init action.
        do_action('dniwoo_init');
    }

    /**
     * Check if WooCommerce is active and compatible.
     *
     * @since 1.0.0
     */
    public function plugins_loaded() {
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        if (!$this->is_woocommerce_compatible()) {
            add_action('admin_notices', array($this, 'woocommerce_incompatible_notice'));
            return;
        }
    }

    /**
     * Check if WooCommerce is active.
     *
     * @return bool
     * @since 1.0.0
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Check if WooCommerce version is compatible.
     *
     * @return bool
     * @since 1.0.0
     */
    private function is_woocommerce_compatible() {
        if (!defined('WC_VERSION')) {
            return false;
        }
        return version_compare(WC_VERSION, '5.0', '>=');
    }

    /**
     * WooCommerce missing notice.
     *
     * @since 1.0.0
     */
    public function woocommerce_missing_notice() {
        $message = sprintf(
            /* translators: %s: WooCommerce link */
            __('DNIWOO requires WooCommerce to be installed and active. You can download %s here.', 'dniwoo'),
            '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
        );
        printf('<div class="error"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * WooCommerce incompatible notice.
     *
     * @since 1.0.0
     */
    public function woocommerce_incompatible_notice() {
        $message = sprintf(
            /* translators: %s: WooCommerce version */
            __('DNIWOO requires WooCommerce version 5.0 or higher. You are running version %s.', 'dniwoo'),
            WC_VERSION
        );
        printf('<div class="error"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Load localization files.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'dniwoo',
            false,
            dirname(DNIWOO_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Plugin activation callback.
     *
     * @since 1.0.0
     */
    public function activate() {
        if (!$this->is_woocommerce_active()) {
            wp_die(
                esc_html__('DNIWOO requires WooCommerce to be installed and active.', 'dniwoo'),
                esc_html__('Plugin Activation Error', 'dniwoo'),
                array('back_link' => true)
            );
        }

        DNIWOO_Install::install();
        
        // Trigger action
        do_action('dniwoo_activated');
    }

    /**
     * Plugin deactivation callback.
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Trigger action
        do_action('dniwoo_deactivated');
    }

    /**
     * Get the plugin path.
     *
     * @return string
     * @since 1.0.0
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Get the plugin URL.
     *
     * @return string
     * @since 1.0.0
     */
    public function plugin_url() {
        return untrailingslashit(plugin_dir_url(__FILE__));
    }

    /**
     * Get plugin version.
     *
     * @return string
     * @since 1.0.0
     */
    public function get_version() {
        return $this->version;
    }
}

/**
 * Main instance of DNIWOO.
 *
 * Returns the main instance of DNIWOO to prevent the need to use globals.
 *
 * @since 1.0.0
 * @return DNIWOO
 */
function DNIWOO() {
    return DNIWOO::instance();
}

// Global for backwards compatibility.
$GLOBALS['dniwoo'] = DNIWOO();
