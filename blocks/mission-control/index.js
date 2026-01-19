/**
 * Mission Control Block - Gutenberg Editor Component
 * WordPress Dependencies: blocks, element, blockEditor, components
 */

(function (wp) {
    const registerBlockType = wp.blocks.registerBlockType;
    const ServerSideRender = wp.serverSideRender;
    const PanelBody = wp.components.PanelBody;
    const TextControl = wp.components.TextControl;
    const Placeholder = wp.components.Placeholder;
    const InspectorControls = wp.blockEditor.InspectorControls;
    const __ = wp.i18n.__;

    registerBlockType('campaignpress/mission-control', {
        title: __('Mission Control Center', 'campaignpress'),
        description: __('Central dashboard with election countdown, weather, and momentum metrics.', 'campaignpress'),
        icon: 'dashboard',
        category: 'campaignpress',
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        attributes: {
            electionDate: {
                type: 'string',
                default: ''
            },
            locationCity: {
                type: 'string',
                default: 'Washington DC'
            }
        },
        edit: function Edit(props) {
            const { attributes, setAttributes } = props;
            const { electionDate, locationCity } = attributes;

            const inspectorControls = (
                InspectorControls,
                null,
                wp.element.createElement(PanelBody, {
                    title: __('Mission Control Settings', 'campaignpress'),
                    initialOpen: true
                },
                    wp.element.createElement(TextControl, {
                        label: __('Election Date', 'campaignpress'),
                        help: __('Enter election date (YYYY-MM-DD format)', 'campaignpress'),
                        value: electionDate,
                        onChange: (value) => setAttributes({ electionDate: value }),
                        placeholder: '2024-11-05'
                    }),
                    wp.element.createElement(TextControl, {
                        label: __('Location City', 'campaignpress'),
                        help: __('Enter city name for weather display', 'campaignpress'),
                        value: locationCity,
                        onChange: (value) => setAttributes({ locationCity: value }),
                        placeholder: 'Washington DC'
                    })
                )
            );

            const blockContent = wp.element.createElement(ServerSideRender, {
                block: 'campaignpress/mission-control',
                attributes: attributes
            });

            return wp.element.createElement(
                'div',
                null,
                inspectorControls,
                blockContent
            );
        },
        save: function () {
            return null;
        }
    });
})(window.wp);