/**
 * maca Njuvs admin form helpers (media uploader, recurrence fields).
 */
(function ($) {
    'use strict';

    var labels = (window.macaNjuvsAdminForm && window.macaNjuvsAdminForm.labels) || {};

    function syncRecurrenceFields() {
        var type = $('#event_recurrence_type').val() || 'none';
        var $wrap = $('#maca-info-recurrence-fields');
        var $weekdays = $('#maca-info-recurrence-weekdays');
        var $unit = $('#maca-info-recurrence-unit');

        if (type === 'none') {
            $wrap.hide();
            return;
        }

        $wrap.show();
        $weekdays.toggle(type === 'weekly');

        if (type === 'daily') {
            $unit.text(labels.dayUnit || 'day(s)');
        } else if (type === 'weekly') {
            $unit.text(labels.weekUnit || 'week(s)');
        } else {
            $unit.text(labels.monthUnit || 'month(s)');
        }
    }

    function syncExceptionFields() {
        var type = $('#exception_type').val() || 'cancelled';

        $('.maca-info-exception-modified').toggle(type === 'modified');
    }

    function formatFileSize(bytes) {
        var value = parseInt(bytes, 10) || 0;

        if (value >= 1048576) {
            return (value / 1048576).toFixed(1) + ' MB';
        }

        if (value >= 1024) {
            return Math.round(value / 1024) + ' KB';
        }

        return value + ' B';
    }

    function updateImageSizeWarning(targetSelector, bytes) {
        var inputId = String(targetSelector || '').replace('#', '');
        var $warning = $('#' + inputId + '_size_warning');
        var threshold = parseInt(labels.imageSizeThreshold, 10) || 512000;
        var template = labels.imageLargeWarning || '';
        var thresholdLabel = labels.imageSizeThresholdLabel || formatFileSize(threshold);

        if (!$warning.length) {
            return;
        }

        if ((parseInt(bytes, 10) || 0) >= threshold) {
            $warning
                .text(
                    template
                        .replace('%1$s', formatFileSize(bytes))
                        .replace('%2$s', thresholdLabel)
                )
                .removeClass('hidden');
            return;
        }

        $warning.text('').addClass('hidden');
    }

    $(function () {
        if ($('#event_recurrence_type').length) {
            $('#event_recurrence_type').on('change', syncRecurrenceFields);
            syncRecurrenceFields();
        }

        if ($('#exception_type').length) {
            $('#exception_type').on('change', syncExceptionFields);
            syncExceptionFields();
        }

        $('.maca-info-hub-upload').on('click', function (event) {
            event.preventDefault();

            var target = $(this).data('target');
            var frame = wp.media({
                title: labels.selectImage || 'Select image',
                button: { text: labels.useImage || 'Use image' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                var bytes = attachment.filesizeInBytes || attachment.filesize || 0;

                $(target).val(attachment.url);
                updateImageSizeWarning(target, bytes);
            });

            frame.open();
        });
    });
}(jQuery));
