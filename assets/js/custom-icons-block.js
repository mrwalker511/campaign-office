/**
 * Custom Icons Block JavaScript
 *
 * Handles the custom icons block editor interface
 */

( function( wp ) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, TextControl, ToggleControl } = wp.components;
    const { __ } = wp.i18n;
    const { useState, useEffect } = wp.element;

    /**
     * Register Custom Icons Block
     */
    registerBlockType( 'campaignpress/custom-icon', {
        edit: function( props ) {
            const { attributes, setAttributes } = props;
            const { iconName, size, className, ariaLabel } = attributes;
            const blockProps = useBlockProps();

            const [ availableIcons, setAvailableIcons ] = useState( [] );
            const [ isLoading, setIsLoading ] = useState( true );

            // Load available custom icons
            useEffect( () => {
                const formData = new FormData();
                formData.append( 'action', 'campaignpress_get_custom_icons' );
                formData.append( 'nonce', cpIconsBrowser.nonce );

                fetch( cpIconsBrowser.ajaxUrl, {
                    method: 'POST',
                    body: formData
                } )
                    .then( response => response.json() )
                    .then( data => {
                        if ( data.success ) {
                            setAvailableIcons( data.data );
                        }
                        setIsLoading( false );
                    } )
                    .catch( error => {
                        console.error( 'Error loading custom icons:', error );
                        setIsLoading( false );
                    } );
            }, [] );

            // If no icon selected, show selector
            if ( ! iconName ) {
                return (
                    <div { ...blockProps }>
                        <InspectorControls>
                            <PanelBody title={ __( 'Icon Settings', 'campaignpress' ) }>
                                { isLoading ? (
                                    <p>{ __( 'Loading icons...', 'campaignpress' ) }</p>
                                ) : (
                                    <SelectControl
                                        label={ __( 'Select Icon', 'campaignpress' ) }
                                        value={ iconName }
                                        options={ [
                                            { label: __( 'Choose an icon...', 'campaignpress' ), value: '' },
                                            ...availableIcons.map( icon => ( {
                                                label: icon.replace( /-/g, ' ' ).replace( /\b\w/g, l => l.toUpperCase() ),
                                                value: icon
                                            } ) )
                                        ] }
                                        onChange={ ( value ) => setAttributes( { iconName: value } ) }
                                    />
                                ) }
                            </PanelBody>
                        </InspectorControls>

                        <div className="custom-icon-placeholder">
                            <div className="custom-icon-grid">
                                { availableIcons.map( icon => (
                                    <div
                                        key={ icon }
                                        className={ `custom-icon-item ${ iconName === icon ? 'selected' : '' }` }
                                        onClick={ () => setAttributes( { iconName: icon } ) }
                                    >
                                        {/* Icon will be rendered here by the server-side function */}
                                        <span className="icon-name">{ icon.replace( /-/g, ' ' ) }</span>
                                    </div>
                                ) ) }
                            </div>
                        </div>
                    </div>
                );
            }

            // Show icon with controls
            return (
                <div { ...blockProps }>
                    <InspectorControls>
                        <PanelBody title={ __( 'Icon Settings', 'campaignpress' ) }>
                            <SelectControl
                                label={ __( 'Select Icon', 'campaignpress' ) }
                                value={ iconName }
                                options={ [
                                    { label: __( 'Choose an icon...', 'campaignpress' ), value: '' },
                                    ...availableIcons.map( icon => ( {
                                        label: icon.replace( /-/g, ' ' ).replace( /\b\w/g, l => l.toUpperCase() ),
                                        value: icon
                                    } ) )
                                ] }
                                onChange={ ( value ) => setAttributes( { iconName: value } ) }
                            />

                            <SelectControl
                                label={ __( 'Size', 'campaignpress' ) }
                                value={ size }
                                options={ [
                                    { label: __( 'Small (16px)', 'campaignpress' ), value: 'sm' },
                                    { label: __( 'Medium (24px)', 'campaignpress' ), value: 'md' },
                                    { label: __( 'Large (32px)', 'campaignpress' ), value: 'lg' },
                                    { label: __( 'Extra Large (48px)', 'campaignpress' ), value: 'xl' },
                                ] }
                                onChange={ ( value ) => setAttributes( { size: value } ) }
                            />

                            <TextControl
                                label={ __( 'CSS Class', 'campaignpress' ) }
                                value={ className }
                                onChange={ ( value ) => setAttributes( { className: value } ) }
                                placeholder={ __( 'custom-icon-lg', 'campaignpress' ) }
                            />

                            <TextControl
                                label={ __( 'ARIA Label', 'campaignpress' ) }
                                value={ ariaLabel }
                                onChange={ ( value ) => setAttributes( { ariaLabel: value } ) }
                                placeholder={ __( 'Campaign Information', 'campaignpress' ) }
                            />
                        </PanelBody>
                    </InspectorControls>

                    <div className="custom-icon-preview">
                        <div className="custom-icon-display">
                            {/* Server-rendered icon will appear here */}
                            <span className="custom-icon-label">
                                { __( 'Icon Preview:', 'campaignpress' ) } { iconName.replace( /-/g, ' ' ) }
                            </span>
                        </div>
                    </div>
                </div>
            );
        },

        save: function() {
            // Server-side rendered
            return null;
        },
    } );

} )( window.wp );