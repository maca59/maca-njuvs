/**
 * maca Events block – editor
 */
(function () {
    var __ = wp.i18n.__;
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var ServerSideRender = wp.serverSideRender;
    var RangeControl = wp.components.RangeControl;
    var ToggleControl = wp.components.ToggleControl;
    var SelectControl = wp.components.SelectControl;

    registerBlockType('maca-njuvs/maca-info-events', {
        title: __('maca Events', 'maca-njuvs'),
        description: __('Show upcoming events from maca Njuvs.', 'maca-njuvs'),
        category: 'maca-njuvs',
        icon: 'calendar',
        supports: {
            html: false
        },
        attributes: {
            limit: { type: 'number', default: 10 },
            view: { type: 'string', default: 'list' },
            showImage: { type: 'boolean', default: true },
            showLocation: { type: 'boolean', default: true },
            mondayFirst: { type: 'boolean', default: true },
            showSubscribe: { type: 'boolean', default: true }
        },
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var isList = (attributes.view || 'list') === 'list';

            return el(
                'div',
                { className: 'maca-njuvs-block-editor' },
                el(ServerSideRender, {
                    block: 'maca-njuvs/maca-info-events',
                    attributes: attributes
                }),
                el(
                    'div',
                    { className: 'maca-njuvs-block-controls' },
                    el(SelectControl, {
                        label: __('View', 'maca-njuvs'),
                        value: attributes.view || 'list',
                        options: [
                            { label: __('List', 'maca-njuvs'), value: 'list' },
                            { label: __('Month calendar', 'maca-njuvs'), value: 'month' }
                        ],
                        onChange: function (value) {
                            setAttributes({ view: value });
                        }
                    }),
                    el(ToggleControl, {
                        label: __('Show calendar subscription', 'maca-njuvs'),
                        checked: attributes.showSubscribe !== false,
                        onChange: function (value) {
                            setAttributes({ showSubscribe: value });
                        }
                    }),
                    isList ? el(RangeControl, {
                        label: __('Number of events', 'maca-njuvs'),
                        value: attributes.limit || 10,
                        onChange: function (value) {
                            setAttributes({ limit: value });
                        },
                        min: 1,
                        max: 30
                    }) : null,
                    isList ? el(ToggleControl, {
                        label: __('Show image', 'maca-njuvs'),
                        checked: attributes.showImage !== false,
                        onChange: function (value) {
                            setAttributes({ showImage: value });
                        }
                    }) : null,
                    isList ? el(ToggleControl, {
                        label: __('Show location', 'maca-njuvs'),
                        checked: attributes.showLocation !== false,
                        onChange: function (value) {
                            setAttributes({ showLocation: value });
                        }
                    }) : null,
                    !isList ? el(ToggleControl, {
                        label: __('Week starts on Monday', 'maca-njuvs'),
                        checked: attributes.mondayFirst !== false,
                        onChange: function (value) {
                            setAttributes({ mondayFirst: value });
                        }
                    }) : null
                )
            );
        },
        save: function () {
            return null;
        }
    });
})();
