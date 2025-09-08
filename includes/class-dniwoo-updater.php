<?php
/**
 * DNIWOO Updater Class
 * 
 * Handles automatic updates from GitHub
 * 
 * @package DNIWOO
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * DNIWOO Updater class
 */
class DNIWOO_Updater {

    /**
     * Update checker instance
     * 
     * @var object|null
     */
    private $update_checker = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize updater
     */
    private function init() {
        // Only load update checker in admin
        if (!is_admin()) {
            return;
        }

        add_action('init', array($this, 'setup_update_checker'));
        add_filter('puc_request_info_query_args-dniwoo', array($this, 'add_license_key'));
    }

    /**
     * Setup update checker
     */
    public function setup_update_checker() {
        // Auto-updater disabled for compatibility
        // Manual updates via WordPress plugin repository
        return;
        
        /*
        // Load the library
        $puc_path = DNIWOO_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
        
        if (!file_exists($puc_path)) {
            return;
        }

        require_once $puc_path;

        // Check if class exists (to prevent conflicts)
        if (!class_exists('Puc_v4p11_Factory')) {
            return;
        }

        try {
            $this->update_checker = Puc_v4p11_Factory::buildUpdateChecker(
                'https://github.com/replantadev/dniwoo/',
                DNIWOO_PLUGIN_FILE,
                'dniwoo'
            );

            // Optional: Set the branch that contains the stable release
            $this->update_checker->setBranch('main');

            // Optional: Enable release assets for downloads
            $this->update_checker->getVcsApi()->enableReleaseAssets();

            // Add update filters
            $this->add_update_filters();
        */
    }

    /**
     * Add update filters
     */
    private function add_update_filters() {
        if (!$this->update_checker) {
            return;
        }

        // Filter plugin metadata
        add_filter('puc_request_info_result-dniwoo', array($this, 'filter_plugin_info'), 10, 2);
        
        // Add custom update messages
        add_action('in_plugin_update_message-' . DNIWOO_PLUGIN_BASENAME, array($this, 'update_message'), 10, 2);
    }

    /**
     * Add license key to update requests
     * 
     * @param array $query_args
     * @return array
     */
    public function add_license_key($query_args) {
        $license_key = get_option('dniwoo_license_key', '');
        
        if (!empty($license_key)) {
            $query_args['license_key'] = $license_key;
        }
        
        return $query_args;
    }

    /**
     * Filter plugin information
     * 
     * @param object $plugin_info
     * @param array $result
     * @return object
     */
    public function filter_plugin_info($plugin_info, $result) {
        // Add additional plugin information
        if (isset($result['body'])) {
            $body = json_decode($result['body'], true);
            
            if (isset($body['name'])) {
                $plugin_info->name = $body['name'];
            }
            
            if (isset($body['description'])) {
                $plugin_info->short_description = $body['description'];
            }
        }
        
        return $plugin_info;
    }

    /**
     * Display custom update message
     * 
     * @param array $plugin_data
     * @param object $response
     */
    public function update_message($plugin_data, $response) {
        if (empty($response->upgrade_notice)) {
            return;
        }

        echo '<div class="update-message">';
        echo wp_kses_post($response->upgrade_notice);
        echo '</div>';
    }

    /**
     * Check for updates manually
     * 
     * @return bool|WP_Error
     */
    public function check_for_updates() {
        if (!$this->update_checker) {
            return new WP_Error('no_checker', __('Update checker not initialized', 'dniwoo'));
        }

        try {
            $update = $this->update_checker->checkForUpdates();
            return $update !== null;
        } catch (Exception $e) {
            return new WP_Error('check_failed', $e->getMessage());
        }
    }

    /**
     * Get update information
     * 
     * @return array|false
     */
    public function get_update_info() {
        if (!$this->update_checker) {
            return false;
        }

        $update = $this->update_checker->getUpdate();
        
        if (!$update) {
            return false;
        }

        return array(
            'version' => $update->version,
            'details_url' => $update->details_url,
            'download_url' => $update->download_url,
            'upgrade_notice' => isset($update->upgrade_notice) ? $update->upgrade_notice : '',
            'tested' => isset($update->tested) ? $update->tested : '',
            'requires_php' => isset($update->requires_php) ? $update->requires_php : '',
        );
    }

    /**
     * Force update check
     */
    public function force_check() {
        if ($this->update_checker) {
            $this->update_checker->checkForUpdates();
        }
    }

    /**
     * Get current version
     * 
     * @return string
     */
    public function get_current_version() {
        return DNIWOO_VERSION;
    }

    /**
     * Get remote version
     * 
     * @return string|false
     */
    public function get_remote_version() {
        $info = $this->get_update_info();
        return $info ? $info['version'] : false;
    }

    /**
     * Check if update is available
     * 
     * @return bool
     */
    public function is_update_available() {
        $remote_version = $this->get_remote_version();
        
        if (!$remote_version) {
            return false;
        }
        
        return version_compare($this->get_current_version(), $remote_version, '<');
    }

    /**
     * Get changelog URL
     * 
     * @return string
     */
    public function get_changelog_url() {
        return 'https://github.com/replantadev/dniwoo/blob/main/CHANGELOG.md';
    }

    /**
     * Get support URL
     * 
     * @return string
     */
    public function get_support_url() {
        return 'https://github.com/replantadev/dniwoo/issues';
    }
}
