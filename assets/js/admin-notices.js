/**
 * Admin Notices JavaScript
 *
 * @package CampaignPress
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Handle demo notice dismissal
        $('.campaignpress-dismiss-demo-notice').on('click', function (e) {
            e.preventDefault();

            $.post(campaignpress_admin_notices.ajax_url, {
                action: 'campaignpress_dismiss_demo_notice',
                nonce: campaignpress_admin_notices.demo_nonce
            }, function (response) {
                if (response && response.success) {
                    $('.campaignpress-demo-notice').fadeOut();
                }
            }).fail(function () {
                // Fail silently in production
            });
        });

        // Handle donation notice dismissal
        $('.campaignpress-dismiss-donation-notice, .campaignpress-donation-notice .notice-dismiss').on('click', function (e) {
            $.post(campaignpress_admin_notices.ajax_url, {
                action: 'campaignpress_dismiss_donation_notice',
                nonce: campaignpress_admin_notices.donation_nonce
            }, function (response) {
                if (response && response.success) {
                    $('.campaignpress-donation-notice').fadeOut();
                }
            }).fail(function () {
                // Fail silently in production
            });
        });
    });

})(jQuery);
