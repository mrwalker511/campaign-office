/**
 * Donation Button Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

registerBlockType('campaignpress/donation-button', {
    title: __('Donation Button', 'campaign-office'),
    category: 'campaignpress',
    icon: 'money',
    attributes: {
        buttonText: {
            type: 'string',
            default: __('Donate Now', 'campaign-office'),
        },
        donationUrl: {
            type: 'string',
            default: '',
        },
        buttonStyle: {
            type: 'string',
            default: 'primary',
        },
        alignment: {
            type: 'string',
            default: 'left',
        },
    },
    edit: function (props) {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <ServerSideRender
                    block="campaignpress/donation-button"
                    attributes={props.attributes}
                />
            </div>
        );
    },
    save: () => null,
});
