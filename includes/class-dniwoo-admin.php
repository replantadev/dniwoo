<?php
/**
 * DNIWOO Admin
 *
 * @package DNIWOO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DNIWOO_Admin class.
 */
class DNIWOO_Admin {

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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_filter('plugin_action_links_' . DNIWOO_PLUGIN_BASENAME, array($this, 'add_settings_link'));
    }

    /**
     * Add admin menu.
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('DNIWOO Settings', 'dniwoo'),
            __('DNI/NIF Field', 'dniwoo'),
            'manage_woocommerce',
            'dniwoo-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Initialize settings.
     *
     * @since 1.0.0
     */
    public function settings_init() {
        register_setting('dniwoo_settings', 'dniwoo_enabled');
        register_setting('dniwoo_settings', 'dniwoo_required');
        register_setting('dniwoo_settings', 'dniwoo_position');
        register_setting('dniwoo_settings', 'dniwoo_validation_mode');
        register_setting('dniwoo_settings', 'dniwoo_supported_countries');

        add_settings_section(
            'dniwoo_settings_section',
            __('DNI/NIF Field Configuration', 'dniwoo'),
            array($this, 'settings_section_callback'),
            'dniwoo_settings'
        );

        add_settings_field(
            'dniwoo_enabled',
            __('Enable DNI/NIF Field', 'dniwoo'),
            array($this, 'enabled_field_callback'),
            'dniwoo_settings',
            'dniwoo_settings_section'
        );

        add_settings_field(
            'dniwoo_required',
            __('Required Field', 'dniwoo'),
            array($this, 'required_field_callback'),
            'dniwoo_settings',
            'dniwoo_settings_section'
        );

        add_settings_field(
            'dniwoo_position',
            __('Field Position', 'dniwoo'),
            array($this, 'position_field_callback'),
            'dniwoo_settings',
            'dniwoo_settings_section'
        );

        add_settings_field(
            'dniwoo_validation_mode',
            __('Validation Mode', 'dniwoo'),
            array($this, 'validation_mode_callback'),
            'dniwoo_settings',
            'dniwoo_settings_section'
        );
    }

    /**
     * Settings section callback.
     *
     * @since 1.0.0
     */
    public function settings_section_callback() {
        echo '<p>' . esc_html__('Configure the DNI/NIF field settings for your WooCommerce checkout.', 'dniwoo') . '</p>';
    }

    /**
     * Enabled field callback.
     *
     * @since 1.0.0
     */
    public function enabled_field_callback() {
        $value = get_option('dniwoo_enabled', 'yes');
        echo '<input type="checkbox" name="dniwoo_enabled" value="yes" ' . checked($value, 'yes', false) . ' />';
        echo '<p class="description">' . esc_html__('Enable or disable the DNI/NIF field in checkout.', 'dniwoo') . '</p>';
    }

    /**
     * Required field callback.
     *
     * @since 1.0.0
     */
    public function required_field_callback() {
        $value = get_option('dniwoo_required', 'yes');
        echo '<input type="checkbox" name="dniwoo_required" value="yes" ' . checked($value, 'yes', false) . ' />';
        echo '<p class="description">' . esc_html__('Make the DNI/NIF field required during checkout.', 'dniwoo') . '</p>';
    }

    /**
     * Position field callback.
     *
     * @since 1.0.0
     */
    public function position_field_callback() {
        $value = get_option('dniwoo_position', 'after_phone');
        $options = array(
            'after_email' => __('After Email', 'dniwoo'),
            'after_phone' => __('After Phone', 'dniwoo'),
            'before_company' => __('Before Company', 'dniwoo'),
            'after_company' => __('After Company', 'dniwoo'),
            'end' => __('End of Form', 'dniwoo'),
        );

        echo '<select name="dniwoo_position">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Choose where to display the DNI/NIF field in the checkout form.', 'dniwoo') . '</p>';
    }

    /**
     * Validation mode callback.
     *
     * @since 1.0.0
     */
    public function validation_mode_callback() {
        $value = get_option('dniwoo_validation_mode', 'real_time');
        $options = array(
            'real_time' => __('Real-time validation', 'dniwoo'),
            'on_submit' => __('Validate on form submit', 'dniwoo'),
        );

        echo '<select name="dniwoo_validation_mode">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Choose when to validate the DNI/NIF field.', 'dniwoo') . '</p>';
    }

    /**
     * Settings page.
     *
     * @since 1.0.0
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('DNIWOO Settings', 'dniwoo'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('dniwoo_settings');
                do_settings_sections('dniwoo_settings');
                submit_button();
                ?>
            </form>
            
            <div class="dniwoo-info-box" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-top: 20px;">
                <h3><?php echo esc_html__('Supported Document Types', 'dniwoo'); ?></h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4><?php echo esc_html__('Spain (ES)', 'dniwoo'); ?></h4>
                        <ul>
                            <li><strong>DNI:</strong> <?php echo esc_html__('8 digits + letter (12345678Z)', 'dniwoo'); ?></li>
                            <li><strong>NIE:</strong> <?php echo esc_html__('X/Y/Z + 7 digits + letter (X1234567L)', 'dniwoo'); ?></li>
                            <li><strong>CIF:</strong> <?php echo esc_html__('Letter + 7 digits + control (A1234567C)', 'dniwoo'); ?></li>
                        </ul>
                    </div>
                    <div>
                        <h4><?php echo esc_html__('Portugal (PT)', 'dniwoo'); ?></h4>
                        <ul>
                            <li><strong>NIF:</strong> <?php echo esc_html__('9 digits for individuals (123456789)', 'dniwoo'); ?></li>
                            <li><strong>NIPC:</strong> <?php echo esc_html__('9 digits for companies (123456789)', 'dniwoo'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add settings link to plugin actions.
     *
     * @param array $links Plugin action links.
     * @return array Modified links.
     * @since 1.0.0
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=dniwoo-settings') . '">' . __('Settings', 'dniwoo') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
