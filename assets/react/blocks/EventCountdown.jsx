/**
 * Event Countdown Block
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

registerBlockType('campaignpress/event-countdown', {
    title: __('Event Countdown', 'campaign-office'),
    category: 'campaignpress',
    icon: 'clock',
    attributes: {
        eventDate: {
            type: 'string',
            default: '',
        },
        eventTitle: {
            type: 'string',
            default: __('Election Day', 'campaign-office'),
        },
    },
    edit: function (props) {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <ServerSideRender
                    block="campaignpress/event-countdown"
                    attributes={props.attributes}
                />
            </div>
        );
    },
    save: () => null,
});
