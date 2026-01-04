/**
 * CampaignPress Customizer Controls
 *
 * Handles interactions in the customizer panel (not the preview frame)
 *
 * @package CampaignPress
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  /**
   * Color scheme definitions
   * Must match the PHP definitions in customizer.php
   */
  var colorSchemes = {
    'democrat-blue': {
      primary: '#0053c3',
      primaryDark: '#003275',
      accent: '#ff8800'
    },
    'republican-red': {
      primary: '#e81b23',
      primaryDark: '#9e0e14',
      accent: '#002868'
    },
    'independent-purple': {
      primary: '#6554c0',
      primaryDark: '#4a3d91',
      accent: '#00b8d9'
    },
    'green-party': {
      primary: '#228B22',
      primaryDark: '#1a6b1a',
      accent: '#FFD700'
    },
    'neutral': {
      primary: '#495057',
      primaryDark: '#343a40',
      accent: '#6c757d'
    }
  };

  // Wait for customizer to be ready
  wp.customize.bind('ready', function () {
    // Listen for color scheme changes
    wp.customize('campaignpress_color_scheme', function (setting) {
      setting.bind(function (scheme) {
        var colors = colorSchemes[scheme];
        if (!colors) {
          return;
        }

        // Update the primary color control
        var primarySetting = wp.customize('campaignpress_primary_color');
        if (primarySetting) {
          primarySetting.set(colors.primary);
        }

        // Update the accent/secondary color control
        var accentSetting = wp.customize('campaignpress_secondary_color');
        if (accentSetting) {
          accentSetting.set(colors.accent);
        }
      });
    });
  });
})(jQuery);
