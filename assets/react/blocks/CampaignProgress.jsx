/**
 * Campaign Progress Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

registerBlockType('campaignpress/campaign-progress', {
    title: __('Campaign Progress', 'campaignpress'),
    category: 'campaignpress',
    icon: 'chart-bar',
    attributes: {
        goalAmount: {
            type: 'number',
            default: 10000,
        },
        raisedAmount: {
            type: 'number',
            default: 0,
        },
        title: {
            type: 'string',
            default: __('Campaign Progress', 'campaignpress'),
        },
        showPercentage: {
            type: 'boolean',
            default: true,
        },
    },
    edit: function (props) {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <ServerSideRender
                    block="campaignpress/campaign-progress"
                    attributes={props.attributes}
                />
            </div>
        );
    },
    save: () => null,
});
