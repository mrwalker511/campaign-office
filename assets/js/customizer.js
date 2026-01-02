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

  // Hero overlay opacity
  wp.customize('campaignpress_hero_overlay_opacity', function (value) {
    value.bind(function (newval) {
      var opacity = parseFloat(newval) / 100;
      $(
        '.is-style-campaign-hero .wp-block-cover__background, .hero-video-section .wp-block-cover__background'
      ).css('opacity', opacity);
    });
  });

  // Hero background image
  wp.customize('campaignpress_hero_image', function (value) {
    value.bind(function (newval) {
      var mediaType = wp.customize('campaignpress_hero_media_type').get();
      if (mediaType === 'image' && newval) {
        $('.is-style-campaign-hero, .hero-video-section').css(
          'background-image',
          'url("' + newval + '")'
        );
      }
    });
  });

  // Hero media type toggle
  wp.customize('campaignpress_hero_media_type', function (value) {
    value.bind(function (newval) {
      var $heroSections = $('.is-style-campaign-hero, .hero-video-section');

      if (newval === 'video') {
        // Remove background image when video is selected
        $heroSections.css('background-image', 'none');

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
            }
          });
        }
      } else {
        // Remove video elements when image is selected
        $heroSections.find('.cp-hero-video').remove();

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
        $video.attr('src', newval);
      } else if (newval) {
        // Video doesn't exist yet, create it
        var $heroSections = $('.is-style-campaign-hero, .hero-video-section');
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
          }
        });
      }
    });
  });
})(jQuery);
