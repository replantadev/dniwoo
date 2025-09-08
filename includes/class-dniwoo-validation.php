<?php
/**
 * DNIWOO Validation
 *
 * @package DNIWOO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DNIWOO_Validation class.
 */
class DNIWOO_Validation {

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
        add_action('woocommerce_checkout_process', array($this, 'validate_dni_field'));
        add_action('wp_ajax_dniwoo_validate', array($this, 'ajax_validate_dni'));
        add_action('wp_ajax_nopriv_dniwoo_validate', array($this, 'ajax_validate_dni'));
    }

    /**
     * Validate DNI field during checkout.
     *
     * @since 1.0.0
     */
    public function validate_dni_field() {
        $dni = isset($_POST['billing_dni']) ? strtoupper(trim(sanitize_text_field(wp_unslash($_POST['billing_dni'])))) : '';
        $country = isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : 'ES';

        if (empty($dni)) {
            if (get_option('dniwoo_required', 'yes') === 'yes') {
                $message = $this->get_empty_field_message($country);
                wc_add_notice($message, 'error');
            }
            return;
        }

        $validation_result = $this->validate_document($dni, $country);

        if (!$validation_result['valid']) {
            $message = $this->get_validation_error_message($country);
            wc_add_notice($message, 'error');
        }
    }

    /**
     * AJAX validation endpoint.
     *
     * @since 1.0.0
     */
    public function ajax_validate_dni() {
        check_ajax_referer('dniwoo_validate', 'nonce');

        $dni = isset($_POST['dni']) ? strtoupper(trim(sanitize_text_field(wp_unslash($_POST['dni'])))) : '';
        $country = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : 'ES';

        $result = $this->validate_document($dni, $country);

        wp_send_json($result);
    }

    /**
     * Validate document based on country.
     *
     * @param string $document Document number.
     * @param string $country Country code.
     * @return array Validation result.
     * @since 1.0.0
     */
    public function validate_document($document, $country) {
        $document = strtoupper(trim($document));

        if (empty($document)) {
            return array(
                'valid' => false,
                'type' => '',
                'message' => $this->get_empty_field_message($country),
            );
        }

        switch ($country) {
            case 'PT':
                return $this->validate_portugal_document($document);
            case 'ES':
            default:
                return $this->validate_spain_document($document);
        }
    }

    /**
     * Validate Portuguese document (NIF/NIPC).
     *
     * @param string $document Document number.
     * @return array Validation result.
     * @since 1.0.0
     */
    private function validate_portugal_document($document) {
        // Remove spaces and hyphens
        $document = preg_replace('/[\s\-]/', '', $document);

        // Must have exactly 9 digits
        if (!preg_match('/^[0-9]{9}$/', $document)) {
            return array(
                'valid' => false,
                'type' => '',
                'message' => __('Invalid format. Must be 9 digits.', 'dniwoo-pro'),
            );
        }

        // Portuguese NIF validation algorithm
        $check_digit = (int) substr($document, 8, 1);
        $sum = 0;

        for ($i = 0; $i < 8; $i++) {
            $sum += (int) substr($document, $i, 1) * (9 - $i);
        }

        $remainder = $sum % 11;
        $calculated_digit = $remainder < 2 ? 0 : 11 - $remainder;

        if ($check_digit === $calculated_digit) {
            // Determine if it's NIF (individual) or NIPC (company)
            $first_digit = (int) substr($document, 0, 1);
            $type = in_array($first_digit, array(1, 2, 3, 5, 6, 8), true) ? 'NIF' : 'NIPC';

            return array(
                'valid' => true,
                'type' => $type,
                'message' => sprintf(__('Valid %s', 'dniwoo-pro'), $type),
            );
        }

        return array(
            'valid' => false,
            'type' => '',
            'message' => __('Invalid NIF/NIPC', 'dniwoo-pro'),
        );
    }

    /**
     * Validate Spanish document (DNI/NIE/CIF).
     *
     * @param string $document Document number.
     * @return array Validation result.
     * @since 1.0.0
     */
    private function validate_spain_document($document) {
        // DNI validation
        if (preg_match('/^[0-9]{8}[A-Z]$/', $document)) {
            $valid = $this->validate_dni($document);
            return array(
                'valid' => $valid,
                'type' => $valid ? 'DNI' : '',
                'message' => $valid ? __('Valid DNI', 'dniwoo-pro') : __('Invalid DNI', 'dniwoo-pro'),
            );
        }

        // NIE validation
        if (preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $document)) {
            $valid = $this->validate_nie($document);
            return array(
                'valid' => $valid,
                'type' => $valid ? 'NIE' : '',
                'message' => $valid ? __('Valid NIE', 'dniwoo-pro') : __('Invalid NIE', 'dniwoo-pro'),
            );
        }

        // CIF validation
        if (preg_match('/^[ABCDEFGHJKLMNPQRSUVW][0-9]{7}[0-9A-J]$/', $document)) {
            $valid = $this->validate_cif($document);
            return array(
                'valid' => $valid,
                'type' => $valid ? 'CIF' : '',
                'message' => $valid ? __('Valid CIF', 'dniwoo-pro') : __('Invalid CIF', 'dniwoo-pro'),
            );
        }

        return array(
            'valid' => false,
            'type' => '',
            'message' => __('Invalid format', 'dniwoo-pro'),
        );
    }

    /**
     * Validate Spanish DNI.
     *
     * @param string $dni DNI number.
     * @return bool Validation result.
     * @since 1.0.0
     */
    private function validate_dni($dni) {
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $number = substr($dni, 0, 8);
        $letter = substr($dni, 8, 1);
        return ($letter === $letters[$number % 23]);
    }

    /**
     * Validate Spanish NIE.
     *
     * @param string $nie NIE number.
     * @return bool Validation result.
     * @since 1.0.0
     */
    private function validate_nie($nie) {
        $map_ini = array('X' => '0', 'Y' => '1', 'Z' => '2');
        $initial_letter = substr($nie, 0, 1);
        $number = $map_ini[$initial_letter] . substr($nie, 1, 7);
        $letter = substr($nie, 8, 1);
        return $this->validate_dni($number . $letter);
    }

    /**
     * Validate Spanish CIF.
     *
     * @param string $cif CIF number.
     * @return bool Validation result.
     * @since 1.0.0
     */
    private function validate_cif($cif) {
        $initial_letter = substr($cif, 0, 1);
        $control_digit = substr($cif, 8, 1);
        $numbers = substr($cif, 1, 7);

        $even_sum = 0;
        $odd_sum = 0;

        // Calculate even positions sum
        for ($i = 1; $i < 7; $i += 2) {
            $even_sum += (int) $numbers[$i];
        }

        // Calculate odd positions sum (with doubling)
        for ($i = 0; $i < 7; $i += 2) {
            $doubled = (int) $numbers[$i] * 2;
            $odd_sum += $doubled > 9 ? $doubled - 9 : $doubled;
        }

        $total = $even_sum + $odd_sum;
        $calculated_digit = (10 - ($total % 10)) % 10;

        $control_letters = 'JABCDEFGHI';

        // Validation based on CIF type
        if (in_array($initial_letter, array('K', 'P', 'Q', 'R', 'S', 'W'), true)) {
            return is_numeric($control_digit) && ((int) $control_digit === $calculated_digit);
        }

        if (in_array($initial_letter, array('A', 'B', 'E', 'H'), true)) {
            return $control_digit === $control_letters[$calculated_digit];
        }

        // For other types, can be numeric or alphabetic
        return (is_numeric($control_digit) && ((int) $control_digit === $calculated_digit)) ||
               ($control_digit === $control_letters[$calculated_digit]);
    }

    /**
     * Get empty field error message.
     *
     * @param string $country Country code.
     * @return string Error message.
     * @since 1.0.0
     */
    private function get_empty_field_message($country) {
        if ($country === 'PT') {
            return __('<strong>NIF/NIPC</strong> is a required field.', 'dniwoo-pro');
        }
        return __('<strong>Document</strong> is a required field (DNI, NIE or CIF).', 'dniwoo-pro');
    }

    /**
     * Get validation error message.
     *
     * @param string $country Country code.
     * @return string Error message.
     * @since 1.0.0
     */
    private function get_validation_error_message($country) {
        if ($country === 'PT') {
            return __(
                'Invalid NIF/NIPC. Accepted formats:<br>
                • Individual NIF: 9 digits (ex: 123456789)<br>
                • Company NIPC: 9 digits (ex: 123456789)',
                'dniwoo-pro'
            );
        }

        return __(
            'Invalid document. Accepted formats:<br>
            • DNI: 8 numbers + letter (ex: 12345678Z)<br>
            • NIE: X/Y/Z + 7 numbers + letter (ex: X1234567L)<br>
            • CIF: Letter + 7 numbers + control digit (ex: A1234567C)',
            'dniwoo-pro'
        );
    }
}
