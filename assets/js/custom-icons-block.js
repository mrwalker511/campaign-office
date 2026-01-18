/**
 * Custom Icons Block JavaScript
 *
 * Handles the custom icons block editor interface
 * Uses createElement instead of JSX for WordPress compatibility without build step
 */

( function( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var TextControl = wp.components.TextControl;
    var __ = wp.i18n.__;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var createElement = wp.element.createElement;

    /**
     * Register Custom Icons Block
     */
    registerBlockType( 'campaignpress/custom-icon', {
        edit: function( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var iconName = attributes.iconName;
            var size = attributes.size;
            var className = attributes.className;
            var ariaLabel = attributes.ariaLabel;
            var blockProps = useBlockProps();

            var iconState = useState( [] );
            var availableIcons = iconState[0];
            var setAvailableIcons = iconState[1];

            var loadingState = useState( true );
            var isLoading = loadingState[0];
            var setIsLoading = loadingState[1];

            // Load available custom icons
            useEffect( function() {
                // Check if cpIconsBrowser is available
                if ( typeof cpIconsBrowser === 'undefined' ) {
                    console.warn( 'Custom Icons Block: cpIconsBrowser not available' );
                    setIsLoading( false );
                    return;
                }

                var formData = new FormData();
                formData.append( 'action', 'campaignpress_get_custom_icons' );
                formData.append( 'nonce', cpIconsBrowser.nonce );

                fetch( cpIconsBrowser.ajaxUrl, {
                    method: 'POST',
                    body: formData
                } )
                    .then( function( response ) { return response.json(); } )
                    .then( function( data ) {
                        if ( data.success ) {
                            setAvailableIcons( data.data );
                        }
                        setIsLoading( false );
                    } )
                    .catch( function( error ) {
                        console.error( 'Error loading custom icons:', error );
                        setIsLoading( false );
                    } );
            }, [] );

            // Build icon options for SelectControl
            var iconOptions = [ { label: __( 'Choose an icon...', 'campaignpress' ), value: '' } ];
            availableIcons.forEach( function( icon ) {
                iconOptions.push( {
                    label: icon.replace( /-/g, ' ' ).replace( /\b\w/g, function( l ) { return l.toUpperCase(); } ),
                    value: icon
                } );
            } );

            // Build size options
            var sizeOptions = [
                { label: __( 'Small (16px)', 'campaignpress' ), value: 'sm' },
                { label: __( 'Medium (24px)', 'campaignpress' ), value: 'md' },
                { label: __( 'Large (32px)', 'campaignpress' ), value: 'lg' },
                { label: __( 'Extra Large (48px)', 'campaignpress' ), value: 'xl' }
            ];

            // Inspector controls
            var inspectorContent;
            if ( isLoading ) {
                inspectorContent = createElement( 'p', null, __( 'Loading icons...', 'campaignpress' ) );
            } else {
                inspectorContent = createElement( SelectControl, {
                    label: __( 'Select Icon', 'campaignpress' ),
                    value: iconName,
                    options: iconOptions,
                    onChange: function( value ) { setAttributes( { iconName: value } ); }
                } );
            }

            var inspectorControls = createElement( InspectorControls, null,
                createElement( PanelBody, { title: __( 'Icon Settings', 'campaignpress' ) },
                    inspectorContent,
                    createElement( SelectControl, {
                        label: __( 'Size', 'campaignpress' ),
                        value: size,
                        options: sizeOptions,
                        onChange: function( value ) { setAttributes( { size: value } ); }
                    } ),
                    createElement( TextControl, {
                        label: __( 'CSS Class', 'campaignpress' ),
                        value: className || '',
                        onChange: function( value ) { setAttributes( { className: value } ); },
                        placeholder: __( 'custom-icon-lg', 'campaignpress' )
                    } ),
                    createElement( TextControl, {
                        label: __( 'ARIA Label', 'campaignpress' ),
                        value: ariaLabel || '',
                        onChange: function( value ) { setAttributes( { ariaLabel: value } ); },
                        placeholder: __( 'Campaign Information', 'campaignpress' )
                    } )
                )
            );

            // If no icon selected, show selector grid
            if ( ! iconName ) {
                var gridItems = availableIcons.map( function( icon ) {
                    return createElement( 'div', {
                        key: icon,
                        className: 'custom-icon-item' + ( iconName === icon ? ' selected' : '' ),
                        onClick: function() { setAttributes( { iconName: icon } ); }
                    },
                        createElement( 'span', { className: 'icon-name' }, icon.replace( /-/g, ' ' ) )
                    );
                } );

                return createElement( 'div', blockProps,
                    inspectorControls,
                    createElement( 'div', { className: 'custom-icon-placeholder' },
                        createElement( 'p', { style: { marginBottom: '1rem', color: '#666' } },
                            __( 'Select an icon from the sidebar or click below:', 'campaignpress' )
                        ),
                        createElement( 'div', { className: 'custom-icon-grid' }, gridItems )
                    )
                );
            }

            // Show icon preview with controls
            return createElement( 'div', blockProps,
                inspectorControls,
                createElement( 'div', { className: 'custom-icon-preview' },
                    createElement( 'div', { className: 'custom-icon-display' },
                        createElement( 'span', { className: 'custom-icon-label' },
                            __( 'Icon Preview:', 'campaignpress' ) + ' ' + iconName.replace( /-/g, ' ' )
                        )
                    )
                )
            );
        },

        save: function() {
            // Server-side rendered
            return null;
        }
    } );

} )( window.wp );
