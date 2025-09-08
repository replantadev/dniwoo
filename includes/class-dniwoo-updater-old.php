<?php
/**
 * DNIWOO Updater
 *
 * @package DNIWOO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * DNIWOO_Updater class.
 */
class DNIWOO_Updater {

    /**
     * Update checker instance.
     *
     * @var object
     */
    private $update_checker;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_updater();
    }

    /**
     * Initialize the update checker.
     *
     * @since 1.0.0
     */
    private function init_updater() {
        if (!class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
            return;
        }

        $this->update_checker = PucFactory::buildUpdateChecker(
            'https://github.com/replantadev/dniwoo',
            DNIWOO_PLUGIN_FILE,
            'dniwoo'
        );

        // Enable release assets if needed
        $this->update_checker->getVcsApi()->enableReleaseAssets();

        // Add settings page link
        add_filter('puc_manual_check_link-dniwoo', array($this, 'add_manual_check_link'), 10, 1);
    }

    /**
     * Add manual check link.
     *
     * @param string $link Existing link.
     * @return string Modified link.
     * @since 1.0.0
     */
    public function add_manual_check_link($link) {
        $settings_url = admin_url('admin.php?page=dniwoo-settings');
        return sprintf(
            '%s | <a href="%s">%s</a>',
            $link,
            $settings_url,
            __('Settings', 'dniwoo')
        );
    }

    /**
     * Get update checker instance.
     *
     * @return object|null Update checker instance.
     * @since 1.0.0
     */
    public function get_update_checker() {
        return $this->update_checker;
    }
}
