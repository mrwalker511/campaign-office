(function (blocks, element, blockEditor) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;
    var useBlockProps = blockEditor.useBlockProps;

    blocks.registerBlockType('campaignpress/section-wrapper', {
        edit: function () {
            var blockProps = useBlockProps({ className: 'cp-section-editor' });
            return el('div', blockProps, el(InnerBlocks));
        },
        save: function () {
            return el(InnerBlocks.Content);
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor);
