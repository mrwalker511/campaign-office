/**
 * Donation Form Block - Editor Component
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    InspectorControls,
    RichText,
    useBlockProps
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    ToggleControl,
    SelectControl,
    Button,
    BaseControl,
    ColorPalette,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';

registerBlockType('campaignpress/donation-form', {
    edit: ({ attributes, setAttributes }) => {
        const {
            heading, description, tiers, allowCustomAmount, minCustomAmount,
            maxCustomAmount, currencySymbol, allowRecurring, paymentProcessor,
            actblueUrl, enableCrypto, btcAddress, showGoal, goalAmount,
            currentAmount, primaryColor, backgroundColor, buttonText,
            showDisclaimer, disclaimerText
        } = attributes;

        const blockProps = useBlockProps({ style: { backgroundColor, padding: '2rem' } });

        const updateTier = (index, field, value) => {
            const newTiers = [...tiers];
            newTiers[index] = { ...newTiers[index], [field]: value };
            setAttributes({ tiers: newTiers });
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Donation Tiers" initialOpen={true}>
                        {tiers.map((tier, index) => (
                            <div key={index} style={{ marginBottom: '16px', padding: '12px', border: '1px solid #ddd' }}>
                                <NumberControl label="Amount" value={tier.amount} onChange={(v) => updateTier(index, 'amount', parseInt(v))} />
                                <TextControl label="Label" value={tier.label} onChange={(v) => updateTier(index, 'label', v)} />
                                <TextControl label="Description" value={tier.description} onChange={(v) => updateTier(index, 'description', v)} />
                                <ToggleControl label="Featured" checked={tier.featured} onChange={(v) => updateTier(index, 'featured', v)} />
                                <Button isDestructive onClick={() => setAttributes({ tiers: tiers.filter((_, i) => i !== index) })}>Remove</Button>
                            </div>
                        ))}
                        <Button isPrimary onClick={() => setAttributes({ tiers: [...tiers, { amount: 500, label: 'Major Donor', description: '', featured: false }] })}>Add Tier</Button>
                    </PanelBody>

                    <PanelBody title="Payment" initialOpen={false}>
                        <SelectControl label="Processor" value={paymentProcessor} options={[
                            { label: 'ActBlue', value: 'actblue' },
                            { label: 'Stripe', value: 'stripe' },
                            { label: 'PayPal', value: 'paypal' }
                        ]} onChange={(v) => setAttributes({ paymentProcessor: v })} />
                        {paymentProcessor === 'actblue' && <TextControl label="ActBlue URL" value={actblueUrl} onChange={(v) => setAttributes({ actblueUrl: v })} />}
                        <ToggleControl label="Allow Recurring" checked={allowRecurring} onChange={(v) => setAttributes({ allowRecurring: v })} />
                        <ToggleControl label="Custom Amount" checked={allowCustomAmount} onChange={(v) => setAttributes({ allowCustomAmount: v })} />
                        <ToggleControl label="Enable Crypto" checked={enableCrypto} onChange={(v) => setAttributes({ enableCrypto: v })} />
                        {enableCrypto && <TextControl label="BTC Address" value={btcAddress} onChange={(v) => setAttributes({ btcAddress: v })} />}
                    </PanelBody>

                    <PanelBody title="Goal" initialOpen={false}>
                        <ToggleControl label="Show Goal" checked={showGoal} onChange={(v) => setAttributes({ showGoal: v })} />
                        {showGoal && (
                            <>
                                <NumberControl label="Goal Amount" value={goalAmount} onChange={(v) => setAttributes({ goalAmount: parseInt(v) })} />
                                <NumberControl label="Current Amount" value={currentAmount} onChange={(v) => setAttributes({ currentAmount: parseInt(v) })} />
                            </>
                        )}
                    </PanelBody>

                    <PanelBody title="Styling" initialOpen={false}>
                        <BaseControl label="Primary Color"><ColorPalette value={primaryColor} onChange={(v) => setAttributes({ primaryColor: v })} /></BaseControl>
                        <BaseControl label="Background"><ColorPalette value={backgroundColor} onChange={(v) => setAttributes({ backgroundColor: v })} /></BaseControl>
                        <TextControl label="Button Text" value={buttonText} onChange={(v) => setAttributes({ buttonText: v })} />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <RichText tagName="h2" value={heading} onChange={(v) => setAttributes({ heading: v })} placeholder="Heading..." />
                    <RichText tagName="p" value={description} onChange={(v) => setAttributes({ description: v })} placeholder="Description..." />

                    {showGoal && (
                        <div style={{ margin: '1rem 0', padding: '1rem', backgroundColor: '#f5f5f5', borderRadius: '8px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.5rem' }}>
                                <span><strong>{currencySymbol}{currentAmount.toLocaleString()}</strong></span>
                                <span>Goal: {currencySymbol}{goalAmount.toLocaleString()}</span>
                            </div>
                            <div style={{ height: '8px', backgroundColor: '#ddd', borderRadius: '4px' }}>
                                <div style={{ height: '100%', width: `${Math.min((currentAmount / goalAmount) * 100, 100)}%`, backgroundColor: primaryColor }} />
                            </div>
                        </div>
                    )}

                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '1rem', margin: '1rem 0' }}>
                        {tiers.map((tier, i) => (
                            <div key={i} style={{ padding: '1.5rem', border: tier.featured ? `3px solid ${primaryColor}` : '2px solid #ddd', borderRadius: '8px', textAlign: 'center' }}>
                                <div style={{ fontSize: '2rem', fontWeight: 'bold', color: primaryColor }}>{currencySymbol}{tier.amount}</div>
                                <div style={{ fontWeight: 'bold', margin: '0.5rem 0' }}>{tier.label}</div>
                                {tier.description && <div style={{ fontSize: '0.875rem', color: '#666' }}>{tier.description}</div>}
                            </div>
                        ))}
                    </div>

                    <button style={{ width: '100%', padding: '1rem', backgroundColor: primaryColor, color: 'white', border: 'none', borderRadius: '8px', fontWeight: 'bold' }} disabled>
                        {buttonText}
                    </button>
                </div>
            </>
        );
    },
    save: () => null
});
