(function ($) {
    'use strict';

    var config = window.macaInfoHubSocialProgress || {};
    var labels = config.labels || {};
    var captionLimit = labels.captionLimit || 2200;

    function channelLabel(channel) {
        if (channel === 'facebook') {
            return labels.publishingFacebook || labels.facebook || 'Publishing to Facebook…';
        }
        if (channel === 'instagram') {
            return labels.publishingInstagram || labels.instagram || 'Publishing to Instagram…';
        }
        return labels.saving || 'Saving…';
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getEditorContent(id) {
        if (typeof window.tinymce !== 'undefined') {
            var editor = window.tinymce.get(id);
            if (editor && !editor.isHidden()) {
                return editor.getContent();
            }
        }

        var textarea = document.getElementById(id);

        return textarea ? textarea.value : '';
    }

    function textLength(text) {
        return Array.from(String(text || '')).length;
    }

    function richTextToPlain(html) {
        var source = String(html || '').trim();

        if (!source) {
            return '';
        }

        source = source
            .replace(/<\/p>\s*<p>/gi, '\n\n')
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<\/p>/gi, '\n\n')
            .replace(/<\/li>/gi, '\n')
            .replace(/<\/h[1-6]>/gi, '\n\n');

        var container = document.createElement('div');
        container.innerHTML = source;
        var plain = container.textContent || container.innerText || '';
        var decoder = document.createElement('textarea');
        decoder.innerHTML = plain;
        plain = decoder.value;

        return plain
            .split(/\r\n|\r|\n/)
            .map(function (line) {
                return line.trim();
            })
            .join('\n')
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    function buildSocialCaption(title, excerptHtml, contentHtml) {
        var titlePlain = String(title || '').trim();
        var excerptPlain = richTextToPlain(excerptHtml);
        var contentPlain = richTextToPlain(contentHtml);
        var parts = [];

        if (titlePlain !== '') {
            parts.push(titlePlain);
        }

        if (excerptPlain !== '' && contentPlain !== '' && excerptPlain === contentPlain) {
            parts.push(contentPlain);
        } else {
            if (excerptPlain !== '' && excerptPlain !== titlePlain) {
                parts.push(excerptPlain);
            }
            if (contentPlain !== '' && contentPlain !== titlePlain && contentPlain !== excerptPlain) {
                parts.push(contentPlain);
            }
        }

        return parts.join('\n\n');
    }

    function getSocialCaptionLength(form) {
        if (!form) {
            return 0;
        }

        var title = (form.querySelector('#news_title') || {}).value || '';
        var caption = buildSocialCaption(
            title,
            getEditorContent('news_excerpt'),
            getEditorContent('news_content')
        );

        return textLength(caption);
    }

    function formatCaptionCounter(length) {
        var remaining = captionLimit - length;
        var counterText = (labels.captionCounter || '%1$d / %2$d characters')
            .replace('%1$d', String(length))
            .replace('%2$d', String(captionLimit));
        var remainingText = (labels.captionRemaining || '%d characters left')
            .replace('%d', String(Math.max(0, remaining)));

        return counterText + ' · ' + remainingText;
    }

    function updateCaptionCounter(form) {
        var counter = form ? form.querySelector('#maca-info-hub-social-caption-counter') : null;

        if (!counter) {
            return {
                length: 0,
                overLimit: false
            };
        }

        var length = getSocialCaptionLength(form);
        var overLimit = length > captionLimit;
        var nearLimit = !overLimit && length > captionLimit - 200;

        counter.textContent = formatCaptionCounter(length);
        counter.classList.toggle('is-over-limit', overLimit);
        counter.classList.toggle('is-near-limit', nearLimit);

        return {
            length: length,
            overLimit: overLimit
        };
    }

    function captionExceedsLimit(form) {
        return getSocialCaptionLength(form) > captionLimit;
    }

    function bindCaptionCounter(form) {
        if (!form || !form.querySelector('#maca-info-hub-social-caption-counter')) {
            return;
        }

        var update = function () {
            updateCaptionCounter(form);
        };

        var titleInput = form.querySelector('#news_title');
        if (titleInput) {
            titleInput.addEventListener('input', update);
        }

        ['news_excerpt', 'news_content'].forEach(function (editorId) {
            var textarea = document.getElementById(editorId);
            if (textarea) {
                textarea.addEventListener('input', update);
            }
        });

        if (typeof window.tinymce !== 'undefined') {
            var bindEditor = function (editor) {
                if (!editor || (editor.id !== 'news_excerpt' && editor.id !== 'news_content')) {
                    return;
                }

                editor.off('keyup change input SetContent NodeChange', update);
                editor.on('keyup change input SetContent NodeChange', update);
            };

            window.tinymce.on('AddEditor', function (event) {
                bindEditor(event.editor);
            });

            ['news_excerpt', 'news_content'].forEach(function (editorId) {
                bindEditor(window.tinymce.get(editorId));
            });
        }

        update();
    }

    function willPublishSocial(form) {
        var selectors = [
            '[name$="_share_facebook"]',
            '[name$="_share_instagram"]',
            '[name="news_republish_facebook"]',
            '[name="news_republish_instagram"]'
        ];

        return selectors.some(function (selector) {
            var input = form.querySelector(selector);
            return input && !input.disabled && input.checked;
        });
    }

    function getSelectedChannels(form) {
        var channels = [];

        if (form.querySelector('[name$="_share_facebook"]:checked, [name="news_republish_facebook"]:checked')) {
            channels.push('facebook');
        }
        if (form.querySelector('[name$="_share_instagram"]:checked, [name="news_republish_instagram"]:checked')) {
            channels.push('instagram');
        }

        return channels;
    }

    function collectPreviewPayload(form) {
        return {
            action: 'maca_njuvs_preview_social_caption',
            nonce: config.nonce,
            object_type: form.getAttribute('data-object-type') || 'news',
            title: (form.querySelector('#news_title') || {}).value || '',
            excerpt: getEditorContent('news_excerpt'),
            content: getEditorContent('news_content'),
            image_url: (form.querySelector('#news_image_url') || {}).value || '',
            share_facebook: form.querySelector('[name$="_share_facebook"]:checked') ? 1 : 0,
            share_instagram: form.querySelector('[name$="_share_instagram"]:checked') ? 1 : 0,
            republish_facebook: form.querySelector('[name="news_republish_facebook"]:checked') ? 1 : 0,
            republish_instagram: form.querySelector('[name="news_republish_instagram"]:checked') ? 1 : 0
        };
    }

    function ensureDeferInput(form) {
        var input = form.querySelector('input[name="maca_defer_social"]');

        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'maca_defer_social';
            form.appendChild(input);
        }

        input.value = '1';
    }

    function createOverlay(initialStatus, mode) {
        var existing = document.getElementById('maca-info-hub-social-progress');

        if (existing) {
            existing.remove();
        }

        var overlay = document.createElement('div');
        overlay.id = 'maca-info-hub-social-progress';
        overlay.className = 'maca-info-hub-social-progress-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-live', 'polite');

        if (mode === 'preview') {
            overlay.innerHTML = '<div class="maca-info-hub-social-progress-card maca-info-hub-social-preview-card">' +
                '<p class="maca-info-hub-social-progress-status">' + escapeHtml(initialStatus || labels.previewLoading || 'Building preview…') + '</p>' +
                '</div>';
        } else {
            overlay.innerHTML =
                '<div class="maca-info-hub-social-progress-card">' +
                    '<div class="maca-info-hub-social-progress-spinner" aria-hidden="true"></div>' +
                    '<h2 class="maca-info-hub-social-progress-title">' + escapeHtml(labels.title || 'Publishing to social media') + '</h2>' +
                    '<p class="maca-info-hub-social-progress-status">' + escapeHtml(initialStatus || labels.saving || 'Saving…') + '</p>' +
                    '<div class="maca-info-hub-social-progress-bar" aria-hidden="true">' +
                        '<div class="maca-info-hub-social-progress-bar-fill"></div>' +
                    '</div>' +
                    '<div class="maca-info-hub-social-progress-steps"></div>' +
                    '<p class="maca-info-hub-social-progress-hint">' + escapeHtml(labels.wait || 'This can take a little while — please keep this tab open.') + '</p>' +
                '</div>';
        }

        document.body.appendChild(overlay);

        return {
            root: overlay,
            status: overlay.querySelector('.maca-info-hub-social-progress-status'),
            fill: overlay.querySelector('.maca-info-hub-social-progress-bar-fill'),
            steps: overlay.querySelector('.maca-info-hub-social-progress-steps')
        };
    }

    function buildSteps(ui, stepKeys) {
        if (!ui.steps) {
            return;
        }

        ui.steps.innerHTML = '';

        stepKeys.forEach(function (key) {
            var dot = document.createElement('span');
            dot.className = 'maca-info-hub-social-progress-step';
            dot.dataset.step = key;
            ui.steps.appendChild(dot);
        });
    }

    function setProgress(ui, stepKeys, activeIndex, percent) {
        if (ui.fill) {
            ui.fill.style.width = Math.max(0, Math.min(100, percent)) + '%';
        }

        if (!ui.steps) {
            return;
        }

        stepKeys.forEach(function (key, index) {
            var dot = ui.steps.querySelector('[data-step="' + key + '"]');

            if (!dot) {
                return;
            }

            dot.classList.remove('is-active', 'is-done');

            if (index < activeIndex) {
                dot.classList.add('is-done');
            } else if (index === activeIndex) {
                dot.classList.add('is-active');
            }
        });
    }

    function channelName(channel) {
        return channel === 'instagram'
            ? (labels.instagram || 'Instagram')
            : (labels.facebook || 'Facebook');
    }

    function showPreviewModal(form, data) {
        var ui = createOverlay('', 'preview');
        var caption = data.instagram_truncated ? data.sent_caption : (data.caption || data.sent_caption || '');
        var channels = Array.isArray(data.channels) ? data.channels : [];
        var channelText = channels.map(channelName).join(', ');
        var imageHtml = '';

        if (data.image_url) {
            imageHtml = '<p><img src="' + escapeHtml(data.image_url) + '" alt="" class="maca-info-hub-social-preview-image"></p>';
        } else {
            imageHtml = '<p class="maca-info-hub-social-preview-warning">' + escapeHtml(labels.previewNoImage || 'No image selected. Instagram requires an image.') + '</p>';
        }

        var truncatedHtml = '';
        if (data.instagram_truncated) {
            truncatedHtml = '<p class="maca-info-hub-social-preview-warning">' + escapeHtml(labels.previewTruncated || 'Instagram limits captions to 2,200 characters. The text below is truncated as it will be sent.') + '</p>';
        }

        ui.root.querySelector('.maca-info-hub-social-preview-card').innerHTML =
            '<h2 class="maca-info-hub-social-progress-title">' + escapeHtml(labels.previewTitle || 'Preview social post') + '</h2>' +
            '<p class="maca-info-hub-social-preview-intro">' + escapeHtml(labels.previewIntro || 'This is the text that will be sent to the selected channels.') + '</p>' +
            '<p class="maca-info-hub-social-preview-meta"><strong>' + escapeHtml(labels.previewChannels || 'Channels') + ':</strong> ' + escapeHtml(channelText || '—') + '</p>' +
            truncatedHtml +
            '<p class="maca-info-hub-social-preview-meta"><strong>' + escapeHtml(labels.previewCaption || 'Caption') + '</strong> (' +
                escapeHtml(labels.previewChars || 'Characters') + ': ' + escapeHtml(String(data.sent_length || 0)) + ')</p>' +
            '<pre class="maca-info-hub-social-preview-caption">' + escapeHtml(caption) + '</pre>' +
            '<p class="maca-info-hub-social-preview-meta"><strong>' + escapeHtml(labels.previewImage || 'Image') + '</strong></p>' +
            imageHtml +
            '<div class="maca-info-hub-social-preview-actions">' +
                '<button type="button" class="button maca-info-hub-social-preview-cancel">' + escapeHtml(labels.previewCancel || 'Cancel') + '</button>' +
                '<button type="button" class="button button-primary maca-info-hub-social-preview-confirm">' + escapeHtml(labels.previewConfirm || 'Save and publish') + '</button>' +
            '</div>';

        ui.root.querySelector('.maca-info-hub-social-preview-cancel').addEventListener('click', function () {
            ui.root.remove();
        });

        ui.root.querySelector('.maca-info-hub-social-preview-confirm').addEventListener('click', function () {
            ui.root.remove();
            form.dataset.previewConfirmed = '1';
            form.requestSubmit ? form.requestSubmit() : form.submit();
        });
    }

    function fetchPreview(form) {
        return $.ajax({
            url: config.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: collectPreviewPayload(form)
        });
    }

    function openPreview(form, requireSocial) {
        if (requireSocial && !willPublishSocial(form)) {
            return;
        }

        if (willPublishSocial(form) && captionExceedsLimit(form)) {
            window.alert(labels.captionOverLimit || 'The social post text may not exceed 2,200 characters (Instagram limit).');
            updateCaptionCounter(form);
            return;
        }

        var ui = createOverlay(labels.previewLoading || 'Building preview…', 'preview');

        fetchPreview(form).done(function (response) {
            ui.root.remove();

            if (!response || !response.success) {
                window.alert((response && response.data && response.data.message) || labels.failed || 'Something went wrong.');
                return;
            }

            showPreviewModal(form, response.data);
        }).fail(function () {
            ui.root.remove();
            window.alert(labels.failed || 'Something went wrong.');
        });
    }

    function publishChannels(pending) {
        var channels = Array.isArray(pending.channels) ? pending.channels.slice() : [];
        var stepKeys = ['save'].concat(channels);
        var ui = createOverlay(labels.saving || 'Saving…');

        buildSteps(ui, stepKeys);
        setProgress(ui, stepKeys, 0, 8);

        if (!channels.length) {
            ui.status.textContent = labels.done || 'Done!';
            setProgress(ui, stepKeys, stepKeys.length - 1, 100);
            ui.root.classList.add('is-complete');
            return Promise.resolve();
        }

        var chain = Promise.resolve();

        channels.forEach(function (channel, index) {
            chain = chain.then(function () {
                ui.status.textContent = channelLabel(channel);
                setProgress(ui, stepKeys, index + 1, 20 + ((index + 1) / channels.length) * 75);

                return $.ajax({
                    url: config.ajaxUrl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'maca_njuvs_publish_social_channel',
                        nonce: config.nonce,
                        object_type: pending.object_type,
                        object_id: pending.object_id,
                        channel: channel
                    }
                }).then(function (response) {
                    if (!response || !response.success) {
                        var message = response && response.data && response.data.message
                            ? response.data.message
                            : (labels.failed || 'Something went wrong.');
                        throw new Error(message);
                    }
                });
            });
        });

        return chain.then(function () {
            ui.status.textContent = labels.done || 'Done!';
            setProgress(ui, stepKeys, stepKeys.length - 1, 100);
            ui.root.classList.add('is-complete');

            window.setTimeout(function () {
                if (ui.root && ui.root.parentNode) {
                    ui.root.parentNode.removeChild(ui.root);
                }
                window.location.reload();
            }, 900);
        }).catch(function (error) {
            ui.status.textContent = error && error.message ? error.message : (labels.failed || 'Something went wrong.');
            ui.root.classList.add('is-error');
        });
    }

    function runTestOverlay() {
        var stepKeys = ['facebook', 'instagram'];
        var ui = createOverlay(labels.testFacebook || 'Sending test post to Facebook…');

        buildSteps(ui, stepKeys);
        setProgress(ui, stepKeys, 0, 20);

        window.setTimeout(function () {
            ui.status.textContent = labels.testInstagram || 'Sending test post to Instagram…';
            setProgress(ui, stepKeys, 1, 65);
        }, 3500);
    }

    $(function () {
        $('.maca-info-hub-social-save-form').each(function () {
            bindCaptionCounter(this);
        });

        $('.maca-info-hub-social-save-form').on('submit', function (event) {
            var form = event.currentTarget;

            if (!willPublishSocial(form)) {
                if (captionExceedsLimit(form)) {
                    updateCaptionCounter(form);
                }
                return;
            }

            if (captionExceedsLimit(form)) {
                event.preventDefault();
                window.alert(labels.captionOverLimit || 'The social post text may not exceed 2,200 characters (Instagram limit).');
                updateCaptionCounter(form);
                return;
            }

            if (form.dataset.previewConfirmed === '1') {
                delete form.dataset.previewConfirmed;
                ensureDeferInput(form);

                var channels = getSelectedChannels(form);
                var stepKeys = ['save'].concat(channels);
                var ui = createOverlay(labels.saving || 'Saving…');
                buildSteps(ui, stepKeys);
                setProgress(ui, stepKeys, 0, 12);
                return;
            }

            event.preventDefault();
            openPreview(form, true);
        });

        $(document).on('click', '.maca-info-hub-social-preview-btn', function () {
            var form = $(this).closest('form.maca-info-hub-social-save-form')[0];
            if (form) {
                openPreview(form, false);
            }
        });

        $('.maca-info-hub-social-test-form').on('submit', function () {
            runTestOverlay();
        });

        if (config.pending && config.pending.object_type && config.pending.object_id) {
            publishChannels(config.pending);
        }
    });
}(jQuery));
