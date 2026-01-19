/**
 * Heroicon Block
 *
 * Gutenberg block for inserting Heroicons with customizable properties
 * Uses createElement for WordPress compatibility without build step
 *
 * @package CampaignPress
 * @since 2.1.0
 */

( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var BlockControls = wp.blockEditor.BlockControls;
    var AlignmentToolbar = wp.blockEditor.AlignmentToolbar;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var RangeControl = wp.components.RangeControl;
    var TextControl = wp.components.TextControl;
    var Button = wp.components.Button;
    var ToggleControl = wp.components.ToggleControl;
    var ColorPicker = wp.components.ColorPicker;
    var __ = wp.i18n.__;
    var useState = wp.element.useState;
    // eslint-disable-next-line no-unused-vars
    var useEffect = wp.element.useEffect;
    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;

    // Popular campaign-related icons
    var campaignIcons = [
        'megaphone', 'heart', 'star', 'flag', 'users', 'user-group',
        'calendar', 'map-pin', 'envelope', 'phone', 'chat-bubble-left-right',
        'chart-bar', 'currency-dollar', 'hand-raised', 'hand-thumb-up',
        'bell', 'bookmark', 'share', 'globe-alt', 'building-office',
        'check-circle', 'x-circle', 'exclamation-triangle', 'information-circle',
        'arrow-right', 'arrow-left', 'chevron-down', 'chevron-up',
        'plus', 'minus', 'x-mark', 'check'
    ];

    // Size mapping
    var sizeMap = {
        sm: '18px',
        md: '24px',
        lg: '32px',
        xl: '48px'
    };

    // Icon Picker Component
    function IconPicker( props ) {
        var selectedIcon = props.selectedIcon;
        var onSelect = props.onSelect;
        var onClose = props.onClose;

        var searchState = useState( '' );
        var search = searchState[0];
        var setSearch = searchState[1];

        var styleState = useState( 'outline' );
        var _style = styleState[0];
        var _setStyle = styleState[1];

        var filteredIcons = campaignIcons.filter( function( icon ) {
            if ( search && icon.toLowerCase().indexOf( search.toLowerCase() ) === -1 ) {
                return false;
            }
            return true;
        } );

        var iconGrid = filteredIcons.map( function( icon ) {
            return createElement( 'div', {
                key: icon,
                className: 'cp-icon-grid-item' + ( selectedIcon === icon ? ' selected' : '' ),
                onClick: function() { onSelect( icon ); },
                style: {
                    padding: '12px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    cursor: 'pointer',
                    textAlign: 'center',
                    backgroundColor: selectedIcon === icon ? '#007cba' : '#fff',
                    color: selectedIcon === icon ? '#fff' : '#1e1e1e'
                }
            },
                createElement( 'svg', {
                    xmlns: 'http://www.w3.org/2000/svg',
                    fill: 'none',
                    viewBox: '0 0 24 24',
                    strokeWidth: 1.5,
                    stroke: 'currentColor',
                    style: { width: '24px', height: '24px', margin: '0 auto 4px' }
                },
                    createElement( 'path', {
                        strokeLinecap: 'round',
                        strokeLinejoin: 'round',
                        d: 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'
                    } )
                ),
                createElement( 'span', { style: { fontSize: '11px', display: 'block' } }, icon )
            );
        } );

        return createElement( 'div', {
            className: 'cp-icon-picker-modal',
            onClick: onClose,
            style: {
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                backgroundColor: 'rgba(0,0,0,0.5)',
                zIndex: 100000,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center'
            }
        },
            createElement( 'div', {
                className: 'cp-icon-picker-content',
                onClick: function( e ) { e.stopPropagation(); },
                style: {
                    backgroundColor: '#fff',
                    borderRadius: '8px',
                    padding: '20px',
                    maxWidth: '600px',
                    maxHeight: '80vh',
                    overflow: 'auto',
                    width: '90%'
                }
            },
                createElement( 'div', {
                    style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }
                },
                    createElement( 'h2', { style: { margin: 0 } }, __( 'Choose an Icon', 'campaignpress' ) ),
                    createElement( 'button', {
                        onClick: onClose,
                        style: { background: 'none', border: 'none', fontSize: '24px', cursor: 'pointer' }
                    }, '\u00D7' )
                ),
                createElement( 'input', {
                    type: 'text',
                    placeholder: __( 'Search icons...', 'campaignpress' ),
                    value: search,
                    onChange: function( e ) { setSearch( e.target.value ); },
                    style: { width: '100%', padding: '8px', marginBottom: '16px', border: '1px solid #ddd', borderRadius: '4px' }
                } ),
                createElement( 'div', {
                    style: {
                        display: 'grid',
                        gridTemplateColumns: 'repeat(auto-fill, minmax(80px, 1fr))',
                        gap: '8px'
                    }
                }, iconGrid ),
                createElement( 'div', { style: { marginTop: '16px', textAlign: 'right' } },
                    createElement( Button, { onClick: onClose, variant: 'secondary' }, __( 'Cancel', 'campaignpress' ) ),
                    createElement( Button, {
                        onClick: function() { onSelect( selectedIcon ); onClose(); },
                        variant: 'primary',
                        style: { marginLeft: '8px' }
                    }, __( 'Insert Icon', 'campaignpress' ) )
                )
            )
        );
    }

    // Register block
    registerBlockType( 'campaignpress/icon', {
        edit: function Edit( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var icon = attributes.icon;
            var iconStyle = attributes.iconStyle;
            var iconSize = attributes.iconSize;
            var customSize = attributes.customSize;
            var iconColor = attributes.iconColor;
            var linkUrl = attributes.linkUrl;
            var linkTarget = attributes.linkTarget;
            var ariaLabel = attributes.ariaLabel;

            var pickerState = useState( false );
            var showPicker = pickerState[0];
            var setShowPicker = pickerState[1];

            var blockProps = useBlockProps( {
                style: { color: iconColor || 'inherit' }
            } );

            var currentSize = iconSize === 'custom' ? customSize + 'px' : sizeMap[iconSize] || '24px';

            // Style options
            var styleOptions = [
                { label: __( 'Outline', 'campaignpress' ), value: 'outline' },
                { label: __( 'Solid', 'campaignpress' ), value: 'solid' },
                { label: __( 'Mini (20px)', 'campaignpress' ), value: 'mini' },
                { label: __( 'Micro (16px)', 'campaignpress' ), value: 'micro' }
            ];

            // Size options
            var sizeOptions = [
                { label: __( 'Small (18px)', 'campaignpress' ), value: 'sm' },
                { label: __( 'Medium (24px)', 'campaignpress' ), value: 'md' },
                { label: __( 'Large (32px)', 'campaignpress' ), value: 'lg' },
                { label: __( 'Extra Large (48px)', 'campaignpress' ), value: 'xl' },
                { label: __( 'Custom', 'campaignpress' ), value: 'custom' }
            ];

            return createElement( Fragment, null,
                createElement( BlockControls, null,
                    createElement( AlignmentToolbar, {
                        value: attributes.align,
                        onChange: function( align ) { setAttributes( { align: align } ); }
                    } )
                ),
                createElement( InspectorControls, null,
                    createElement( PanelBody, { title: __( 'Icon Settings', 'campaignpress' ), initialOpen: true },
                        createElement( 'div', { style: { marginBottom: '1rem' } },
                            createElement( Button, {
                                variant: 'secondary',
                                onClick: function() { setShowPicker( true ); },
                                style: { width: '100%' }
                            }, __( 'Choose Icon', 'campaignpress' ) ),
                            createElement( 'p', { style: { marginTop: '0.5rem', fontSize: '0.875rem', color: '#666' } },
                                __( 'Current:', 'campaignpress' ) + ' ',
                                createElement( 'strong', null, icon )
                            )
                        ),
                        createElement( SelectControl, {
                            label: __( 'Icon Style', 'campaignpress' ),
                            value: iconStyle,
                            options: styleOptions,
                            onChange: function( value ) { setAttributes( { iconStyle: value } ); }
                        } ),
                        createElement( SelectControl, {
                            label: __( 'Icon Size', 'campaignpress' ),
                            value: iconSize,
                            options: sizeOptions,
                            onChange: function( value ) { setAttributes( { iconSize: value } ); }
                        } ),
                        iconSize === 'custom' && createElement( RangeControl, {
                            label: __( 'Custom Size (px)', 'campaignpress' ),
                            value: customSize,
                            onChange: function( value ) { setAttributes( { customSize: value } ); },
                            min: 12,
                            max: 200
                        } ),
                        createElement( 'div', { style: { marginBottom: '1rem' } },
                            createElement( 'label', { style: { display: 'block', marginBottom: '0.5rem', fontWeight: 500 } },
                                __( 'Icon Color', 'campaignpress' )
                            ),
                            createElement( ColorPicker, {
                                color: iconColor,
                                onChangeComplete: function( color ) { setAttributes( { iconColor: color.hex } ); },
                                disableAlpha: true
                            } ),
                            iconColor && createElement( Button, {
                                isSmall: true,
                                variant: 'secondary',
                                onClick: function() { setAttributes( { iconColor: '' } ); },
                                style: { marginTop: '0.5rem' }
                            }, __( 'Reset Color', 'campaignpress' ) )
                        )
                    ),
                    createElement( PanelBody, { title: __( 'Link Settings', 'campaignpress' ), initialOpen: false },
                        createElement( TextControl, {
                            label: __( 'Link URL', 'campaignpress' ),
                            value: linkUrl,
                            onChange: function( value ) { setAttributes( { linkUrl: value } ); },
                            type: 'url',
                            help: __( 'Make the icon clickable by adding a URL', 'campaignpress' )
                        } ),
                        linkUrl && createElement( ToggleControl, {
                            label: __( 'Open in new tab', 'campaignpress' ),
                            checked: linkTarget === '_blank',
                            onChange: function( value ) { setAttributes( { linkTarget: value ? '_blank' : '_self' } ); }
                        } )
                    ),
                    createElement( PanelBody, { title: __( 'Accessibility', 'campaignpress' ), initialOpen: false },
                        createElement( TextControl, {
                            label: __( 'ARIA Label', 'campaignpress' ),
                            value: ariaLabel,
                            onChange: function( value ) { setAttributes( { ariaLabel: value } ); },
                            help: __( 'Describe the icon for screen readers', 'campaignpress' )
                        } )
                    )
                ),
                createElement( 'div', blockProps,
                    createElement( 'svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        fill: iconStyle === 'solid' ? 'currentColor' : 'none',
                        viewBox: '0 0 24 24',
                        strokeWidth: 1.5,
                        stroke: iconStyle === 'solid' ? 'none' : 'currentColor',
                        style: {
                            width: currentSize,
                            height: currentSize,
                            color: iconColor || 'inherit'
                        }
                    },
                        createElement( 'path', {
                            strokeLinecap: 'round',
                            strokeLinejoin: 'round',
                            d: 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'
                        } )
                    ),
                    createElement( 'div', { style: { fontSize: '0.7rem', marginTop: '0.25rem', color: '#666' } }, icon )
                ),
                showPicker && createElement( IconPicker, {
                    selectedIcon: icon,
                    onSelect: function( newIcon ) { setAttributes( { icon: newIcon } ); },
                    onClose: function() { setShowPicker( false ); }
                } )
            );
        },

        save: function() {
            // Rendered server-side
            return null;
        }
    } );

} )( window.wp );
