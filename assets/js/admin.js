/**
 * DNIWOO Admin JavaScript
 * 
 * @package DNIWOO
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * DNIWOO Admin Handler
     */
    const DNIWOOAdmin = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            $('#dniwoo-check-updates').on('click', this.checkUpdates.bind(this));
        },

        /**
         * Check for updates
         */
        checkUpdates: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const $result = $('#dniwoo-update-result');
            const originalText = $button.text();
            
            // Show loading state
            $button.prop('disabled', true).text(dniwoo_admin.strings.checking);
            $result.removeClass('notice-success notice-error').html('');
            
            // Make AJAX request
            $.ajax({
                url: dniwoo_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'dniwoo_check_updates',
                    nonce: dniwoo_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.addClass('notice notice-success')
                               .html('<p>' + response.data + '</p>');
                    } else {
                        $result.addClass('notice notice-error')
                               .html('<p>' + (response.data || dniwoo_admin.strings.error) + '</p>');
                    }
                },
                error: function() {
                    $result.addClass('notice notice-error')
                           .html('<p>' + dniwoo_admin.strings.error + '</p>');
                },
                complete: function() {
                    // Reset button
                    $button.prop('disabled', false).text(originalText);
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        DNIWOOAdmin.init();
    });

})(jQuery);
