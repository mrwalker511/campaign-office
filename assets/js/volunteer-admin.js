/**
 * Volunteer Admin JavaScript
 *
 * Handles admin functionality for volunteer management
 *
 * @package CampaignPress
 * @since 2.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Select all checkbox functionality
        $('#cb-select-all').on('change', function() {
            $('input[name="volunteer_ids[]"]').prop('checked', $(this).prop('checked'));
        });

        // Update select all checkbox when individual checkboxes change
        $('input[name="volunteer_ids[]"]').on('change', function() {
            var allChecked = $('input[name="volunteer_ids[]"]').length === $('input[name="volunteer_ids[]"]:checked').length;
            $('#cb-select-all').prop('checked', allChecked);
        });

        // Confirm bulk delete
        $('form').on('submit', function(e) {
            if ($('select[name="cp_bulk_action"]').val() === 'delete') {
                var checkedCount = $('input[name="volunteer_ids[]"]:checked').length;
                if (checkedCount > 0) {
                    if (!confirm('Are you sure you want to delete ' + checkedCount + ' volunteer(s)?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });

        // Confirm individual delete
        $('.submitdelete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this volunteer?')) {
                e.preventDefault();
                return false;
            }
        });
    });

})(jQuery);
