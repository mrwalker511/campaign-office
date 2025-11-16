/**
 * CampaignPress Customizer Live Preview
 *
 * @package CampaignPress
 * @since 1.0.0
 */

(function($) {
  'use strict';

  wp.customize('blogname', function(value) {
    value.bind(function(newval) {
      $('.site-title a').text(newval);
    });
  });

  wp.customize('blogdescription', function(value) {
    value.bind(function(newval) {
      $('.site-description').text(newval);
    });
  });

  wp.customize('campaignpress_primary_color', function(value) {
    value.bind(function(newval) {
      $('body').css('--cp-primary', newval);
    });
  });

  wp.customize('campaignpress_secondary_color', function(value) {
    value.bind(function(newval) {
      $('body').css('--cp-secondary', newval);
    });
  });

  wp.customize('campaignpress_color_scheme', function(value) {
    value.bind(function(newval) {
      // Remove all color scheme classes
      $('body').removeClass(function(index, className) {
        return (className.match(/\bcolor-scheme-\S+/g) || []).join(' ');
      });

      // Add new color scheme class
      $('body').addClass('color-scheme-' + newval);
    });
  });

})(jQuery);
