/**
 * CampaignPress Editor Overrides
 * Implements "Elementor-like" behavior: Template Library, Style Inline Panel.
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
            { name: 'Hero (Political)', slug: 'campaignpress/hero-political', icon: 'superhero' },
            { name: 'Donation Tiers', slug: 'campaignpress/donation-tiers', icon: 'money' },
            { name: 'Event Teaser', slug: 'campaignpress/event-teaser', icon: 'calendar' },
            { name: 'Policy Grid', slug: 'campaignpress/policy-grid', icon: 'grid-view' },
            { name: 'Volunteer Form', slug: 'campaignpress/volunteer-form', icon: 'groups' }
        ];

        var insertPattern = function (slug) {
            // In a real app, this would fetch the pattern content.
            // For now, we simulate inserting a block representation.
            var block = createBlock('core/pattern', { slug: slug });
            dispatch.insertBlocks(block);
            setIsOpen(false);
        };

        return el(Fragment, {},
            el(PluginSidebarMoreMenuItem, {
                icon: 'layout',
                onClick: function () { setIsOpen(true); }
            }, 'Political Studio Templates'),
            isOpen && el(Modal, {
                title: 'Political Studio Library',
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
            var [padding, setPadding] = wp.element.useState(0);

            // Only show on specific blocks (e.g., Groups) to avoid clutter
            if (props.name !== 'core/group' && props.name !== 'core/columns') {
                return el(BlockEdit, props);
            }

            return el(Fragment, {},
                el(BlockEdit, props),
                el(BlockControls, {},
                    el(ToolbarGroup, {},
                        el(ToolbarButton, {
                            icon: 'art', // Paintbrush icon
                            label: 'Quick Style',
                            onClick: function () { setIsVisible(!isVisible); },
                            isActive: isVisible
                        })
                    )
                ),
                isVisible && el(Popover, {
                    className: 'cp-style-popover',
                    onClose: function () { setIsVisible(false); }
                },
                    el('div', { className: 'cp-style-control-group' },
                        el('span', { className: 'cp-style-label' }, 'Padding (px)'),
                        el(RangeControl, {
                            value: padding,
                            onChange: function (val) {
                                setPadding(val);
                                // Apply to block attributes (simulated)
                                props.setAttributes({ style: { spacing: { padding: { top: val + 'px', bottom: val + 'px', left: val + 'px', right: val + 'px' } } } });
                            },
                            min: 0,
                            max: 100
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
