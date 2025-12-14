import '../css/app.css';

/**
 * CampaignPress Main JavaScript
 *
 * @package CampaignPress
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    // Mobile menu toggle
    initMobileMenu();

    // Smooth scroll for anchor links
    initSmoothScroll();

    // Campaign progress animations
    initProgressAnimations();

    // Event countdown updates
    initCountdownTimers();
  });

  /**
   * Initialize mobile menu
   */
  function initMobileMenu() {
    const $menuToggle = $('.menu-toggle');
    const $mobileMenu = $('.mobile-menu');

    $menuToggle.on('click', function (e) {
      e.preventDefault();
      $(this).toggleClass('active');
      $mobileMenu.toggleClass('active');
      $('body').toggleClass('menu-open');
    });

    // Close menu when clicking outside
    $(document).on('click', function (e) {
      if (!$(e.target).closest('.mobile-menu, .menu-toggle').length) {
        $menuToggle.removeClass('active');
        $mobileMenu.removeClass('active');
        $('body').removeClass('menu-open');
      }
    });
  }

  /**
   * Initialize smooth scrolling for anchor links
   */
  function initSmoothScroll() {
    $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').on('click', function (e) {
      if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') &&
        location.hostname === this.hostname) {

        let target = $(this.hash);
        target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

        if (target.length) {
          e.preventDefault();
          $('html, body').animate({
            scrollTop: target.offset().top - 80
          }, 800);
        }
      }
    });
  }

  /**
   * Animate progress bars when they come into view
   */
  function initProgressAnimations() {
    const $progressBars = $('.cp-progress-bar');

    if ($progressBars.length === 0) {
      return;
    }

    // Intersection Observer for scroll animations
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            const $bar = $(entry.target);
            const width = $bar.attr('style').match(/width:\s*(\d+(?:\.\d+)?%)/);

            if (width) {
              $bar.css('width', '0%');
              setTimeout(function () {
                $bar.css('width', width[1]);
              }, 100);
            }

            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.5 });

      $progressBars.each(function () {
        observer.observe(this);
      });
    }
  }

  /**
   * Initialize countdown timers
   */
  function initCountdownTimers() {
    const $countdowns = $('.cp-event-countdown[data-date]');

    if ($countdowns.length === 0) {
      return;
    }

    $countdowns.each(function () {
      const $countdown = $(this);
      const targetDate = new Date($countdown.data('date')).getTime();

      updateCountdown($countdown, targetDate);

      // Update every hour
      setInterval(function () {
        updateCountdown($countdown, targetDate);
      }, 3600000);
    });
  }

  /**
   * Update countdown display
   */
  function updateCountdown($element, targetDate) {
    const now = new Date().getTime();
    const distance = targetDate - now;

    if (distance < 0) {
      $element.find('.cp-countdown-display').html(
        '<p>' + campaignpress_vars.countdown_ended + '</p>'
      );
      return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

    $element.find('.cp-countdown-number').text(days);
    $element.find('.cp-countdown-label').text(days === 1 ? 'Day' : 'Days');
  }

  /**
   * AJAX form submission helper
   */
  window.campaignpress = window.campaignpress || {};

  window.campaignpress.ajaxSubmit = function (formData, callback) {
    $.ajax({
      url: campaignpress_vars.ajax_url,
      type: 'POST',
      data: formData,
      success: function (response) {
        if (typeof callback === 'function') {
          callback(response);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
        if (typeof callback === 'function') {
          callback({ success: false, message: 'An error occurred.' });
        }
      }
    });
  };

})(jQuery);
