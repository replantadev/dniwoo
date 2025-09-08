<?php
/**
 * DNIWOO - Professional DNI/NIF field for WooCommerce
 * 
 * @package DNIWOO
 * @author Replanta
 * @copyright 2024 Replanta
 * @license GPL-3.0-or-later
 * @version 1.0.1
 * 
 * @wordpress-plugin
 * Plugin Name: DNIWOO - Professional DNI/NIF for WooCommerce
 * Plugin URI: https://github.com/replantadev/dniwoo
 * Description: Professional DNI/NIF field for WooCommerce checkout with validation for Spain and Portugal.
 * Version: 1.0.1
 * Author: Replanta
 * Author URI: https://replanta.net
 * Text Domain: dniwoo-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.9
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Network: false
 * Update URI: https://github.com/replantadev/dniwoo
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
 * @since 1.0.0
 */
final class DNIWOO {

    /**
     * Plugin instance
     * 
     * @var DNIWOO|null
     */
    private static $instance = null;

    /**
     * Checkout handler
     * 
     * @var DNIWOO_Checkout|null
     */
    private $checkout = null;

    /**
     * Validation handler
     * 
     * @var DNIWOO_Validation|null
     */
    private $validation = null;

    /**
     * Admin handler
     * 
     * @var DNIWOO_Admin|null
     */
    private $admin = null;

    /**
     * Assets handler
     * 
     * @var DNIWOO_Assets|null
     */
    private $assets = null;

    /**
     * Updater handler
     * 
     * @var DNIWOO_Updater|null
     */
    private $updater = null;

    /**
     * Get singleton instance
     * 
     * @return DNIWOO
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'init'), 10);
        add_action('init', array($this, 'load_textdomain'));
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check dependencies
        if (!$this->check_dependencies()) {
            return;
        }

        // Load classes
        $this->load_classes();
        
        // Initialize components
        $this->init_components();
    }

    /**
     * Check plugin dependencies
     * 
     * @return bool
     */
    private function check_dependencies() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return false;
        }

        // Check WooCommerce version
        if (class_exists('WooCommerce')) {
            $wc_version = get_option('woocommerce_db_version', '0');
            if (version_compare($wc_version, '5.0', '<')) {
                add_action('admin_notices', array($this, 'woocommerce_version_notice'));
                return false;
            }
        }

        return true;
    }

    /**
     * Load plugin classes
     */
    private function load_classes() {
        require_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-install.php';
        require_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-checkout.php';
        require_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-validation.php';
        require_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-admin.php';
        require_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-assets.php';
        require_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-updater.php';
    }

    /**
     * Initialize components
     */
    private function init_components() {
        $this->checkout = new DNIWOO_Checkout();
        $this->validation = new DNIWOO_Validation();
        $this->admin = new DNIWOO_Admin();
        $this->assets = new DNIWOO_Assets();
        $this->updater = new DNIWOO_Updater();
    }

    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'dniwoo',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Check dependencies before activation
        if (!$this->check_dependencies()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('DNIWOO requires WooCommerce 5.0 or higher to be installed and active.', 'dniwoo'),
                esc_html__('Plugin Activation Error', 'dniwoo'),
                array('back_link' => true)
            );
        }

        // Load installer
        require_once DNIWOO_PLUGIN_DIR . 'includes/class-dniwoo-install.php';
        DNIWOO_Install::install();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
        do_action('dniwoo_deactivate');
    }

    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        $message = sprintf(
            /* translators: %s: WooCommerce plugin name */
            esc_html__('DNIWOO requires %s to be installed and active.', 'dniwoo'),
            '<strong>WooCommerce</strong>'
        );
        
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            wp_kses_post($message)
        );
    }

    /**
     * WooCommerce version notice
     */
    public function woocommerce_version_notice() {
        $message = sprintf(
            /* translators: %s: required WooCommerce version */
            esc_html__('DNIWOO requires WooCommerce version %s or higher.', 'dniwoo'),
            '<strong>5.0</strong>'
        );
        
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            wp_kses_post($message)
        );
    }

    /**
     * Get checkout instance
     * 
     * @return DNIWOO_Checkout|null
     */
    public function get_checkout() {
        return $this->checkout;
    }

    /**
     * Get validation instance
     * 
     * @return DNIWOO_Validation|null
     */
    public function get_validation() {
        return $this->validation;
    }

    /**
     * Get admin instance
     * 
     * @return DNIWOO_Admin|null
     */
    public function get_admin() {
        return $this->admin;
    }

    /**
     * Get assets instance
     * 
     * @return DNIWOO_Assets|null
     */
    public function get_assets() {
        return $this->assets;
    }

    /**
     * Get updater instance
     * 
     * @return DNIWOO_Updater|null
     */
    public function get_updater() {
        return $this->updater;
    }
}

/**
 * Initialize DNIWOO
 * 
 * @return DNIWOO
 */
function dniwoo() {
    return DNIWOO::instance();
}

// Start the plugin
dniwoo();
