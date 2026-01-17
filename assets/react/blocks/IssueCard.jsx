/**
 * Issue Card Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

registerBlockType('campaignpress/issue-card', {
    title: __('Issue Card', 'campaignpress'),
    category: 'campaignpress',
    icon: 'megaphone',
    attributes: {
        issueTitle: {
            type: 'string',
            default: '',
        },
        issueDescription: {
            type: 'string',
            default: '',
        },
        iconName: {
            type: 'string',
            default: 'megaphone',
        },
    },
    edit: function (props) {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <ServerSideRender
                    block="campaignpress/issue-card"
                    attributes={props.attributes}
                />
            </div>
        );
    },
    save: () => null,
});
