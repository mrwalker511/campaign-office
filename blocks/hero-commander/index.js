/**
 * Hero Commander Block - Editor Component
 *
 * Production-ready Gutenberg block with full drag-and-drop functionality
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
    RichText,
    useBlockProps,
    BlockControls,
    AlignmentToolbar,
    __experimentalColorGradientControl as ColorGradientControl
} from '@wordpress/block-editor';
import {
    PanelBody,
    PanelRow,
    TextControl,
    ToggleControl,
    SelectControl,
    RangeControl,
    Button,
    ButtonGroup,
    BaseControl,
    __experimentalBoxControl as BoxControl,
    __experimentalUnitControl as UnitControl
} from '@wordpress/components';
import { useState } from '@wordpress/element';

/**
 * Register the Hero Commander block
 */
registerBlockType('campaignpress/hero-commander', {
    edit: EditComponent,
    save: () => null // Server-side rendering
});

/**
 * Edit Component - The drag-and-drop editor interface
 */
function EditComponent({ attributes, setAttributes }) {
    const {
        headline,
        subheadline,
        typewriterEnabled,
        typewriterTexts,
        typewriterSpeed,
        backgroundType,
        backgroundImage,
        backgroundVideo,
        backgroundColor,
        gradientColors,
        overlayEnabled,
        overlayColor,
        overlayOpacity,
        minHeight,
        textAlign,
        textColor,
        headlineSize,
        subheadlineSize,
        contentMaxWidth,
        primaryCTA,
        secondaryCTA,
        parallaxEnabled,
        animationStyle
    } = attributes;

    const blockProps = useBlockProps({
        className: 'hero-commander-editor',
        style: {
            minHeight: minHeight,
            textAlign: textAlign,
            color: textColor
        }
    });

    // Helper to update CTA objects
    const updateCTA = (ctaType, field, value) => {
        const cta = ctaType === 'primary' ? primaryCTA : secondaryCTA;
        const updatedCTA = { ...cta, [field]: value };
        setAttributes({
            [ctaType === 'primary' ? 'primaryCTA' : 'secondaryCTA']: updatedCTA
        });
    };

    // Background style generator
    const getBackgroundStyle = () => {
        let style = {};

        switch (backgroundType) {
            case 'image':
                if (backgroundImage.url) {
                    style.backgroundImage = `url(${backgroundImage.url})`;
                    style.backgroundSize = 'cover';
                    style.backgroundPosition = 'center';
                }
                break;
            case 'video':
                style.backgroundColor = backgroundColor;
                break;
            case 'gradient':
                style.backgroundImage = `linear-gradient(135deg, ${gradientColors.start}, ${gradientColors.end})`;
                break;
            case 'color':
                style.backgroundColor = backgroundColor;
                break;
        }

        return style;
    };

    const overlayStyle = overlayEnabled ? {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: overlayColor,
        opacity: overlayOpacity,
        pointerEvents: 'none'
    } : {};

    return (
        <>
            {/* Block Toolbar */}
            <BlockControls>
                <AlignmentToolbar
                    value={textAlign}
                    onChange={(newAlign) => setAttributes({ textAlign: newAlign })}
                />
            </BlockControls>

            {/* Inspector Controls - Settings Sidebar */}
            <InspectorControls>
                {/* Background Settings */}
                <PanelBody title={__('Background', 'campaign-office')} initialOpen={true}>
                    <BaseControl label={__('Background Type', 'campaign-office')}>
                        <ButtonGroup>
                            <Button
                                isPressed={backgroundType === 'image'}
                                onClick={() => setAttributes({ backgroundType: 'image' })}
                            >
                                {__('Image', 'campaign-office')}
                            </Button>
                            <Button
                                isPressed={backgroundType === 'video'}
                                onClick={() => setAttributes({ backgroundType: 'video' })}
                            >
                                {__('Video', 'campaign-office')}
                            </Button>
                            <Button
                                isPressed={backgroundType === 'gradient'}
                                onClick={() => setAttributes({ backgroundType: 'gradient' })}
                            >
                                {__('Gradient', 'campaign-office')}
                            </Button>
                            <Button
                                isPressed={backgroundType === 'color'}
                                onClick={() => setAttributes({ backgroundType: 'color' })}
                            >
                                {__('Color', 'campaign-office')}
                            </Button>
                        </ButtonGroup>
                    </BaseControl>

                    {backgroundType === 'image' && (
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => setAttributes({
                                    backgroundImage: {
                                        url: media.url,
                                        id: media.id,
                                        alt: media.alt
                                    }
                                })}
                                allowedTypes={['image']}
                                value={backgroundImage.id}
                                render={({ open }) => (
                                    <div>
                                        <Button
                                            onClick={open}
                                            variant="secondary"
                                            style={{ marginTop: '8px', width: '100%' }}
                                        >
                                            {backgroundImage.url
                                                ? __('Change Background Image', 'campaign-office')
                                                : __('Select Background Image', 'campaign-office')
                                            }
                                        </Button>
                                        {backgroundImage.url && (
                                            <div style={{ marginTop: '8px' }}>
                                                <img
                                                    src={backgroundImage.url}
                                                    alt={backgroundImage.alt}
                                                    style={{ width: '100%', height: 'auto', borderRadius: '4px' }}
                                                />
                                                <Button
                                                    onClick={() => setAttributes({
                                                        backgroundImage: { url: '', id: 0, alt: '' }
                                                    })}
                                                    variant="tertiary"
                                                    isDestructive
                                                    style={{ marginTop: '8px' }}
                                                >
                                                    {__('Remove Image', 'campaign-office')}
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                )}
                            />
                        </MediaUploadCheck>
                    )}

                    {backgroundType === 'video' && (
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => setAttributes({
                                    backgroundVideo: {
                                        url: media.url,
                                        id: media.id
                                    }
                                })}
                                allowedTypes={['video']}
                                value={backgroundVideo.id}
                                render={({ open }) => (
                                    <div>
                                        <Button
                                            onClick={open}
                                            variant="secondary"
                                            style={{ marginTop: '8px', width: '100%' }}
                                        >
                                            {backgroundVideo.url
                                                ? __('Change Background Video', 'campaign-office')
                                                : __('Select Background Video', 'campaign-office')
                                            }
                                        </Button>
                                        {backgroundVideo.url && (
                                            <div style={{ marginTop: '8px' }}>
                                                <p><strong>{__('Video selected:', 'campaign-office')}</strong> {backgroundVideo.url.split('/').pop()}</p>
                                                <Button
                                                    onClick={() => setAttributes({
                                                        backgroundVideo: { url: '', id: 0 }
                                                    })}
                                                    variant="tertiary"
                                                    isDestructive
                                                >
                                                    {__('Remove Video', 'campaign-office')}
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                )}
                            />
                        </MediaUploadCheck>
                    )}

                    {backgroundType === 'color' && (
                        <ColorGradientControl
                            label={__('Background Color', 'campaign-office')}
                            colorValue={backgroundColor}
                            onColorChange={(color) => setAttributes({ backgroundColor: color })}
                            disableCustomGradients={true}
                            clearable={false}
                        />
                    )}

                    {backgroundType === 'gradient' && (
                        <>
                            <ColorGradientControl
                                label={__('Gradient Start Color', 'campaign-office')}
                                colorValue={gradientColors.start}
                                onColorChange={(color) => setAttributes({
                                    gradientColors: { ...gradientColors, start: color }
                                })}
                                disableCustomGradients={true}
                                clearable={false}
                            />
                            <ColorGradientControl
                                label={__('Gradient End Color', 'campaign-office')}
                                colorValue={gradientColors.end}
                                onColorChange={(color) => setAttributes({
                                    gradientColors: { ...gradientColors, end: color }
                                })}
                                disableCustomGradients={true}
                                clearable={false}
                            />
                        </>
                    )}
                </PanelBody>

                {/* Overlay Settings */}
                <PanelBody title={__('Overlay', 'campaign-office')} initialOpen={false}>
                    <ToggleControl
                        label={__('Enable Overlay', 'campaign-office')}
                        checked={overlayEnabled}
                        onChange={() => setAttributes({ overlayEnabled: !overlayEnabled })}
                    />

                    {overlayEnabled && (
                        <>
                            <ColorGradientControl
                                label={__('Overlay Color', 'campaign-office')}
                                colorValue={overlayColor}
                                onColorChange={(color) => setAttributes({ overlayColor: color })}
                                disableCustomGradients={true}
                                clearable={false}
                            />
                            <RangeControl
                                label={__('Overlay Opacity', 'campaign-office')}
                                value={overlayOpacity}
                                onChange={(value) => setAttributes({ overlayOpacity: value })}
                                min={0}
                                max={1}
                                step={0.05}
                            />
                        </>
                    )}
                </PanelBody>

                {/* Layout Settings */}
                <PanelBody title={__('Layout', 'campaign-office')} initialOpen={false}>
                    <UnitControl
                        label={__('Minimum Height', 'campaign-office')}
                        value={minHeight}
                        onChange={(value) => setAttributes({ minHeight: value })}
                        units={[
                            { value: 'px', label: 'px' },
                            { value: 'vh', label: 'vh' },
                            { value: 'rem', label: 'rem' }
                        ]}
                    />

                    <UnitControl
                        label={__('Content Max Width', 'campaign-office')}
                        value={contentMaxWidth}
                        onChange={(value) => setAttributes({ contentMaxWidth: value })}
                        units={[
                            { value: 'px', label: 'px' },
                            { value: '%', label: '%' },
                            { value: 'rem', label: 'rem' }
                        ]}
                    />
                </PanelBody>

                {/* Typography Settings */}
                <PanelBody title={__('Typography', 'campaign-office')} initialOpen={false}>
                    <ColorGradientControl
                        label={__('Text Color', 'campaign-office')}
                        colorValue={textColor}
                        onColorChange={(color) => setAttributes({ textColor: color })}
                        disableCustomGradients={true}
                        clearable={false}
                    />

                    <TextControl
                        label={__('Headline Font Size', 'campaign-office')}
                        value={headlineSize}
                        onChange={(value) => setAttributes({ headlineSize: value })}
                        help={__('Use CSS values like clamp(2rem, 5vw, 5rem)', 'campaign-office')}
                    />

                    <TextControl
                        label={__('Subheadline Font Size', 'campaign-office')}
                        value={subheadlineSize}
                        onChange={(value) => setAttributes({ subheadlineSize: value })}
                    />
                </PanelBody>

                {/* Typewriter Effect */}
                <PanelBody title={__('Typewriter Effect', 'campaign-office')} initialOpen={false}>
                    <ToggleControl
                        label={__('Enable Typewriter', 'campaign-office')}
                        checked={typewriterEnabled}
                        onChange={() => setAttributes({ typewriterEnabled: !typewriterEnabled })}
                    />

                    {typewriterEnabled && (
                        <>
                            <BaseControl
                                label={__('Typewriter Words', 'campaign-office')}
                                help={__('One word per line', 'campaign-office')}
                            >
                                <textarea
                                    value={typewriterTexts.join('\n')}
                                    onChange={(e) => setAttributes({
                                        typewriterTexts: e.target.value.split('\n').filter(t => t.trim())
                                    })}
                                    rows={4}
                                    style={{ width: '100%', padding: '8px' }}
                                />
                            </BaseControl>

                            <RangeControl
                                label={__('Typing Speed', 'campaign-office')}
                                value={typewriterSpeed}
                                onChange={(value) => setAttributes({ typewriterSpeed: value })}
                                min={50}
                                max={300}
                                step={10}
                                help={__('Lower is faster', 'campaign-office')}
                            />
                        </>
                    )}
                </PanelBody>

                {/* Call to Action Buttons */}
                <PanelBody title={__('Call to Action Buttons', 'campaign-office')} initialOpen={false}>
                    <h3>{__('Primary Button', 'campaign-office')}</h3>
                    <ToggleControl
                        label={__('Show Primary Button', 'campaign-office')}
                        checked={primaryCTA.enabled}
                        onChange={(value) => updateCTA('primary', 'enabled', value)}
                    />
                    {primaryCTA.enabled && (
                        <>
                            <TextControl
                                label={__('Button Text', 'campaign-office')}
                                value={primaryCTA.text}
                                onChange={(value) => updateCTA('primary', 'text', value)}
                            />
                            <TextControl
                                label={__('Button URL', 'campaign-office')}
                                value={primaryCTA.url}
                                onChange={(value) => updateCTA('primary', 'url', value)}
                                type="url"
                            />
                        </>
                    )}

                    <hr style={{ margin: '20px 0' }} />

                    <h3>{__('Secondary Button', 'campaign-office')}</h3>
                    <ToggleControl
                        label={__('Show Secondary Button', 'campaign-office')}
                        checked={secondaryCTA.enabled}
                        onChange={(value) => updateCTA('secondary', 'enabled', value)}
                    />
                    {secondaryCTA.enabled && (
                        <>
                            <TextControl
                                label={__('Button Text', 'campaign-office')}
                                value={secondaryCTA.text}
                                onChange={(value) => updateCTA('secondary', 'text', value)}
                            />
                            <TextControl
                                label={__('Button URL', 'campaign-office')}
                                value={secondaryCTA.url}
                                onChange={(value) => updateCTA('secondary', 'url', value)}
                                type="url"
                            />
                        </>
                    )}
                </PanelBody>

                {/* Advanced Settings */}
                <PanelBody title={__('Advanced', 'campaign-office')} initialOpen={false}>
                    <ToggleControl
                        label={__('Enable Parallax Effect', 'campaign-office')}
                        checked={parallaxEnabled}
                        onChange={() => setAttributes({ parallaxEnabled: !parallaxEnabled })}
                        help={__('Subtle background movement on scroll', 'campaign-office')}
                    />

                    <SelectControl
                        label={__('Animation Style', 'campaign-office')}
                        value={animationStyle}
                        options={[
                            { label: 'None', value: 'none' },
                            { label: 'Fade Up', value: 'fade-up' },
                            { label: 'Fade In', value: 'fade-in' },
                            { label: 'Slide Up', value: 'slide-up' },
                            { label: 'Zoom In', value: 'zoom-in' }
                        ]}
                        onChange={(value) => setAttributes({ animationStyle: value })}
                    />
                </PanelBody>
            </InspectorControls>

            {/* Block Preview in Editor */}
            <div {...blockProps}>
                <div className="hero-commander-background" style={getBackgroundStyle()}>
                    {backgroundType === 'video' && backgroundVideo.url && (
                        <video
                            autoPlay
                            muted
                            loop
                            playsInline
                            style={{
                                position: 'absolute',
                                top: 0,
                                left: 0,
                                width: '100%',
                                height: '100%',
                                objectFit: 'cover'
                            }}
                        >
                            <source src={backgroundVideo.url} type="video/mp4" />
                        </video>
                    )}

                    {overlayEnabled && <div style={overlayStyle} />}

                    <div
                        className="hero-commander-content"
                        style={{
                            position: 'relative',
                            zIndex: 10,
                            maxWidth: contentMaxWidth,
                            margin: '0 auto',
                            padding: '2rem'
                        }}
                    >
                        <RichText
                            tagName="h1"
                            value={headline}
                            onChange={(value) => setAttributes({ headline: value })}
                            placeholder={__('Enter your headline...', 'campaign-office')}
                            style={{
                                fontSize: headlineSize,
                                color: textColor,
                                marginBottom: '1rem'
                            }}
                        />

                        {typewriterEnabled && typewriterTexts.length > 0 && (
                            <p
                                style={{
                                    fontSize: headlineSize,
                                    color: textColor,
                                    fontWeight: 'bold',
                                    marginBottom: '1.5rem'
                                }}
                            >
                                {typewriterTexts[0]}
                                <span className="typewriter-cursor">|</span>
                            </p>
                        )}

                        <RichText
                            tagName="p"
                            value={subheadline}
                            onChange={(value) => setAttributes({ subheadline: value })}
                            placeholder={__('Enter your subheadline...', 'campaign-office')}
                            style={{
                                fontSize: subheadlineSize,
                                color: textColor,
                                marginBottom: '2rem'
                            }}
                        />

                        <div className="hero-commander-cta" style={{ display: 'flex', gap: '1rem', justifyContent: textAlign, flexWrap: 'wrap' }}>
                            {primaryCTA.enabled && (
                                <a
                                    href={primaryCTA.url}
                                    className="hero-cta-primary"
                                    style={{
                                        padding: '1rem 2rem',
                                        backgroundColor: '#ff8800',
                                        color: '#ffffff',
                                        textDecoration: 'none',
                                        borderRadius: '0.5rem',
                                        fontWeight: 'bold',
                                        display: 'inline-block'
                                    }}
                                    onClick={(e) => e.preventDefault()}
                                >
                                    {primaryCTA.text}
                                </a>
                            )}

                            {secondaryCTA.enabled && (
                                <a
                                    href={secondaryCTA.url}
                                    className="hero-cta-secondary"
                                    style={{
                                        padding: '1rem 2rem',
                                        backgroundColor: 'transparent',
                                        color: textColor,
                                        border: `2px solid ${textColor}`,
                                        textDecoration: 'none',
                                        borderRadius: '0.5rem',
                                        fontWeight: 'bold',
                                        display: 'inline-block'
                                    }}
                                    onClick={(e) => e.preventDefault()}
                                >
                                    {secondaryCTA.text}
                                </a>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
