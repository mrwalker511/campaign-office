/**
 * CampaignPress Customizer Live Preview
 *
 * @package CampaignPress
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  wp.customize('blogname', function (value) {
    value.bind(function (newval) {
      $('.wp-block-site-title a, .site-title a').text(newval);
    });
  });

  wp.customize('blogdescription', function (value) {
    value.bind(function (newval) {
      $('.wp-block-site-tagline, .site-description').text(newval);
    });
  });

  wp.customize('campaignpress_primary_color', function (value) {
    value.bind(function (newval) {
      $('body').css('--cp-primary', newval);
      $('body').css('--wp--preset--color--primary', newval);
    });
  });

  wp.customize('campaignpress_secondary_color', function (value) {
    value.bind(function (newval) {
      $('body').css('--cp-secondary', newval);
      $('body').css('--wp--preset--color--accent', newval);
    });
  });

  wp.customize('campaignpress_color_scheme', function (value) {
    value.bind(function (newval) {
      $('body').removeClass(function (index, className) {
        return (className.match(/\bcolor-scheme-\S+/g) || []).join(' ');
      });

      $('body').addClass('color-scheme-' + newval);
    });
  });

  wp.customize('campaignpress_primary_menu_layout', function (value) {
    value.bind(function (newval) {
      $('body')
        .removeClass('cp-primary-menu-inline cp-primary-menu-vertical')
        .addClass('cp-primary-menu-' + newval);
    });
  });

  wp.customize('campaignpress_disclaimer_text', function (value) {
    value.bind(function (newval) {
      const $disclaimer = $('.cp-disclaimer');
      if (!$disclaimer.length) {
        return;
      }

      $disclaimer.text((newval || '').trim());
    });
  });
})(jQuery);
