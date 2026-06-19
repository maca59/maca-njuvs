/**
 * maca News block – editor
 */
(function () {
    var __ = wp.i18n.__;
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var ServerSideRender = wp.serverSideRender;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var RangeControl = wp.components.RangeControl;
    var ToggleControl = wp.components.ToggleControl;
    var SelectControl = wp.components.SelectControl;

    registerBlockType('maca-njuvs/maca-info-news', {
        title: __('maca News', 'maca-njuvs'),
        description: __('Show published news from maca Njuvs.', 'maca-njuvs'),
        category: 'maca-njuvs',
        icon: 'megaphone',
        supports: {
            html: false
        },
        attributes: {
            limit: { type: 'number', default: 5 },
            layout: { type: 'string', default: 'list' },
            bannerScroll: { type: 'boolean', default: true },
            showImage: { type: 'boolean', default: true },
            showDate: { type: 'boolean', default: true },
            showExcerpt: { type: 'boolean', default: true }
        },
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var layout = attributes.layout || 'list';
            var isList = layout === 'list';
            var isBanner = layout === 'banner';
            var isEmbedded = layout === 'embedded';
            var isSidebar = layout === 'sidebar-left' || layout === 'sidebar-right';

            return el(
                Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        {
                            title: __('News settings', 'maca-njuvs'),
                            initialOpen: true
                        },
                        el(SelectControl, {
                            label: __('Layout', 'maca-njuvs'),
                            value: layout,
                            options: [
                                { label: __('List', 'maca-njuvs'), value: 'list' },
                                { label: __('In page (table/column)', 'maca-njuvs'), value: 'embedded' },
                                { label: __('Fixed panel left', 'maca-njuvs'), value: 'sidebar-left' },
                                { label: __('Fixed panel right', 'maca-njuvs'), value: 'sidebar-right' },
                                { label: __('Top banner', 'maca-njuvs'), value: 'banner' }
                            ],
                            onChange: function (value) {
                                setAttributes({ layout: value });
                            },
                            help: layout === 'embedded'
                                ? __('Stays where you place the block — for tables and columns. Scrolls with the page. Click a headline to read more.', 'maca-njuvs')
                                : isSidebar
                                ? __('Fixed on the side while scrolling on desktop. Click a news item to read the full text in a popup. On mobile it appears last on the page.', 'maca-njuvs')
                                : ''
                        }),
                        el(RangeControl, {
                            label: isBanner
                                ? __('Number of news in banner', 'maca-njuvs')
                                : __('Number of items', 'maca-njuvs'),
                            value: attributes.limit || 5,
                            onChange: function (value) {
                                setAttributes({ limit: value });
                            },
                            min: 1,
                            max: 20
                        }),
                        isBanner ? el(ToggleControl, {
                            label: __('Scrolling ticker', 'maca-njuvs'),
                            checked: !!attributes.bannerScroll,
                            onChange: function (value) {
                                setAttributes({ bannerScroll: value });
                            },
                            help: __('Continuous horizontal scroll. Without this, swipe or scroll sideways when there are several news items.', 'maca-njuvs')
                        }) : null,
                        isList ? el(ToggleControl, {
                            label: __('Show image', 'maca-njuvs'),
                            checked: attributes.showImage !== false,
                            onChange: function (value) {
                                setAttributes({ showImage: value });
                            }
                        }) : null,
                        el(ToggleControl, {
                            label: __('Show date', 'maca-njuvs'),
                            checked: attributes.showDate !== false,
                            onChange: function (value) {
                                setAttributes({ showDate: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show excerpt', 'maca-njuvs'),
                            checked: attributes.showExcerpt !== false,
                            onChange: function (value) {
                                setAttributes({ showExcerpt: value });
                            }
                        })
                    )
                ),
                el(
                    'div',
                    { className: 'maca-njuvs-block-editor' },
                    el(ServerSideRender, {
                        block: 'maca-njuvs/maca-info-news',
                        attributes: attributes
                    }),
                    el(
                        'p',
                        { className: 'maca-njuvs-block-editor-hint description' },
                        __('Select this block and open “News settings” in the right sidebar to change layout.', 'maca-njuvs')
                    )
                )
            );
        },
        save: function () {
            return null;
        }
    });
})();
