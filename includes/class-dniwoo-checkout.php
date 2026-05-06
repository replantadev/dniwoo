<?php
/**
 * DNIWOO Checkout
 *
 * @package DNIWOO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DNIWOO_Checkout class.
 */
class DNIWOO_Checkout {

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
        // Classic (shortcode) checkout
        add_filter('woocommerce_checkout_fields', array($this, 'add_dni_field'));
        add_action('woocommerce_checkout_order_created', array($this, 'save_dni_field'));

        // Blocks checkout (WC 8.9+ Additional Checkout Fields API)
        add_action('woocommerce_init', array($this, 'register_blocks_field'));
        add_action('woocommerce_blocks_validate_location_contact_fields', array($this, 'validate_blocks_field'), 10, 3);
        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'save_dni_field_blocks'), 10, 2);

        // Address formatting
        add_filter('woocommerce_order_formatted_billing_address', array($this, 'add_dni_to_address'), 10, 2);
        add_filter('woocommerce_localisation_address_formats', array($this, 'modify_address_format'));
        add_filter('woocommerce_formatted_address_replacements', array($this, 'replace_dni_placeholder'), 10, 2);
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_dni_admin'), 10, 1);
        
        // HPOS compatible column hooks
        add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'add_dni_column'));
        add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'display_dni_column_hpos'), 10, 2);
        
        // Legacy hooks for backwards compatibility
        add_filter('manage_edit-shop_order_columns', array($this, 'add_dni_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_dni_column_legacy'), 10, 2);
        add_filter('manage_edit-shop_order_sortable_columns', array($this, 'make_dni_column_sortable'));
    }

    /**
     * Register DNI field for Blocks checkout (WC Additional Checkout Fields API, WC 8.9+).
     *
     * The field appears in the "Contact information" section so it renders once,
     * not duplicated in billing AND shipping address panels.
     *
     * @since 1.2.2
     */
    public function register_blocks_field() {
        if (!function_exists('woocommerce_register_additional_checkout_field')) {
            return;
        }
        if ('yes' !== get_option('dniwoo_enabled', 'yes')) {
            return;
        }
        woocommerce_register_additional_checkout_field(array(
            'id'         => 'dniwoo/billing_dni',
            'label'      => __('DNI/NIE/CIF/NIF/NIPC', 'dniwoo'),
            'location'   => 'contact',
            'required'   => get_option('dniwoo_required', 'yes') === 'yes',
            'attributes' => array(
                'autocomplete' => 'off',
                'maxlength'    => '20',
            ),
        ));
    }

    /**
     * Validate DNI field submitted via Blocks checkout.
     *
     * Runs on woocommerce_blocks_validate_location_contact_fields.
     *
     * @param \WP_Error $errors            Error object to add errors to.
     * @param array     $fields            Array of submitted field values keyed by field id.
     * @param string    $validationContext 'billing' or 'shipping'.
     * @since 1.2.2
     */
    public function validate_blocks_field($errors, $fields, $validationContext) {
        if (!isset($fields['dniwoo/billing_dni'])) {
            return;
        }
        if ('yes' !== get_option('dniwoo_enabled', 'yes')) {
            return;
        }
        $value = strtoupper(trim($fields['dniwoo/billing_dni']));
        if (empty($value)) {
            return; // WC handles the "required" check automatically.
        }
        // Accept document if valid for Spain OR Portugal.
        $validation = dniwoo()->get_validation();
        $es = $validation->validate_document($value, 'ES');
        $pt = $validation->validate_document($value, 'PT');
        if (!$es['valid'] && !$pt['valid']) {
            $errors->add(
                'invalid_billing_dni',
                __('The entered DNI/NIE/CIF/NIF/NIPC is not valid.', 'dniwoo')
            );
        }
    }

    /**
     * Save DNI submitted via Blocks checkout (Store API path).
     *
     * WooCommerce saves the raw value under meta key 'dniwoo/billing_dni';
     * we also copy it to '_billing_dni' so all existing order/address code works.
     *
     * @param \WC_Order        $order   Order object.
     * @param \WP_REST_Request $request REST request.
     * @since 1.2.2
     */
    public function save_dni_field_blocks($order, $request) {
        // 1. Try the Additional Fields payload (WC 8.9+ blocks REST path).
        $additional = $request->get_param('additional_fields');
        $dni = !empty($additional['dniwoo/billing_dni'])
            ? sanitize_text_field($additional['dniwoo/billing_dni'])
            : '';

        // 2. Fallback: WC may have already persisted to its own meta key.
        if (empty($dni)) {
            $stored = $order->get_meta('dniwoo/billing_dni');
            $dni = !empty($stored) ? sanitize_text_field($stored) : '';
        }

        if (!empty($dni)) {
            $order->update_meta_data('_billing_dni', strtoupper(trim($dni)));
            $order->save();
        }
    }

    /**
     * Add DNI field to checkout (classic / shortcode checkout).
     *
     * @param array $fields Checkout fields.
     * @return array Modified checkout fields.
     * @since 1.0.0
     */
    public function add_dni_field($fields) {
        if ('yes' !== get_option('dniwoo_enabled', 'yes')) {
            return $fields;
        }

        $position = get_option('dniwoo_position', 'after_phone');
        $priority = $this->get_field_priority($position);
        $required = get_option('dniwoo_required', 'yes') === 'yes';

        $fields['billing']['billing_dni'] = array(
            'label' => __('DNI/NIE/CIF/NIF/NIPC', 'dniwoo'),
            'placeholder' => _x('12345678X', 'placeholder', 'dniwoo'),
            'required' => $required,
            'class' => array('form-row-wide'),
            'clear' => true,
            'priority' => $priority,
            'custom_attributes' => array(
                'data-validation' => 'dni',
                'autocomplete' => 'off',
            ),
        );

        return $fields;
    }

    /**
     * Get field priority based on position setting.
     *
     * @param string $position Field position.
     * @return int Priority number.
     * @since 1.0.0
     */
    private function get_field_priority($position) {
        $priorities = array(
            'after_email' => 35,
            'after_phone' => 105,
            'before_company' => 25,
            'after_company' => 35,
            'end' => 200,
        );

        return isset($priorities[$position]) ? $priorities[$position] : 105;
    }

    /**
     * Add DNI to formatted address.
     *
     * @param array    $address Formatted address.
     * @param WC_Order $order Order object.
     * @return array Modified address.
     * @since 1.0.0
     */
    public function add_dni_to_address($address, $order) {
        $dni = $order->get_meta('_billing_dni');
        if ($dni) {
            $address['dni'] = $dni;
        }
        return $address;
    }

    /**
     * Modify address format to include DNI.
     *
     * @param array $formats Address formats.
     * @return array Modified formats.
     * @since 1.0.0
     */
    public function modify_address_format($formats) {
        foreach ($formats as $country => $format) {
            // Only inject DNI line for Spain and Portugal.
            // Guard against duplicate injection on repeated filter calls.
            if (false !== strpos($format, '{dni}')) {
                continue;
            }
            if ($country === 'ES' || $country === 'PT') {
                // Inject just the bare token — the label prefix is added in
                // replace_dni_placeholder() together with the value so that
                // when there is no DNI the whole line collapses to empty and
                // WooCommerce removes it (avoids orphan "DNI/NIE/CIF: " lines).
                $formats[$country] = str_replace('{name}', "{name}\n{dni}", $format);
            }
        }
        return $formats;
    }

    /**
     * Replace DNI placeholder in address.
     *
     * @param array $replacements Address replacements.
     * @param array $args Address arguments.
     * @return array Modified replacements.
     * @since 1.0.0
     */
    public function replace_dni_placeholder($replacements, $args) {
        if (!empty($args['dni'])) {
            // Include the country-aware label so the format line is complete
            // only when a value actually exists.
            $country = isset($args['country']) ? $args['country'] : 'ES';
            $label   = ($country === 'PT')
                ? __('NIF/NIPC:', 'dniwoo')
                : __('DNI/NIE/CIF:', 'dniwoo');
            $replacements['{dni}'] = $label . ' ' . $args['dni'];
        } else {
            // Empty string -> WC strips the blank line from the formatted address.
            $replacements['{dni}'] = '';
        }
        return $replacements;
    }

    /**
     * Save DNI field to order meta.
     *
     * @param WC_Order $order Order object.
     * @since 1.0.0
     * @since 1.1.0 Updated to use WC_Order object for HPOS compatibility
     */
    public function save_dni_field($order) {
        if (!empty($_POST['billing_dni'])) {
            $dni = sanitize_text_field(wp_unslash($_POST['billing_dni']));
            $order->update_meta_data('_billing_dni', $dni);

            // Save country for reference
            if (!empty($_POST['billing_country'])) {
                $country = sanitize_text_field(wp_unslash($_POST['billing_country']));
                $order->update_meta_data('_billing_country_dni', $country);
            }
            
            $order->save();
        }
    }

    /**
     * Display DNI in admin order page.
     *
     * @param WC_Order $order Order object.
     * @since 1.0.0
     */
    public function display_dni_admin($order) {
        $dni = $order->get_meta('_billing_dni');
        $country = $order->get_billing_country();

        if ($dni) {
            $label = ($country === 'PT') ? __('NIF/NIPC:', 'dniwoo') : __('DNI/NIE/CIF:', 'dniwoo');
            echo '<p><strong>' . esc_html($label) . '</strong> ' . esc_html($dni) . '</p>';
        }
    }

    /**
     * Add DNI column to orders list.
     *
     * @param array $columns Existing columns.
     * @return array Modified columns.
     * @since 1.0.0
     */
    public function add_dni_column($columns) {
        $new_columns = array();

        foreach ($columns as $key => $name) {
            $new_columns[$key] = $name;
            if ('billing_address' === $key) {
                $new_columns['billing_dni'] = __('Document', 'dniwoo');
            }
        }

        return $new_columns;
    }

    /**
     * Display DNI in orders list column (HPOS).
     *
     * @param string   $column Column name.
     * @param WC_Order $order  Order object.
     * @since 1.1.0
     */
    public function display_dni_column_hpos($column, $order) {
        if ('billing_dni' === $column) {
            $dni = $order->get_meta('_billing_dni', true);
            echo $dni ? esc_html($dni) : '—';
        }
    }

    /**
     * Display DNI in orders list column (Legacy).
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     * @since 1.0.0
     * @since 1.1.0 Renamed for backwards compatibility
     */
    public function display_dni_column_legacy($column, $post_id) {
        if ('billing_dni' === $column) {
            $order = wc_get_order($post_id);
            if ($order) {
                $dni = $order->get_meta('_billing_dni', true);
                echo $dni ? esc_html($dni) : '—';
            } else {
                echo '—';
            }
        }
    }

    /**
     * Make DNI column sortable.
     *
     * @param array $columns Sortable columns.
     * @return array Modified sortable columns.
     * @since 1.0.0
     */
    public function make_dni_column_sortable($columns) {
        $columns['billing_dni'] = '_billing_dni';
        return $columns;
    }
}
