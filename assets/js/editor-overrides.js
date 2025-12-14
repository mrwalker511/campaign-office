/**
 * CampaignPress Designer - Editor Overrides
 * Implements "CampaignPress Designer" behavior: Template Library, Style Inline Panel.
 */
(function (wp) {
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var Button = wp.components.Button;
    var Modal = wp.components.Modal;
    var RangeControl = wp.components.RangeControl;
    var ColorPalette = wp.components.ColorPalette;
    var Popover = wp.components.Popover;
    var ToolbarGroup = wp.components.ToolbarGroup;
    var ToolbarButton = wp.components.ToolbarButton;
    var BlockControls = wp.blockEditor.BlockControls;
    var withSelect = wp.data.withSelect;
    var withDispatch = wp.data.withDispatch;
    var compose = wp.compose.compose;
    var createBlock = wp.blocks.createBlock;

    /**
     * 1. Template Library Modal
     */
    var TemplateLibrary = wp.element.useState ? function () {
        var [isOpen, setIsOpen] = wp.element.useState(false);
        var dispatch = wp.data.dispatch('core/block-editor');

        var patterns = [
            { name: 'Campaign Hero', slug: 'campaignpress/hero-section', icon: 'superhero' },
            { name: 'Donation CTA', slug: 'campaignpress/donation-cta', icon: 'money' },
            { name: 'Event Highlight', slug: 'campaignpress/event-highlight', icon: 'calendar' },
            { name: 'Issue Card', slug: 'campaignpress/issue-card', icon: 'info' },
            { name: 'Team Grid', slug: 'campaignpress/team-grid', icon: 'groups' },
            { name: 'Newsletter Signup', slug: 'campaignpress/newsletter-signup', icon: 'email' },
            { name: 'Testimonial', slug: 'campaignpress/testimonial-card', icon: 'format-quote' }
        ];

        var insertPattern = function (slug) {
            // Insert the pattern block
            var block = createBlock('core/pattern', { slug: slug });
            dispatch.insertBlocks(block);
            setIsOpen(false);
        };

        return el(Fragment, {},
            el(PluginSidebarMoreMenuItem, {
                icon: 'layout',
                onClick: function () { setIsOpen(true); }
            }, 'CampaignPress Designer'),
            isOpen && el(Modal, {
                title: 'CampaignPress Designer Library',
                onRequestClose: function () { setIsOpen(false); },
                className: 'cp-template-library-modal'
            },
                el('div', { className: 'cp-template-grid' },
                    patterns.map(function (pattern) {
                        return el('div', {
                            className: 'cp-template-card',
                            onClick: function () { insertPattern(pattern.slug); }
                        },
                            el('div', { className: 'cp-template-preview' },
                                el('span', { className: 'dashicons dashicons-' + pattern.icon })
                            ),
                            el('div', { className: 'cp-template-name' }, pattern.name)
                        );
                    })
                )
            )
        );
    } : function () { return null; }; // Fallback for older WP

    registerPlugin('campaignpress-template-library', {
        render: TemplateLibrary
    });

    /**
     * 2. Inline Style Panel (Hook)
     * Adds a "Style" brush icon to block toolbars.
     */
    var withStylePanel = wp.compose.createHigherOrderComponent(function (BlockEdit) {
        return function (props) {
            var [isVisible, setIsVisible] = wp.element.useState(false);

            // State for controls
            var attributes = props.attributes || {};
            var style = attributes.style || {};
            var spacing = style.spacing || {};
            var border = style.border || {};

            // Padding
            var initialPadding = 0;
            if (spacing.padding && spacing.padding.top) {
                initialPadding = parseInt(spacing.padding.top);
            }
            var [padding, setPadding] = wp.element.useState(initialPadding);

            // Margin
            var initialMargin = 0;
            if (spacing.margin && spacing.margin.top) {
                initialMargin = parseInt(spacing.margin.top);
            }
            var [margin, setMargin] = wp.element.useState(initialMargin);

            // Radius
            var initialRadius = 0;
            if (border.radius) {
                initialRadius = parseInt(border.radius);
            }
            var [radius, setRadius] = wp.element.useState(initialRadius);

            // Background
            var initialBg = props.attributes.backgroundColor ? ('var(--wp--preset--color--' + props.attributes.backgroundColor + ')') : (style.color ? style.color.background : '');
            var [bgColor, setBgColor] = wp.element.useState(initialBg);


            // Only show on container-like blocks to act as "Sections"
            if (props.name !== 'core/group' && props.name !== 'core/columns' && props.name !== 'core/column' && props.name !== 'core/cover') {
                return el(BlockEdit, props);
            }

            var updateStyle = function(newStyle) {
                // Deep merge helper would be better, but we'll do simple merge for now
                var currentStyle = props.attributes.style || {};
                var mergedStyle = {
                    ...currentStyle,
                    ...newStyle,
                    spacing: { ...currentStyle.spacing, ...newStyle.spacing },
                    border: { ...currentStyle.border, ...newStyle.border },
                    color: { ...currentStyle.color, ...newStyle.color }
                };

                // Cleanup empty objects
                if (Object.keys(newStyle.spacing || {}).length > 0) mergedStyle.spacing = { ...currentStyle.spacing, ...newStyle.spacing };
                if (Object.keys(newStyle.border || {}).length > 0) mergedStyle.border = { ...currentStyle.border, ...newStyle.border };
                 if (Object.keys(newStyle.color || {}).length > 0) mergedStyle.color = { ...currentStyle.color, ...newStyle.color };

                props.setAttributes({ style: mergedStyle });
            };

            return el(Fragment, {},
                el(BlockEdit, props),
                el(BlockControls, {},
                    el(ToolbarGroup, {},
                        el(ToolbarButton, {
                            icon: 'art', // Paintbrush icon
                            label: 'Designer Controls',
                            onClick: function () { setIsVisible(!isVisible); },
                            isActive: isVisible
                        })
                    )
                ),
                isVisible && el(Popover, {
                    className: 'cp-style-popover',
                    onClose: function () { setIsVisible(false); },
                    position: 'bottom center'
                },
                    el('div', { className: 'cp-style-panel-header' }, 'Designer Controls'),

                    // Padding Control
                    el('div', { className: 'cp-style-control-group' },
                        el('span', { className: 'cp-style-label' }, 'Padding (px)'),
                        el(RangeControl, {
                            value: padding,
                            onChange: function (val) {
                                setPadding(val);
                                var valStr = val + 'px';
                                updateStyle({ spacing: { padding: { top: valStr, bottom: valStr, left: valStr, right: valStr } } });
                            },
                            min: 0,
                            max: 100
                        })
                    ),

                    // Margin Control
                    el('div', { className: 'cp-style-control-group' },
                        el('span', { className: 'cp-style-label' }, 'Margin (Vertical px)'),
                        el(RangeControl, {
                            value: margin,
                            onChange: function (val) {
                                setMargin(val);
                                var valStr = val + 'px';
                                updateStyle({ spacing: { margin: { top: valStr, bottom: valStr } } });
                            },
                            min: 0,
                            max: 100
                        })
                    ),

                    // Border Radius
                     el('div', { className: 'cp-style-control-group' },
                        el('span', { className: 'cp-style-label' }, 'Corner Radius (px)'),
                        el(RangeControl, {
                            value: radius,
                            onChange: function (val) {
                                setRadius(val);
                                updateStyle({ border: { radius: val + 'px' } });
                            },
                            min: 0,
                            max: 50
                        })
                    ),

                    // Background Color (Custom)
                    el('div', { className: 'cp-style-control-group' },
                        el('span', { className: 'cp-style-label' }, 'Custom Background'),
                        el(ColorPalette, {
                            value: bgColor,
                            onChange: function (val) {
                                setBgColor(val);
                                updateStyle({ color: { background: val } });
                                // Clear preset if custom is set
                                if (val) {
                                    props.setAttributes({ backgroundColor: undefined });
                                }
                            }
                        })
                    )
                )
            );
        };
    }, 'withStylePanel');

    wp.hooks.addFilter(
        'editor.BlockEdit',
        'campaignpress/style-panel',
        withStylePanel
    );

})(window.wp);
