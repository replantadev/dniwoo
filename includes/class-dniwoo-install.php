<?php
/**
 * DNIWOO Installation
 *
 * @package DNIWOO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DNIWOO_Install class.
 */
class DNIWOO_Install {

    /**
     * Install DNIWOO.
     *
     * @since 1.0.0
     */
    public static function install() {
        if (!is_blog_installed()) {
            return;
        }

        // Check if we are not already running this routine.
        if ('yes' === get_transient('dniwoo_installing')) {
            return;
        }

        // If we made it till here nothing is running yet, lets set the transient now.
        set_transient('dniwoo_installing', 'yes', MINUTE_IN_SECONDS * 10);

        self::create_options();
        self::create_database_tables();
        self::update_version();

        delete_transient('dniwoo_installing');

        do_action('dniwoo_installed');
    }

    /**
     * Create default options.
     *
     * @since 1.0.0
     */
    private static function create_options() {
        $default_options = array(
            'dniwoo_version' => DNIWOO_VERSION,
            'dniwoo_enabled' => 'yes',
            'dniwoo_required' => 'yes',
            'dniwoo_position' => 'after_phone',
            'dniwoo_validation_mode' => 'real_time',
            'dniwoo_supported_countries' => array('ES', 'PT'),
        );

        foreach ($default_options as $option => $value) {
            add_option($option, $value);
        }
    }

    /**
     * Create database tables if needed.
     *
     * @since 1.0.0
     */
    private static function create_database_tables() {
        // Currently no custom tables needed
        // This method is prepared for future extensions
    }

    /**
     * Update DNIWOO version to current.
     *
     * @since 1.0.0
     */
    private static function update_version() {
        update_option('dniwoo_version', DNIWOO_VERSION);
    }

    /**
     * Get list of DB update callbacks.
     *
     * @return array
     * @since 1.0.0
     */
    public static function get_db_update_callbacks() {
        return array(
            '1.0.0' => array(
                'dniwoo_update_100_create_options',
            ),
        );
    }
}
