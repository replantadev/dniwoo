<?php
/**
 * DNIWOO Assets
 *
 * @package DNIWOO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DNIWOO_Assets class.
 */
class DNIWOO_Assets {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Hook into actions and filters.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
    }

    /**
     * Enqueue frontend scripts and styles.
     *
     * @since 1.0.0
     */
    public function frontend_scripts() {
        if (!is_checkout()) {
            return;
        }

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            'dniwoo-checkout',
            DNIWOO_PLUGIN_URL . 'assets/js/checkout' . $suffix . '.js',
            array('jquery'),
            DNIWOO_VERSION,
            true
        );

        wp_enqueue_style(
            'dniwoo-checkout',
            DNIWOO_PLUGIN_URL . 'assets/css/checkout' . $suffix . '.css',
            array(),
            DNIWOO_VERSION
        );

        // Localize script
        wp_localize_script('dniwoo-checkout', 'dniwoo_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dniwoo_validate'),
            'validation_mode' => get_option('dniwoo_validation_mode', 'real_time'),
            'messages' => array(
                'es' => array(
                    'invalid' => __('❌ Invalid DNI/NIE/CIF', 'dniwoo'),
                    'valid' => __('✔️ Valid document', 'dniwoo'),
                    'label' => __('DNI/NIE/CIF', 'dniwoo'),
                    'placeholder' => '12345678X',
                ),
                'pt' => array(
                    'invalid' => __('❌ Invalid NIF/NIPC', 'dniwoo'),
                    'valid' => __('✔️ Valid document', 'dniwoo'),
                    'label' => __('NIF/NIPC', 'dniwoo'),
                    'placeholder' => '123456789',
                ),
            ),
        ));
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook_suffix Current admin page.
     * @since 1.0.0
     */
    public function admin_scripts($hook_suffix) {
        // Only load on our settings page
        if ('woocommerce_page_dniwoo-settings' !== $hook_suffix) {
            return;
        }

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style(
            'dniwoo-admin',
            DNIWOO_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css',
            array(),
            DNIWOO_VERSION
        );
    }
}
