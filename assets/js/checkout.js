/**
 * DNIWOO Checkout JavaScript
 * 
 * @package DNIWOO
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * DNIWOO Checkout Handler
     */
    const DNIWOOCheckout = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.updateFieldByCountry();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Country change events
            $(document).on('change', '#billing_country', this.updateFieldByCountry.bind(this));
            $(document).on('select2:select', '#billing_country', this.updateFieldByCountry.bind(this));
            $(document).on('country_to_state_changed', this.updateFieldByCountry.bind(this));

            // DNI field events
            if (dniwoo_params.validation_mode === 'real_time') {
                $(document).on('input blur', '#billing_dni', this.validateField.bind(this));
            }

            $(document).on('input', '#billing_dni', this.formatField.bind(this));

            // Form submit validation
            $('form.woocommerce-checkout').on('submit', this.validateOnSubmit.bind(this));
        },

        /**
         * Update field based on country
         */
        updateFieldByCountry: function() {
            const country = $('#billing_country').val() || 'ES';
            const $field = $('#billing_dni');
            const $label = $('label[for="billing_dni"]');

            if (!$field.length || !$label.length) return;

            const messages = dniwoo_params.messages[country] || dniwoo_params.messages.es;

            // Update label
            let labelText = messages.label;
            if ($field.prop('required')) {
                labelText += ' <span class="required">*</span>';
            }
            $label.html(labelText);

            // Update placeholder
            $field.attr('placeholder', messages.placeholder);

            // Clear validation
            this.clearValidation($field);
            $field.val('');
        },

        /**
         * Format field input
         */
        formatField: function(e) {
            const $field = $(e.target);
            const country = $('#billing_country').val() || 'ES';
            let value = $field.val().toUpperCase();

            if (country === 'PT') {
                // Portugal: only numbers, max 9
                value = value.replace(/[^0-9]/g, '');
                if (value.length > 9) {
                    value = value.substr(0, 9);
                }
            } else {
                // Spain: specific format validation
                if (value.length <= 9) {
                    // Allow Spanish formats
                    const patterns = [
                        /^[0-9]{0,8}[A-Z]?$/,           // DNI
                        /^[XYZ][0-9]{0,7}[A-Z]?$/,     // NIE
                        /^[ABCDEFGHJKLMNPQRSUVW][0-9]{0,7}[0-9A-J]?$/ // CIF
                    ];

                    const isValidFormat = patterns.some(pattern => pattern.test(value));
                    if (!isValidFormat) {
                        value = value.replace(/[^0-9XYZABCDEFGHJKLMNPQRSUVW]/g, '');
                    }
                }
            }

            $field.val(value);
        },

        /**
         * Validate field via AJAX
         */
        validateField: function(e) {
            const $field = $(e.target);
            const dni = $field.val().trim();
            const country = $('#billing_country').val() || 'ES';

            if (!dni) {
                this.clearValidation($field);
                return;
            }

            // Show loading
            this.showLoading($field);

            $.ajax({
                url: dniwoo_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'dniwoo_validate',
                    dni: dni,
                    country: country,
                    nonce: dniwoo_params.nonce
                },
                success: (response) => {
                    this.hideLoading($field);
                    this.showValidationResult($field, response, country);
                },
                error: () => {
                    this.hideLoading($field);
                    this.clearValidation($field);
                }
            });
        },

        /**
         * Validate on form submit
         */
        validateOnSubmit: function(e) {
            const $field = $('#billing_dni');
            const dni = $field.val().trim();
            
            if (!dni && !$field.prop('required')) {
                return true;
            }

            if (!this.isValidDocument(dni)) {
                e.preventDefault();
                this.showError($field);
                this.scrollToField($field);
                return false;
            }

            return true;
        },

        /**
         * Client-side validation
         */
        isValidDocument: function(document) {
            const country = $('#billing_country').val() || 'ES';
            document = document.toUpperCase().trim();

            if (country === 'PT') {
                return this.validatePortugal(document);
            } else {
                return this.validateSpain(document);
            }
        },

        /**
         * Validate Portuguese document
         */
        validatePortugal: function(document) {
            document = document.replace(/[\s\-]/g, '');
            
            if (!/^[0-9]{9}$/.test(document)) {
                return false;
            }
            
            const checkDigit = parseInt(document.substr(8, 1));
            let sum = 0;
            
            for (let i = 0; i < 8; i++) {
                sum += parseInt(document.charAt(i)) * (9 - i);
            }
            
            const remainder = sum % 11;
            const calculatedDigit = remainder < 2 ? 0 : 11 - remainder;
            
            return checkDigit === calculatedDigit;
        },

        /**
         * Validate Spanish document
         */
        validateSpain: function(document) {
            // DNI
            if (/^[0-9]{8}[A-Z]$/.test(document)) {
                return this.validateDNI(document);
            }
            // NIE
            if (/^[XYZ][0-9]{7}[A-Z]$/.test(document)) {
                return this.validateNIE(document);
            }
            // CIF
            if (/^[ABCDEFGHJKLMNPQRSUVW][0-9]{7}[0-9A-J]$/.test(document)) {
                return this.validateCIF(document);
            }
            return false;
        },

        /**
         * Validate Spanish DNI
         */
        validateDNI: function(dni) {
            const letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
            const number = dni.substr(0, 8);
            const letter = dni.charAt(8);
            return letter === letters[number % 23];
        },

        /**
         * Validate Spanish NIE
         */
        validateNIE: function(nie) {
            const mapIni = { 'X': '0', 'Y': '1', 'Z': '2' };
            const initialLetter = nie.charAt(0);
            const number = mapIni[initialLetter] + nie.substr(1, 7);
            const letter = nie.charAt(8);
            return this.validateDNI(number + letter);
        },

        /**
         * Validate Spanish CIF
         */
        validateCIF: function(cif) {
            const initialLetter = cif.charAt(0);
            const controlDigit = cif.charAt(8);
            const numbers = cif.substr(1, 7);
            const controlLetters = 'JABCDEFGHI';
            
            let evenSum = 0;
            let oddSum = 0;
            
            // Even positions
            for (let i = 1; i < 7; i += 2) {
                evenSum += parseInt(numbers.charAt(i), 10);
            }
            
            // Odd positions (with doubling)
            for (let i = 0; i < 7; i += 2) {
                let doubled = parseInt(numbers.charAt(i), 10) * 2;
                oddSum += doubled > 9 ? doubled - 9 : doubled;
            }
            
            const total = evenSum + oddSum;
            const calculatedDigit = (10 - (total % 10)) % 10;
            
            if (['K', 'P', 'Q', 'R', 'S', 'W'].includes(initialLetter)) {
                return !isNaN(controlDigit) && parseInt(controlDigit, 10) === calculatedDigit;
            }
            
            if (['A', 'B', 'E', 'H'].includes(initialLetter)) {
                return controlDigit === controlLetters.charAt(calculatedDigit);
            }
            
            return (!isNaN(controlDigit) && parseInt(controlDigit, 10) === calculatedDigit) ||
                   (controlDigit === controlLetters.charAt(calculatedDigit));
        },

        /**
         * Show validation result
         */
        showValidationResult: function($field, response, country) {
            this.clearValidation($field);

            if (response.valid) {
                this.showSuccess($field, response.message);
                $field.removeClass('dniwoo-error');
            } else {
                this.showError($field, response.message);
                $field.addClass('dniwoo-error');
            }
        },

        /**
         * Show success message
         */
        showSuccess: function($field, message) {
            const country = $('#billing_country').val() || 'ES';
            const defaultMessage = dniwoo_params.messages[country].valid;
            const $feedback = $('<small class="dniwoo-feedback dniwoo-valid"></small>')
                .text(message || defaultMessage)
                .insertAfter($field);
        },

        /**
         * Show error message
         */
        showError: function($field, message) {
            const country = $('#billing_country').val() || 'ES';
            const defaultMessage = dniwoo_params.messages[country].invalid;
            const $feedback = $('<small class="dniwoo-feedback dniwoo-error"></small>')
                .text(message || defaultMessage)
                .insertAfter($field);
            $field.addClass('dniwoo-error');
        },

        /**
         * Show loading indicator
         */
        showLoading: function($field) {
            this.clearValidation($field);
            const $loading = $('<small class="dniwoo-feedback dniwoo-loading">⏳ ' + 'Validating...' + '</small>')
                .insertAfter($field);
        },

        /**
         * Hide loading indicator
         */
        hideLoading: function($field) {
            $field.siblings('.dniwoo-loading').remove();
        },

        /**
         * Clear validation messages
         */
        clearValidation: function($field) {
            $field.siblings('.dniwoo-feedback').remove();
            $field.removeClass('dniwoo-error');
        },

        /**
         * Scroll to field
         */
        scrollToField: function($field) {
            $('html, body').animate({
                scrollTop: $field.offset().top - 100
            }, 500);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        DNIWOOCheckout.init();
    });

})(jQuery);
