/**
 * Heroicon Block
 *
 * Gutenberg block for inserting Heroicons with customizable properties
 *
 * @package CampaignPress
 * @since 2.1.0
 */

import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps, BlockControls, AlignmentToolbar } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, TextControl, Button, ToggleControl, ColorPicker } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import './editor.css';

// Import block metadata
import metadata from './block.json';

// Icon Picker Component
const IconPicker = ({ selectedIcon, onSelect, onClose }) => {
	const [search, setSearch] = useState('');
	const [style, setStyle] = useState('outline');
	const [category, setCategory] = useState('all');
	const [icons, setIcons] = useState([]);
	const [loading, setLoading] = useState(true);

	// Common icon categories
	const iconCategories = {
		all: __('All Icons', 'campaignpress'),
		arrows: __('Arrows & Navigation', 'campaignpress'),
		communication: __('Communication', 'campaignpress'),
		ui: __('User Interface', 'campaignpress'),
		files: __('Files & Documents', 'campaignpress'),
		media: __('Media', 'campaignpress'),
		social: __('Social', 'campaignpress'),
		status: __('Status & Alerts', 'campaignpress'),
		data: __('Data & Charts', 'campaignpress'),
	};

	// Popular campaign-related icons
	const campaignIcons = [
		'megaphone', 'heart', 'star', 'flag', 'users', 'user-group',
		'calendar', 'map-pin', 'envelope', 'phone', 'chat-bubble-left-right',
		'chart-bar', 'currency-dollar', 'hand-raised', 'hand-thumb-up',
		'bell', 'bookmark', 'share', 'globe-alt', 'building-office',
		'check-circle', 'x-circle', 'exclamation-triangle', 'information-circle',
		'arrow-right', 'arrow-left', 'chevron-down', 'chevron-up',
		'plus', 'minus', 'x-mark', 'check',
	];

	useEffect(() => {
		// Load icons (in a real implementation, this would be an AJAX call)
		setIcons(campaignIcons);
		setLoading(false);
	}, []);

	const filteredIcons = icons.filter(icon => {
		if (search && !icon.toLowerCase().includes(search.toLowerCase())) {
			return false;
		}
		return true;
	});

	return (
		<div className="cp-icon-picker-modal" onClick={onClose}>
			<div className="cp-icon-picker-content" onClick={(e) => e.stopPropagation()}>
				<div className="cp-icon-picker-header">
					<h2>{__('Choose an Icon', 'campaignpress')}</h2>
					<button className="cp-icon-picker-close" onClick={onClose}>×</button>
				</div>

				<div className="cp-icon-picker-search">
					<input
						type="text"
						placeholder={__('Search icons...', 'campaignpress')}
						value={search}
						onChange={(e) => setSearch(e.target.value)}
					/>
				</div>

				<div className="cp-icon-picker-filters">
					<select value={style} onChange={(e) => setStyle(e.target.value)}>
					<option value="outline">{__('Outline', 'campaignpress')}</option>
					<option value="solid">{__('Solid', 'campaignpress')}</option>
					<option value="mini">{__('Mini', 'campaignpress')}</option>
					</select>
				</div>

				<div className="cp-icon-picker-grid">
					{loading ? (
						<div className="cp-icon-picker-loading">{__('Loading icons...', 'campaignpress')}</div>
					) : (
						<div className="cp-icon-grid">
							{filteredIcons.map(icon => (
								<div
									key={icon}
									className={`cp-icon-grid-item ${selectedIcon === icon ? 'selected' : ''}`}
									onClick={() => onSelect(icon)}
								>
									<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
										<path strokeLinecap="round" strokeLinejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
									</svg>
									<span>{icon}</span>
								</div>
							))}
						</div>
					)}
				</div>

				<div className="cp-icon-picker-footer">
					<button onClick={onClose}>{__('Cancel', 'campaignpress')}</button>
					<button className="primary" onClick={() => { onSelect(selectedIcon); onClose(); }}>
						{__('Insert Icon', 'campaignpress')}
					</button>
				</div>
			</div>
		</div>
	);
};

