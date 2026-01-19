/**
 * Volunteer CTA Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

registerBlockType('campaignpress/volunteer-cta', {
    title: __('Volunteer CTA', 'campaignpress'),
    category: 'campaignpress',
    icon: 'groups',
    attributes: {
        title: {
            type: 'string',
            default: __('Join Our Campaign', 'campaignpress'),
        },
        description: {
            type: 'string',
            default: '',
        },
        buttonText: {
            type: 'string',
            default: __('Sign Up to Volunteer', 'campaignpress'),
        },
        buttonUrl: {
            type: 'string',
            default: '',
        },
    },
    edit: function Edit(props) {
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
