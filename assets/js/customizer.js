/**
 * CampaignPress Customizer Live Preview
 *
 * @package CampaignPress
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  // Wait for customizer to be ready
  wp.customize.bind('ready', function () {

    /**
     * Color scheme definitions
     * Maps scheme slugs to their primary and accent colors
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

    /**
     * Apply color scheme to the preview
     *
     * @param {string} scheme - The scheme slug
     */
    function applyColorScheme(scheme) {
      var colors = colorSchemes[scheme];
      if (!colors) {
        return;
      }

      // Create or update the color scheme style element
      var styleId = 'campaignpress-scheme-preview';
      var $style = $('#' + styleId);

      if (!$style.length) {
        $style = $('<style id="' + styleId + '"></style>');
        $('head').append($style);
      }

      var css =
        'body {' +
        '--cp-primary: ' + colors.primary + ' !important;' +
        '--wp--preset--color--primary: ' + colors.primary + ' !important;' +
        '--cp-primary-dark: ' + colors.primaryDark + ' !important;' +
        '--wp--preset--color--primary-dark: ' + colors.primaryDark + ' !important;' +
        '--cp-secondary: ' + colors.accent + ' !important;' +
        '--wp--preset--color--accent: ' + colors.accent + ' !important;' +
        '}';

      $style.html(css);

      // Update the color picker controls in the customizer panel if available
      if (wp.customize && wp.customize.control) {
        var primaryControl = wp.customize.control('campaignpress_primary_color');
        var accentControl = wp.customize.control('campaignpress_secondary_color');

        if (primaryControl) {
          wp.customize('campaignpress_primary_color').set(colors.primary);
        }
        if (accentControl) {
          wp.customize('campaignpress_secondary_color').set(colors.accent);
        }
      }
    }

    // Blog name
    wp.customize('blogname', function (value) {
      value.bind(function (newval) {
        $('.wp-block-site-title a, .site-title a').text(newval);
      });
    });

    // Blog description
    wp.customize('blogdescription', function (value) {
      value.bind(function (newval) {
        $('.wp-block-site-tagline, .site-description').text(newval);
      });
    });

    // Primary color
    wp.customize('campaignpress_primary_color', function (value) {
      value.bind(function (newval) {
        // Create or update style element for primary color
        var styleId = 'campaignpress-primary-preview';
        var $style = $('#' + styleId);

        if (!$style.length) {
          $style = $('<style id="' + styleId + '"></style>');
          $('head').append($style);
        }

        var css =
          'body {' +
          '--cp-primary: ' + newval + ' !important;' +
          '--wp--preset--color--primary: ' + newval + ' !important;' +
          '}';

        $style.html(css);
      });
    });

    // Secondary/accent color
    wp.customize('campaignpress_secondary_color', function (value) {
      value.bind(function (newval) {
        // Create or update style element for secondary/accent color
        var styleId = 'campaignpress-secondary-preview';
        var $style = $('#' + styleId);

        if (!$style.length) {
          $style = $('<style id="' + styleId + '"></style>');
          $('head').append($style);
        }

        var css =
          'body {' +
          '--cp-secondary: ' + newval + ' !important;' +
          '--wp--preset--color--accent: ' + newval + ' !important;' +
          '}';

        $style.html(css);
      });
    });

    // Color scheme
    wp.customize('campaignpress_color_scheme', function (value) {
      value.bind(function (newval) {
        // Update body class for CSS fallback
        $('body').removeClass(function (index, className) {
          return (className.match(/\bcolor-scheme-\S+/g) || []).join(' ');
        });
        $('body').addClass('color-scheme-' + newval);

        // Apply the color scheme colors
        applyColorScheme(newval);
      });
    });

    // Primary menu layout
    wp.customize('campaignpress_primary_menu_layout', function (value) {
      value.bind(function (newval) {
        $('body')
          .removeClass('cp-primary-menu-inline cp-primary-menu-vertical')
          .addClass('cp-primary-menu-' + newval);
      });
    });

    // Disclaimer text
    wp.customize('campaignpress_disclaimer_text', function (value) {
      value.bind(function (newval) {
        const $disclaimer = $('.cp-disclaimer');
        if (!$disclaimer.length) {
          return;
        }

        $disclaimer.text((newval || '').trim());
      });
    });

    // Hero overlay opacity
    wp.customize('campaignpress_hero_overlay_opacity', function (value) {
      value.bind(function (newval) {
        var opacity = parseFloat(newval) / 100;
        $(
          '.is-style-campaign-hero .wp-block-cover__background, .campaign-hero .wp-block-cover__background, .hero-section .wp-block-cover__background, .hero-video-section .wp-block-cover__background, .cp-hero .wp-block-cover__background'
        ).css('opacity', opacity);
      });
    });

    // Hero background image
    wp.customize('campaignpress_hero_image', function (value) {
      value.bind(function (newval) {
        var mediaType = wp.customize('campaignpress_hero_media_type').get();
        if (mediaType === 'image' && newval) {
          var $heroSections = $('.is-style-campaign-hero, .campaign-hero, .hero-section, .hero-video-section, .cp-hero');
          
          // Update background-image on the wrapper
          $heroSections.css(
            'background-image',
            'url("' + newval + '")'
          );

          // Also update <img> if it exists (standard wp-block-cover structure)
          $heroSections.find('.wp-block-cover__image-background').attr('src', newval);
        }
      });
    });

    // Hero media type toggle
    wp.customize('campaignpress_hero_media_type', function (value) {
      value.bind(function (newval) {
        var $heroSections = $('.is-style-campaign-hero, .campaign-hero, .hero-section, .hero-video-section, .cp-hero');

        if (newval === 'video') {
          // Remove background image when video is selected
          $heroSections.css('background-image', 'none');
          
          // Hide image background if it's an <img> tag
          $heroSections.find('.wp-block-cover__image-background').hide();

          // Add video if URL is set
          var videoUrl = wp.customize('campaignpress_hero_video').get();
          if (videoUrl) {
            $heroSections.each(function () {
              var $section = $(this);
              if (!$section.find('.cp-hero-video').length) {
                var $video = $('<video>', {
                  class:
                    'wp-block-cover__video-background intrinsic-ignore cp-hero-video',
                  autoplay: true,
                  muted: true,
                  loop: true,
                  playsinline: true,
                  src: videoUrl,
                }).css({
                  position: 'absolute',
                  top: '50%',
                  left: '50%',
                  'min-width': '100%',
                  'min-height': '100%',
                  width: 'auto',
                  height: 'auto',
                  transform: 'translate(-50%, -50%)',
                  'object-fit': 'cover',
                  'z-index': 0,
                });
                $section.prepend($video);
              } else {
                $section.find('.cp-hero-video').show();
              }
            });
          }
        } else {
          // Remove/Hide video elements when image is selected
          $heroSections.find('.cp-hero-video').hide();
          
          // Show image background
          $heroSections.find('.wp-block-cover__image-background').show();

          // Restore background image if set
          var imageUrl = wp.customize('campaignpress_hero_image').get();
          if (imageUrl) {
            $heroSections.css('background-image', 'url("' + imageUrl + '")');
          }
        }
      });
    });

    // Hero video URL
    wp.customize('campaignpress_hero_video', function (value) {
      value.bind(function (newval) {
        var mediaType = wp.customize('campaignpress_hero_media_type').get();
        if (mediaType !== 'video') {
          return;
        }

        var $video = $('.cp-hero-video');
        if ($video.length && newval) {
          $video.attr('src', newval).show();
        } else if (newval) {
          // Video doesn't exist yet, create it
          var $heroSections = $('.is-style-campaign-hero, .campaign-hero, .hero-section, .hero-video-section, .cp-hero');
          $heroSections.each(function () {
            var $section = $(this);
            if (!$section.find('.cp-hero-video').length) {
              var $newVideo = $('<video>', {
                class:
                  'wp-block-cover__video-background intrinsic-ignore cp-hero-video',
                autoplay: true,
                muted: true,
                loop: true,
                playsinline: true,
                src: newval,
              }).css({
                position: 'absolute',
                top: '50%',
                left: '50%',
                'min-width': '100%',
                'min-height': '100%',
                width: 'auto',
                height: 'auto',
                transform: 'translate(-50%, -50%)',
                'object-fit': 'cover',
                'z-index': 0,
              });
              $section.prepend($newVideo);
            } else {
              $section.find('.cp-hero-video').attr('src', newval).show();
            }
          });
        }
      });
    });

  });
})(jQuery);