// Register block
registerBlockType(metadata.name, {
	edit: ({ attributes, setAttributes }) => {
		const { icon, iconStyle, iconSize, customSize, iconColor, linkUrl, linkTarget, ariaLabel } = attributes;
		const [showPicker, setShowPicker] = useState(false);

		const blockProps = useBlockProps({
			style: {
				color: iconColor || 'inherit',
			},
		});

		// Size classes mapping
		const sizeMap = {
			sm: '18px',
			md: '24px',
			lg: '32px',
			xl: '48px',
			custom: customSize + 'px',
		};

		return (
			<>
				<BlockControls>
					<AlignmentToolbar
						value={attributes.align}
						onChange={(align) => setAttributes({ align })}
					/>
				</BlockControls>

				<InspectorControls>
					<PanelBody title={__('Icon Settings', 'campaignpress')} initialOpen={true}>
						<div style={{ marginBottom: '1rem' }}>
							<Button
								variant="secondary"
								onClick={() => setShowPicker(true)}
								style={{ width: '100%' }}
							>
								{__('Choose Icon', 'campaignpress')}
							</Button>
							<p style={{ marginTop: '0.5rem', fontSize: '0.875rem', color: '#666' }}>
								{__('Current:', 'campaignpress')} <strong>{icon}</strong>
							</p>
						</div>

						<SelectControl
							label={__('Icon Style', 'campaignpress')}
							value={iconStyle}
							options={[
							{ label: __('Outline', 'campaignpress'), value: 'outline' },
							{ label: __('Solid', 'campaignpress'), value: 'solid' },
							{ label: __('Mini (20px)', 'campaignpress'), value: 'mini' },
							{ label: __('Micro (16px)', 'campaignpress'), value: 'micro' },
							]}
							onChange={(value) => setAttributes({ iconStyle: value })}
						/>

						<SelectControl
							label={__('Icon Size', 'campaignpress')}
							value={iconSize}
							options={[
							{ label: __('Small (18px)', 'campaignpress'), value: 'sm' },
							{ label: __('Medium (24px)', 'campaignpress'), value: 'md' },
							{ label: __('Large (32px)', 'campaignpress'), value: 'lg' },
							{ label: __('Extra Large (48px)', 'campaignpress'), value: 'xl' },
							{ label: __('Custom', 'campaignpress'), value: 'custom' },
							]}
							onChange={(value) => setAttributes({ iconSize: value })}
						/>

						{iconSize === 'custom' && (
							<RangeControl
								label={__('Custom Size (px)', 'campaignpress')}
								value={customSize}
								onChange={(value) => setAttributes({ customSize: value })}
								min={12}
								max={200}
							/>
						)}

						<div style={{ marginBottom: '1rem' }}>
							<label style={{ display: 'block', marginBottom: '0.5rem', fontWeight: 500 }}>
								{__('Icon Color', 'campaignpress')}
							</label>
							<ColorPicker
								color={iconColor}
								onChangeComplete={(color) => setAttributes({ iconColor: color.hex })}
								disableAlpha
							/>
							{iconColor && (
								<Button
									isSmall
									variant="secondary"
									onClick={() => setAttributes({ iconColor: '' })}
									style={{ marginTop: '0.5rem' }}
								>
									{__('Reset Color', 'campaignpress')}
								</Button>
							)}
						</div>
					</PanelBody>

					<PanelBody title={__('Link Settings', 'campaignpress')} initialOpen={false}>
						<TextControl
							label={__('Link URL', 'campaignpress')}
							value={linkUrl}
							onChange={(value) => setAttributes({ linkUrl: value })}
							type="url"
							help={__('Make the icon clickable by adding a URL', 'campaignpress')}
						/>

						{linkUrl && (
							<ToggleControl
								label={__('Open in new tab', 'campaignpress')}
								checked={linkTarget === '_blank'}
								onChange={(value) => setAttributes({ linkTarget: value ? '_blank' : '_self' })}
							/>
						)}
					</PanelBody>

					<PanelBody title={__('Accessibility', 'campaignpress')} initialOpen={false}>
						<TextControl
							label={__('ARIA Label', 'campaignpress')}
							value={ariaLabel}
							onChange={(value) => setAttributes({ ariaLabel: value })}
							help={__('Describe the icon for screen readers', 'campaignpress')}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						fill={iconStyle === 'solid' ? 'currentColor' : 'none'}
						viewBox="0 0 24 24"
						strokeWidth={1.5}
						stroke={iconStyle === 'solid' ? 'none' : 'currentColor'}
						style={{
							width: sizeMap[iconSize],
							height: sizeMap[iconSize],
							color: iconColor || 'inherit',
						}}
					>
						{/* Star icon as placeholder */}
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"
						/>
					</svg>
					<div style={{ fontSize: '0.7rem', marginTop: '0.25rem', color: '#666' }}>
						{icon}
					</div>
				</div>

				{showPicker && (
					<IconPicker
						selectedIcon={icon}
						onSelect={(newIcon) => setAttributes({ icon: newIcon })}
						onClose={() => setShowPicker(false)}
					/>
				)}
			</>
		);
	},

	save: () => {
		// Rendered server-side
		return null;
	},
});
