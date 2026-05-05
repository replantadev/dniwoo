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
        add_filter('woocommerce_checkout_fields', array($this, 'add_dni_field'));
        add_filter('woocommerce_order_formatted_billing_address', array($this, 'add_dni_to_address'), 10, 2);
        add_filter('woocommerce_localisation_address_formats', array($this, 'modify_address_format'));
        add_filter('woocommerce_formatted_address_replacements', array($this, 'replace_dni_placeholder'), 10, 2);
        add_action('woocommerce_checkout_order_created', array($this, 'save_dni_field'));
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
     * Add DNI field to checkout.
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
            // Only inject DNI line for Spain and Portugal; skip all other countries.
            // Guard against duplicate injection on repeated filter calls.
            if (false !== strpos($format, '{dni}')) {
                continue;
            }
            if ($country === 'ES') {
                $formats[$country] = str_replace('{name}', "{name}\n" . __('DNI/NIE/CIF:', 'dniwoo') . ' {dni}', $format);
            } elseif ($country === 'PT') {
                $formats[$country] = str_replace('{name}', "{name}\n" . __('NIF/NIPC:', 'dniwoo') . ' {dni}', $format);
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
        $replacements['{dni}'] = !empty($args['dni']) ? $args['dni'] : '';
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
