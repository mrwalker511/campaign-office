/**
 * Volunteer CTA Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

registerBlockType('campaignpress/volunteer-cta', {
    title: __('Volunteer CTA', 'campaign-office'),
    category: 'campaignpress',
    icon: 'groups',
    attributes: {
        title: {
            type: 'string',
            default: __('Join Our Campaign', 'campaign-office'),
        },
        description: {
            type: 'string',
            default: '',
        },
        buttonText: {
            type: 'string',
            default: __('Sign Up to Volunteer', 'campaign-office'),
        },
        buttonUrl: {
            type: 'string',
            default: '',
        },
    },
    edit: function (props) {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <ServerSideRender
                    block="campaignpress/volunteer-cta"
                    attributes={props.attributes}
                />
            </div>
        );
    },
    save: () => null,
});
