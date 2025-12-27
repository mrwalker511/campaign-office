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
        title: __('Mission Control Center', 'campaign-office'),
        description: __('Central dashboard with election countdown, weather, and momentum metrics.', 'campaign-office'),
        icon: 'dashboard',
        category: 'campaign-office',
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
        edit: function (props) {
            const { attributes, setAttributes } = props;
            const { electionDate, locationCity } = attributes;

            const inspectorControls = (
                InspectorControls,
                null,
                wp.element.createElement(PanelBody, {
                    title: __('Mission Control Settings', 'campaign-office'),
                    initialOpen: true
                },
                    wp.element.createElement(TextControl, {
                        label: __('Election Date', 'campaign-office'),
                        help: __('Enter election date (YYYY-MM-DD format)', 'campaign-office'),
                        value: electionDate,
                        onChange: (value) => setAttributes({ electionDate: value }),
                        placeholder: '2024-11-05'
                    }),
                    wp.element.createElement(TextControl, {
                        label: __('Location City', 'campaign-office'),
                        help: __('Enter city name for weather display', 'campaign-office'),
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