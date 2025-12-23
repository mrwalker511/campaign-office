/**
 * Customizer Live Preview
 *
 * Real-time preview updates for the theme customizer
 *
 * @package CampaignPress
 * @since 2.0.0
 */

(function($) {
    'use strict';

    wp.customize = wp.customize || {};

    // Primary Color
    wp.customize('cp_color_primary', function(value) {
        value.bind(function(newval) {
            $('head').find('#cp-custom-css').remove();
            $('head').append('<style id="cp-preview-primary">:root { --cp-color-primary: ' + newval + ' !important; }</style>');
        });
    });

    // Secondary Color
    wp.customize('cp_color_secondary', function(value) {
        value.bind(function(newval) {
            $('head').find('#cp-preview-secondary').remove();
            $('head').append('<style id="cp-preview-secondary">:root { --cp-color-secondary: ' + newval + ' !important; }</style>');
        });
    });

    // Accent Color
    wp.customize('cp_color_accent', function(value) {
        value.bind(function(newval) {
            $('head').find('#cp-preview-accent').remove();
            $('head').append('<style id="cp-preview-accent">:root { --cp-color-accent: ' + newval + ' !important; }</style>');
        });
    });

    // Background Color
    wp.customize('cp_color_background', function(value) {
        value.bind(function(newval) {
            $('body').css('background-color', newval);
        });
    });

    // Text Color
    wp.customize('cp_color_text', function(value) {
        value.bind(function(newval) {
            $('body').css('color', newval);
        });
    });

    // Heading Font
    wp.customize('cp_heading_font', function(value) {
        value.bind(function(newval) {
            $('h1, h2, h3, h4, h5, h6').css('font-family', newval + ', sans-serif');
        });
    });

    // Body Font
    wp.customize('cp_body_font', function(value) {
        value.bind(function(newval) {
            $('body').css('font-family', newval + ', sans-serif');
        });
    });

    // Container Width
    wp.customize('cp_container_width', function(value) {
        value.bind(function(newval) {
            $('head').find('#cp-preview-width').remove();
            $('head').append('<style id="cp-preview-width">.container, .wp-block-group__inner-container { max-width: ' + newval + 'px !important; }</style>');
        });
    });

    // Color Scheme (instant apply)
    wp.customize('cp_color_scheme', function(value) {
        value.bind(function(scheme) {
            // Trigger refresh to apply the full scheme
            wp.customize.previewer.refresh();
        });
    });

})(jQuery);
